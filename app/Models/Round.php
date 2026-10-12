<?php

namespace App\Models;

use Database\Factories\RoundFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['number', 'due_on', 'beneficiary_id', 'paid_out_at'])]
class Round extends Model
{
    /** @use HasFactory<RoundFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'number' => 'integer',
            'due_on' => 'immutable_date',
            'paid_out_at' => 'immutable_datetime',
        ];
    }

    /**
     * @return BelongsTo<Tontine, $this>
     */
    public function tontine(): BelongsTo
    {
        return $this->belongsTo(Tontine::class);
    }

    /**
     * @return BelongsTo<Member, $this>
     */
    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'beneficiary_id');
    }

    /**
     * @return HasMany<Contribution, $this>
     */
    public function contributions(): HasMany
    {
        return $this->hasMany(Contribution::class);
    }

    public function isPaidOut(): bool
    {
        return $this->paid_out_at !== null;
    }

    public function isOverdue(): bool
    {
        return ! $this->isPaidOut() && $this->due_on->isPast() && ! $this->due_on->isToday();
    }
}
