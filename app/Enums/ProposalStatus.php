<?php

declare(strict_types=1);

namespace App\Enums;

enum ProposalStatus: string
{
    case New = 'new';
    case Approved = 'approved';
    case Disapproved = 'disapproved';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Approved => 'Approved',
            self::Disapproved => 'Disapproved',
        };
    }
}
