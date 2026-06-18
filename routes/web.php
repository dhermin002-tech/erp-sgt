<?php

use App\Http\Controllers\ActionSuiviController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\MembresController;
use App\Http\Controllers\PreferenceController;
use App\Http\Controllers\CommentaireController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PageRapportController;
use App\Http\Controllers\RapportController;
use App\Http\Controllers\PreferencesPageController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\RapportsAgentsController;
use App\Http\Controllers\SessionsAgentsController;
use App\Http\Controllers\TachesAgentsController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SousTacheController;
use App\Http\Controllers\TacheController;
use Illuminate\Support\Facades\Route;

// ── Auth ─────────────────────────────────────────────────────────────────────
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login'])->middleware(['guest', 'throttle:5,1']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// ── App (auth requise — agents IA bloqués du web) ────────────────────────────
Route::middleware(['auth', 'not-agent-account'])->group(function () {

    Route::get('/', fn() => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');

    // ── Tâches ────────────────────────────────────────────────────────────────
    Route::get('/taches/archives', [TacheController::class, 'archives'])->name('taches.archives');
    Route::resource('taches', TacheController::class)->parameters(['taches' => 'tache']);
    Route::patch('/taches/{tache}/restaurer', [TacheController::class, 'restaurer'])->name('taches.restaurer');
    Route::patch('/taches/{tache}/archiver', [TacheController::class, 'archiver'])->name('taches.archiver');
    Route::patch('/taches/{tache}/statut', [TacheController::class, 'patchStatut'])->name('taches.statut');

    // ── Sous-tâches (AJAX) ────────────────────────────────────────────────────
    Route::post('/taches/{tache}/sous-taches', [SousTacheController::class, 'store'])->name('sous-taches.store');
    Route::patch('/sous-taches/{sousTache}/toggle', [SousTacheController::class, 'toggle'])->name('sous-taches.toggle');
    Route::delete('/sous-taches/{sousTache}', [SousTacheController::class, 'destroy'])->name('sous-taches.destroy');

    // ── Commentaires ──────────────────────────────────────────────────────────
    Route::post('/taches/{tache}/commentaires', [CommentaireController::class, 'store'])->name('commentaires.store');
    Route::delete('/commentaires/{commentaire}', [CommentaireController::class, 'destroy'])->name('commentaires.destroy');

    // ── Page Rapport général ──────────────────────────────────────────────────
    Route::get('/rapports', [PageRapportController::class, 'index'])->name('rapports.index');

    // ── Rapports & Actions ────────────────────────────────────────────────────
    Route::post('/taches/{tache}/rapports', [RapportController::class, 'store'])->name('rapports.store');
    Route::delete('/rapports/{rapport}', [RapportController::class, 'destroy'])->name('rapports.destroy');
    Route::post('/taches/{tache}/actions', [ActionSuiviController::class, 'store'])->name('actions.store');
    Route::patch('/actions/{actionSuivi}/toggle', [ActionSuiviController::class, 'toggle'])->name('actions.toggle');
    Route::delete('/actions/{actionSuivi}', [ActionSuiviController::class, 'destroy'])->name('actions.destroy');

    // ── Notifications ─────────────────────────────────────────────────────────
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{id}/lue', [NotificationController::class, 'marquerLue'])->name('notifications.lue');
    Route::patch('/notifications/tout-lire', [NotificationController::class, 'toutLire'])->name('notifications.tout-lire');
    Route::get('/notifications/count', [NotificationController::class, 'count'])->name('notifications.count');

    // ── Préférences utilisateur ───────────────────────────────────────────────
    Route::post('/preferences/locale', [PreferenceController::class, 'setLocale'])->name('preferences.locale');
    Route::patch('/preferences/direction', [PreferenceController::class, 'setDirection'])->name('preferences.direction');

    // ── Profil utilisateur ────────────────────────────────────────────────────
    Route::get('/profil', [ProfilController::class, 'index'])->name('profil.index');
    Route::patch('/profil/infos', [ProfilController::class, 'updateInfos'])->name('profil.updateInfos');
    Route::patch('/profil/password', [ProfilController::class, 'updatePassword'])->name('profil.updatePassword');
    Route::patch('/profil/preferences', [ProfilController::class, 'updatePreferences'])->name('profil.updatePreferences');

    // ── Page Préférences (redirige vers profil) ───────────────────────────────
    Route::get('/preferences', [PreferencesPageController::class, 'index'])->name('preferences.index');

    // ── Agents IA — Rapports, Sessions & Tâches (Manager uniquement) ─────────
    Route::get('/agents/rapports', [RapportsAgentsController::class, 'index'])->name('agents.rapports')->middleware('role:manager');
    Route::get('/agents/sessions', [SessionsAgentsController::class, 'index'])->name('agents.sessions')->middleware('role:manager');
    Route::get('/agents/taches', [TachesAgentsController::class, 'index'])->name('agents.taches')->middleware('role:manager');

    // ── Membres (Manager uniquement) ──────────────────────────────────────────
    Route::resource('membres', MembresController::class)->middleware('role:manager')->parameters(['membres' => 'membre']);
    Route::post('/membres/agents', [MembresController::class, 'storeAgent'])->name('membres.storeAgent')->middleware('role:manager');

    // ── Sites (Manager uniquement) ────────────────────────────────────────────
    Route::resource('sites', SiteController::class)->middleware('role:manager');

});
