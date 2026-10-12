<?php

declare(strict_types=1);

namespace App\Services\Proposal;

use App\DTOs\ProposalData;
use App\Enums\ProposalEvent;
use App\Enums\ProposalStatus;
use App\Exceptions\ProposalAlreadyDecidedException;
use App\Models\Proposal;
use App\Models\ProposalFile;
use App\Repositories\Contracts\ProposalRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final readonly class ProposalService
{
    /** Proposal fields that are recorded in the history when edited. */
    private const FIELD_LABELS = [
        'title' => 'title',
        'abstract' => 'abstract',
        'proponents' => 'proponents',
        'objectives' => 'objectives',
        'research_area' => 'research area',
        'start_date' => 'start date',
        'end_date' => 'end date',
        'budget' => 'budget',
        'keywords' => 'keywords',
    ];

    public function __construct(
        private ProposalRepositoryInterface $proposals,
    ) {}

    /**
     * @param  array{status?: string, year?: int|string, search?: string}  $filters
     */
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->proposals->paginate($filters, $perPage);
    }

    public function get(int $id): Proposal
    {
        return $this->proposals->findById($id);
    }

    public function create(ProposalData $data): Proposal
    {
        return DB::transaction(function () use ($data): Proposal {
            $proposal = $this->proposals->create($data);

            $this->proposals->addHistory($proposal, ProposalEvent::Created);

            return $proposal;
        });
    }

    /**
     * @throws ProposalAlreadyDecidedException
     */
    public function getEditable(int $id): Proposal
    {
        $proposal = $this->proposals->findById($id);

        $this->ensureUndecided($proposal);

        return $proposal;
    }

    /**
     * @throws ProposalAlreadyDecidedException
     */
    public function update(int $id, ProposalData $data): Proposal
    {
        $proposal = $this->getEditable($id);

        return DB::transaction(function () use ($proposal, $data): Proposal {
            $proposal = $this->proposals->update($proposal, $data);

            $changed = collect(self::FIELD_LABELS)
                ->filter(fn (string $label, string $field): bool => $proposal->wasChanged($field))
                ->values();

            $this->proposals->addHistory(
                $proposal,
                ProposalEvent::Updated,
                $changed->isNotEmpty() ? 'Changed '.$changed->implode(', ').'.' : 'Saved with no changes.',
            );

            return $proposal;
        });
    }

    /**
     * Supporting files can be added or removed at any time, whatever the proposal's status.
     *
     * @param  array<int, UploadedFile>  $files
     */
    public function addFiles(int $proposalId, array $files): void
    {
        $proposal = $this->proposals->findById($proposalId);

        $names = [];

        foreach ($files as $file) {
            // UploadedFile::store() relies on realpath(), which fails for PHP's upload temp dir
            // on some Windows setups (C:\Windows\Temp under Apache). Read via the plain path.
            $path = "proposals/{$proposal->id}/{$file->hashName()}";
            Storage::put($path, file_get_contents($file->getPathname()));

            $this->proposals->addFile($proposal, [
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);

            $names[] = $file->getClientOriginalName();
        }

        if ($names !== []) {
            $this->proposals->addHistory($proposal, ProposalEvent::FileAdded, implode(', ', $names));
        }
    }

    public function findFile(int $proposalId, int $fileId): ProposalFile
    {
        return $this->proposals->findFile($this->proposals->findById($proposalId), $fileId);
    }

    public function deleteFile(int $proposalId, int $fileId): void
    {
        $file = $this->findFile($proposalId, $fileId);

        DB::transaction(function () use ($file, $proposalId): void {
            $this->proposals->deleteFile($file);

            $this->proposals->addHistory(
                $this->proposals->findById($proposalId),
                ProposalEvent::FileRemoved,
                $file->name,
            );
        });

        Storage::delete($file->path);
    }

    /**
     * @throws ProposalAlreadyDecidedException
     */
    public function decide(int $id, ProposalStatus $decision, ?string $remarks = null): Proposal
    {
        $proposal = $this->getEditable($id);

        return DB::transaction(function () use ($proposal, $decision, $remarks): Proposal {
            $proposal = $this->proposals->updateStatus($proposal, $decision, $remarks);

            $this->proposals->addHistory(
                $proposal,
                $decision === ProposalStatus::Approved ? ProposalEvent::Approved : ProposalEvent::Disapproved,
                $remarks,
            );

            return $proposal;
        });
    }

    public function delete(int $id): void
    {
        $proposal = $this->proposals->findById($id);

        DB::transaction(function () use ($proposal): void {
            $this->proposals->delete($proposal);
        });
    }

    /**
     * @throws ProposalAlreadyDecidedException
     */
    private function ensureUndecided(Proposal $proposal): void
    {
        if ($proposal->status !== ProposalStatus::New) {
            throw new ProposalAlreadyDecidedException;
        }
    }
}
