<?php

declare(strict_types=1);

namespace App\Enums;

enum ProposalEvent: string
{
    case Created = 'created';
    case Updated = 'updated';
    case Approved = 'approved';
    case Disapproved = 'disapproved';
    case FileAdded = 'file_added';
    case FileRemoved = 'file_removed';

    public function label(): string
    {
        return match ($this) {
            self::Created => 'Proposal created',
            self::Updated => 'Proposal edited',
            self::Approved => 'Proposal approved',
            self::Disapproved => 'Proposal disapproved',
            self::FileAdded => 'Files uploaded',
            self::FileRemoved => 'File removed',
        };
    }
}
