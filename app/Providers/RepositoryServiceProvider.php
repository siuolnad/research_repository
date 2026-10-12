<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Contracts\ProposalRepositoryInterface;
use App\Repositories\Eloquent\EloquentProposalRepository;
use Illuminate\Support\ServiceProvider;

final class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ProposalRepositoryInterface::class,
            EloquentProposalRepository::class,
        );
    }
}
