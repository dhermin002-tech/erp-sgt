<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SousTacheController;
use App\Http\Controllers\TacheController;
use Illuminate\Support\Facades\Route;

// ── Auth ─────────────────────────────────────────────────────────────────────
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login'])->middleware('guest');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// ── App (auth requise) ────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    Route::get('/', fn() => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── Tâches ────────────────────────────────────────────────────────────────
    // Route archives AVANT resource pour éviter conflit avec show/{tache}
    Route::get('/taches/archives', [TacheController::class, 'archives'])->name('taches.archives');
    Route::resource('taches', TacheController::class)->parameters(['taches' => 'tache']);
    Route::patch('/taches/{tache}/restaurer', [TacheController::class, 'restaurer'])->name('taches.restaurer');
    Route::patch('/taches/{tache}/statut', [TacheController::class, 'patchStatut'])->name('taches.statut');

    // ── Sous-tâches (AJAX) ────────────────────────────────────────────────────
    Route::post('/taches/{tache}/sous-taches', [SousTacheController::class, 'store'])->name('sous-taches.store');
    Route::patch('/sous-taches/{sousTache}/toggle', [SousTacheController::class, 'toggle'])->name('sous-taches.toggle');
    Route::delete('/sous-taches/{sousTache}', [SousTacheController::class, 'destroy'])->name('sous-taches.destroy');

    // ── Sites (Manager uniquement) ────────────────────────────────────────────
    Route::resource('sites', SiteController::class)->middleware('role:manager');

});
