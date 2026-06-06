<?php

use App\Http\Controllers\Api\ApiTokenController;
use App\Http\Controllers\Api\TacheApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ── Auth API (rate-limit : 5 tentatives/minute par IP) ───────────────────────
Route::middleware('throttle:5,1')->group(function () {
    Route::post('/v1/auth/token', [ApiTokenController::class, 'issue']);
});

// ── Routes protégées Sanctum (rate-limit : 60 req/minute) ────────────────────
Route::middleware(['auth:sanctum', 'throttle:60,1'])->prefix('v1')->group(function () {

    // Utilisateur connecté
    Route::get('/me', fn(Request $r) => response()->json([
        'id'         => $r->user()->id,
        'nom_complet'=> $r->user()->nom_complet,
        'role'       => $r->user()->role,
    ]));
    Route::delete('/auth/token', [ApiTokenController::class, 'revoke']);

    // Tâches
    Route::get   ('/taches',                     [TacheApiController::class, 'index']);
    Route::post  ('/taches',                     [TacheApiController::class, 'store']);
    Route::get   ('/taches/{tache}',             [TacheApiController::class, 'show']);
    Route::patch ('/taches/{tache}/statut',      [TacheApiController::class, 'changerStatut']);
    Route::delete('/taches/{tache}',             [TacheApiController::class, 'destroy']);

    // Dashboard KPIs
    Route::get('/dashboard/kpis', [DashboardApiController::class, 'kpis']);

    // Référentiels (lecture seule)
    Route::get('/membres', fn(Request $r) => response()->json([
        'data' => User::orderBy('nom')->get()->map(fn($u) => [
            'id'         => $u->id,
            'nom_complet'=> $u->nom_complet,
            'role'       => $u->role,
        ]),
    ]));

    Route::get('/sites', fn() => response()->json([
        'data' => Site::where('actif', true)->orderBy('nom')
            ->get(['id', 'nom', 'localisation']),
    ]));
});
