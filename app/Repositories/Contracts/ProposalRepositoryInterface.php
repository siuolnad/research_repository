<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\DTOs\ProposalData;
use App\Enums\ProposalEvent;
use App\Enums\ProposalStatus;
use App\Models\Proposal;
use App\Models\ProposalFile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

interface ProposalRepositoryInterface
{
    /**
     * @param  array{status?: string, year?: int|string, search?: string}  $filters
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * @throws ModelNotFoundException
     */
    public function findById(int $id): Proposal;

    public function create(ProposalData $data): Proposal;

    public function update(Proposal $proposal, ProposalData $data): Proposal;

    public function updateStatus(Proposal $proposal, ProposalStatus $status, ?string $remarks = null): Proposal;

    public function delete(Proposal $proposal): void;

    /**
     * @param  array{name: string, path: string, mime_type: string|null, size: int}  $attributes
     */
    public function addFile(Proposal $proposal, array $attributes): ProposalFile;

    /**
     * @throws ModelNotFoundException
     */
    public function findFile(Proposal $proposal, int $fileId): ProposalFile;

    public function deleteFile(ProposalFile $file): void;

    public function addHistory(Proposal $proposal, ProposalEvent $event, ?string $description = null): void;
}
