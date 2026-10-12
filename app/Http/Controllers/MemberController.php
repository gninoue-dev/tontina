<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMemberRequest;
use App\Models\Member;
use App\Models\Tontine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class MemberController extends Controller
{
    public function store(StoreMemberRequest $request, Tontine $tontine): RedirectResponse
    {
        if (! $tontine->isDraft()) {
            return back()->with('error', 'On ne peut plus ajouter de membre : la tontine a démarré.');
        }
        $tontine->members()->create($request->validated());

        return back()->with('success', $request->validated('name').' a rejoint la tontine.');
    }

    public function destroy(Tontine $tontine, Member $member): RedirectResponse
    {
        Gate::authorize('manage', $tontine);
        if (! $tontine->isDraft()) {
            return back()->with('error', 'On ne peut plus retirer de membre : la tontine a démarré.');
        }
        $member->delete();

        return back()->with('success', $member->name.' a été retiré(e).');
    }
}
