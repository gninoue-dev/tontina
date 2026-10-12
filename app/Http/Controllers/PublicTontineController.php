<?php

namespace App\Http\Controllers;

use App\Models\Tontine;
use Illuminate\View\View;

class PublicTontineController extends Controller
{
    /**
     * Page de transparence partagée aux membres : qui a cotisé, qui reçoit quand. Aucun numéro complet affiché.
     */
    public function show(string $token): View
    {
        $tontine = Tontine::where('public_token', $token)
            ->with(['members', 'rounds.beneficiary', 'rounds.contributions', 'organizer'])
            ->firstOrFail();

        return view('public.show', ['tontine' => $tontine]);
    }
}
