<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class ProposalAlreadyDecidedException extends Exception
{
    public function __construct()
    {
        parent::__construct('This proposal already has a final decision and can no longer be changed.');
    }

    public function render(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $this->getMessage()], 409);
        }

        return back()->with('status', $this->getMessage());
    }
}
