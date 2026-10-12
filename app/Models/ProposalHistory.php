<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProposalEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $proposal_id
 * @property ProposalEvent $event
 * @property string|null $description
 * @property Carbon $created_at
 */
class ProposalHistory extends Model
{
    public const UPDATED_AT = null;

    /** @var list<string> */
    protected $fillable = [
        'event',
        'description',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event' => ProposalEvent::class,
        ];
    }
}
