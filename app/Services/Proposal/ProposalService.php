<?php

declare(strict_types=1);

namespace App\Services\Proposal;

use App\DTOs\ProposalData;
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
        return DB::transaction(
            fn (): Proposal => $this->proposals->create($data)
        );
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

        return DB::transaction(
            fn (): Proposal => $this->proposals->update($proposal, $data)
        );
    }

    /**
     * Supporting files can be added or removed at any time, whatever the proposal's status.
     *
     * @param  array<int, UploadedFile>  $files
     */
    public function addFiles(int $proposalId, array $files): void
    {
        $proposal = $this->proposals->findById($proposalId);

        foreach ($files as $file) {
            $this->proposals->addFile($proposal, [
                'name' => $file->getClientOriginalName(),
                'path' => $file->store("proposals/{$proposal->id}"),
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }
    }

    public function findFile(int $proposalId, int $fileId): ProposalFile
    {
        return $this->proposals->findFile($this->proposals->findById($proposalId), $fileId);
    }

    public function deleteFile(int $proposalId, int $fileId): void
    {
        $file = $this->findFile($proposalId, $fileId);

        DB::transaction(function () use ($file): void {
            $this->proposals->deleteFile($file);
        });

        Storage::delete($file->path);
    }

    /**
     * @throws ProposalAlreadyDecidedException
     */
    public function decide(int $id, ProposalStatus $decision, ?string $remarks = null): Proposal
    {
        $proposal = $this->getEditable($id);

        return DB::transaction(
            fn (): Proposal => $this->proposals->updateStatus($proposal, $decision, $remarks)
        );
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
