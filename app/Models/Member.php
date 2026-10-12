<?php

namespace App\Models;

use Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'phone', 'payout_position'])]
class Member extends Model
{
    /** @use HasFactory<MemberFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payout_position' => 'integer',
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
     * @return HasMany<Contribution, $this>
     */
    public function contributions(): HasMany
    {
        return $this->hasMany(Contribution::class);
    }

    /**
     * Numéro masqué pour la page publique : « 07 •• •• •• 45 ».
     */
    public function maskedPhone(): string
    {
        return mb_substr($this->phone, 0, 2).' •• •• •• '.mb_substr($this->phone, -2);
    }

    /**
     * Lien WhatsApp (wa.me) vers ce membre avec un message prérempli.
     */
    public function whatsappLink(string $message): string
    {
        return 'https://wa.me/225'.$this->phone.'?text='.rawurlencode($message);
    }
}
