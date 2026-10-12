<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $proposal_id
 * @property string $name
 * @property string $path
 * @property string|null $mime_type
 * @property int $size
 */
class ProposalFile extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'name',
        'path',
        'mime_type',
        'size',
    ];

    /**
     * @return BelongsTo<Proposal, $this>
     */
    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }
}
