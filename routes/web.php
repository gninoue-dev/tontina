<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ContributionController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\PublicTontineController;
use App\Http\Controllers\RoundController;
use App\Http\Controllers\TontineController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::get('/t/{token}', [PublicTontineController::class, 'show'])->name('public.show');

Route::middleware('guest')->group(function () {
    Route::get('/inscription', [RegisterController::class, 'create'])->name('register');
    Route::post('/inscription', [RegisterController::class, 'store'])->middleware('throttle:6,1');
    Route::get('/connexion', [LoginController::class, 'create'])->name('login');
    Route::post('/connexion', [LoginController::class, 'store'])->middleware('throttle:6,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/deconnexion', [LoginController::class, 'destroy'])->name('logout');

    Route::resource('tontines', TontineController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
    Route::post('/tontines/{tontine}/tirage', [TontineController::class, 'draw'])->name('tontines.draw');
    Route::post('/tontines/{tontine}/demarrer', [TontineController::class, 'start'])->name('tontines.start');

    Route::scopeBindings()->group(function () {
        Route::post('/tontines/{tontine}/membres', [MemberController::class, 'store'])->name('members.store');
        Route::delete('/tontines/{tontine}/membres/{member}', [MemberController::class, 'destroy'])->name('members.destroy');

        Route::get('/tontines/{tontine}/tours/{round:number}', [RoundController::class, 'show'])->name('rounds.show');
        Route::post('/tontines/{tontine}/tours/{round:number}/remise', [RoundController::class, 'payout'])->name('rounds.payout');
        Route::post('/tontines/{tontine}/tours/{round:number}/cotisations', [ContributionController::class, 'store'])->name('contributions.store');
        Route::delete('/tontines/{tontine}/tours/{round:number}/cotisations/{contribution}', [ContributionController::class, 'destroy'])->name('contributions.destroy');
    });
});
