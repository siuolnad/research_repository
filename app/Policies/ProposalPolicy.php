<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Auth\Access\Response;

final class ProposalPolicy
{
    /**
     * Reviewers work with every proposal; proponents only with their own.
     * Other people's proposals look like they don't exist.
     */
    public function manage(User $user, Proposal $proposal): Response
    {
        if ($user->role === UserRole::Reviewer || $proposal->user_id === $user->id) {
            return Response::allow();
        }

        return Response::denyAsNotFound();
    }
}
