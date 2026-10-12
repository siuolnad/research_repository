<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\DTOs\ProposalData;
use App\Enums\ProposalEvent;
use App\Enums\ProposalStatus;
use App\Models\Proposal;
use App\Models\ProposalFile;
use App\Repositories\Contracts\ProposalRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class EloquentProposalRepository implements ProposalRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Proposal::query()
            ->when(
                $filters['status'] ?? null,
                fn (Builder $q, string $status) => $q->where('status', $status)
            )
            ->when(
                $filters['year'] ?? null,
                fn (Builder $q, int|string $year) => $q->whereYear('start_date', (int) $year)
            )
            ->when(
                $filters['search'] ?? null,
                fn (Builder $q, string $term) => $q->where(function (Builder $inner) use ($term) {
                    $inner->where('title', 'like', "%{$term}%")
                        ->orWhere('abstract', 'like', "%{$term}%");
                })
            )
            ->latest('start_date')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findById(int $id): Proposal
    {
        return Proposal::query()->findOrFail($id);
    }

    public function create(ProposalData $data): Proposal
    {
        return Proposal::query()->create($data->toDatabase());
    }

    public function update(Proposal $proposal, ProposalData $data): Proposal
    {
        $proposal->update($data->toDatabase());

        return $proposal;
    }

    public function updateStatus(Proposal $proposal, ProposalStatus $status, ?string $remarks = null): Proposal
    {
        $proposal->update(['status' => $status, 'remarks' => $remarks]);

        return $proposal->fresh();
    }

    public function delete(Proposal $proposal): void
    {
        $proposal->delete();
    }

    public function addFile(Proposal $proposal, array $attributes): ProposalFile
    {
        return $proposal->files()->create($attributes);
    }

    public function findFile(Proposal $proposal, int $fileId): ProposalFile
    {
        return $proposal->files()->findOrFail($fileId);
    }

    public function deleteFile(ProposalFile $file): void
    {
        $file->delete();
    }

    public function addHistory(Proposal $proposal, ProposalEvent $event, ?string $description = null): void
    {
        $proposal->history()->create(['event' => $event, 'description' => $description]);
    }
}
