<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureReviewer
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(UserRole::current()->canDecide(), 403, 'Only reviewers can approve or disapprove proposals.');

        return $next($request);
    }
}
