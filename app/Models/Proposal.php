<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProposalStatus;
use App\Enums\ResearchArea;
use Database\Factories\ProposalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $title
 * @property string $abstract
 * @property string $proponent
 * @property array<int, string> $objectives
 * @property array<int, string>|null $keywords
 * @property ResearchArea $research_area
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property string $budget
 * @property ProposalStatus $status
 * @property string|null $remarks
 */
class Proposal extends Model
{
    /** @use HasFactory<ProposalFactory> */
    use HasFactory;

    use SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'title',
        'abstract',
        'proponent',
        'objectives',
        'keywords',
        'research_area',
        'start_date',
        'end_date',
        'budget',
        'status',
        'remarks',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return HasMany<ProposalFile, $this>
     */
    public function files(): HasMany
    {
        return $this->hasMany(ProposalFile::class);
    }

    /**
     * @return HasMany<ProposalHistory, $this>
     */
    public function history(): HasMany
    {
        return $this->hasMany(ProposalHistory::class)->oldest('id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'objectives' => 'array',
            'keywords' => 'array',
            'research_area' => ResearchArea::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'budget' => 'decimal:2',
            'status' => ProposalStatus::class,
        ];
    }
}
