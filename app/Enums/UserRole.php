<?php

declare(strict_types=1);

namespace App\Enums;

use Illuminate\Support\Facades\Auth;

enum UserRole: string
{
    case Proponent = 'proponent';
    case Reviewer = 'reviewer';

    /**
     * The role of the signed-in user.
     */
    public static function current(): self
    {
        return Auth::user()?->role ?? self::Proponent;
    }

    public function label(): string
    {
        return match ($this) {
            self::Proponent => 'Proponent',
            self::Reviewer => 'Reviewer',
        };
    }

    public function canDecide(): bool
    {
        return $this === self::Reviewer;
    }
}
