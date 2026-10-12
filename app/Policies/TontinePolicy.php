<?php

namespace App\Policies;

use App\Models\Tontine;
use App\Models\User;

class TontinePolicy
{
    /**
     * Seul l'organisateur voit le tableau de bord de sa tontine (les membres ont la page publique).
     */
    public function view(User $user, Tontine $tontine): bool
    {
        return $user->id === $tontine->user_id;
    }

    /**
     * Gérer les membres, l'ordre de passage, les cotisations et les remises de cagnotte.
     */
    public function manage(User $user, Tontine $tontine): bool
    {
        return $user->id === $tontine->user_id;
    }

    /**
     * Une tontine ne se supprime que tant qu'elle n'a pas démarré (l'historique des paiements fait foi).
     */
    public function delete(User $user, Tontine $tontine): bool
    {
        return $user->id === $tontine->user_id && $tontine->isDraft();
    }
}
