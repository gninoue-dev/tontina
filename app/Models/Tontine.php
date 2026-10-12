<?php

namespace App\Models;

use App\Frequency;
use App\TontineStatus;
use Database\Factories\TontineFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

#[Fillable(['name', 'description', 'contribution_amount', 'frequency', 'starts_on', 'late_penalty'])]
class Tontine extends Model
{
    /** @use HasFactory<TontineFactory> */
    use HasFactory;

    /**
     * Valeurs par défaut dont un modèle tout juste créé a besoin (identiques à la migration).
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'draft',
        'late_penalty' => 0,
    ];

    protected static function booted(): void
    {
        static::creating(function (Tontine $tontine): void {
            $tontine->public_token ??= Str::random(32);
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'contribution_amount' => 'integer',
            'late_penalty' => 'integer',
            'frequency' => Frequency::class,
            'status' => TontineStatus::class,
            'starts_on' => 'immutable_date',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return HasMany<Member, $this>
     */
    public function members(): HasMany
    {
        return $this->hasMany(Member::class)->orderByRaw('payout_position is null')->orderBy('payout_position')->orderBy('id');
    }

    /**
     * @return HasMany<Round, $this>
     */
    public function rounds(): HasMany
    {
        return $this->hasMany(Round::class)->orderBy('number');
    }

    /**
     * @return HasManyThrough<Contribution, Round, $this>
     */
    public function contributions(): HasManyThrough
    {
        return $this->hasManyThrough(Contribution::class, Round::class);
    }

    public function isDraft(): bool
    {
        return $this->status === TontineStatus::Draft;
    }

    /**
     * Montant total que reçoit le bénéficiaire d'un tour (une cotisation par membre).
     */
    public function potAmount(): int
    {
        return $this->contribution_amount * $this->members()->count();
    }
}
