<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case Proponent = 'proponent';
    case Reviewer = 'reviewer';

    /**
     * The role the visitor is acting as. There are no accounts yet, so it lives in the session.
     */
    public static function current(): self
    {
        return self::tryFrom((string) session('role')) ?? self::Proponent;
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
