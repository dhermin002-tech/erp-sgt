<?php

use App\Http\Controllers\Api\ArchivesApiController;
use App\Http\Controllers\Api\CompteApiController;
use App\Http\Controllers\Api\NotificationsApiController;
use App\Http\Controllers\Api\SupervisionApiController;
use App\Http\Controllers\MembresController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\Api\AnalyseApiController;
use App\Http\Controllers\Api\ApiTokenController;
use App\Http\Controllers\Api\CommentaireApiController;
use App\Http\Controllers\Api\OrdreIaController;
use App\Http\Controllers\Api\AppareilPushController;
use App\Http\Controllers\Api\ProfilMobileController;
use App\Http\Controllers\Api\SynchronisationMobileController;
use App\Http\Controllers\Api\TacheApiController;
use App\Http\Controllers\Api\SousTacheApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\RapportAgentApiController;
use App\Http\Controllers\RapportController;
use App\Http\Controllers\Api\SessionAgentApiController;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ── Auth API (rate-limit : 5 tentatives/minute par IP et identifiant) ───────────────────────
Route::middleware('throttle:emission-jeton')->group(function () {
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

    // Les routes statiques précèdent le binding de /taches/{tache}.
    Route::get('/taches/archives', [ArchivesApiController::class, 'index'])
        ->middleware('ability:mobile,mcp:read,mcp:write,taches:read');
    Route::middleware('ability:mobile,mcp:write,taches:update')->group(function () {
        Route::patch('/taches/{tache}/archiver', [ArchivesApiController::class, 'archiver']);
        Route::patch('/taches/{tache}/restaurer', [ArchivesApiController::class, 'restaurer']);
    });

    Route::middleware('ability:mobile')->group(function () {
        Route::get('/notifications', [NotificationsApiController::class, 'index']);
        Route::get('/notifications/compteur', [NotificationsApiController::class, 'compteur']);
        Route::patch('/notifications/tout-lire', [NotificationsApiController::class, 'toutLire']);
        Route::patch('/notifications/{notification}/lue', [NotificationsApiController::class, 'lire'])->whereUuid('notification');
        Route::get('/profil', [CompteApiController::class, 'profil']);
        Route::patch('/profil/infos', [CompteApiController::class, 'modifierProfil']);
        Route::patch('/profil/password', [CompteApiController::class, 'modifierMotDePasse'])->middleware('throttle:mot-de-passe-mobile');
        Route::get('/preferences', [CompteApiController::class, 'preferences']);
        Route::patch('/preferences', [CompteApiController::class, 'modifierPreferences']);

        Route::middleware('role:manager')->group(function () {
            Route::prefix('gestion')->name('api.gestion.')->group(function () {
                Route::apiResource('membres', MembresController::class)->parameters(['membres' => 'membre'])->except('show');
                Route::get('/membres/{membre}', [MembresController::class, 'showApi'])->name('membres.show');
                Route::apiResource('sites', SiteController::class);
            });
            Route::get('/gestion/agents/taches', [SupervisionApiController::class, 'taches']);
            Route::get('/gestion/agents/rapports', [SupervisionApiController::class, 'rapports']);
            Route::get('/gestion/agents/sessions', [SupervisionApiController::class, 'sessions']);
        });
    });
    Route::get('/rapports', [SupervisionApiController::class, 'rapportGlobal'])
        ->middleware('ability:mobile,mcp:read,mcp:write,taches:read');

    // Tâches
    Route::get   ('/taches',                     [TacheApiController::class, 'index'])->middleware('ability:mcp:read,mcp:write,mobile,taches:read');
    Route::post  ('/taches',                     [TacheApiController::class, 'store'])->middleware('ability:mcp:write,mobile,taches:create');
    Route::get   ('/taches/{tache}',             [TacheApiController::class, 'show'])->middleware('ability:mcp:read,mcp:write,mobile,taches:read');
    Route::patch ('/taches/{tache}',              [TacheApiController::class, 'update'])->middleware('ability:mcp:write,mobile,taches:update');
    Route::patch ('/taches/{tache}/statut',      [TacheApiController::class, 'changerStatut'])->middleware('ability:mcp:write,mobile,taches:update');
    Route::delete('/taches/{tache}',             [TacheApiController::class, 'destroy'])->middleware('ability:mcp:write,mobile,taches:delete');

    Route::get('/taches/{tache}/commentaires', [CommentaireApiController::class, 'index'])
        ->middleware('ability:mcp:read,mcp:write,mobile,taches:read');
    Route::post('/taches/{tache}/commentaires', [CommentaireApiController::class, 'store'])
        ->middleware('ability:mcp:write,mobile,taches:update');
    Route::delete('/commentaires/{commentaire}', [CommentaireApiController::class, 'destroy'])
        ->middleware('ability:mcp:write,mobile,taches:update');

    Route::get('/taches/{tache}/historique', [TacheApiController::class, 'historique'])
        ->middleware('ability:mobile,mcp:read,mcp:write,taches:read');
    Route::get('/taches/{tache}/rapports', [RapportController::class, 'listeApi'])
        ->middleware('ability:mobile,mcp:read,mcp:write,taches:read');
    Route::post('/taches/{tache}/rapports', [RapportController::class, 'store'])
        ->middleware('ability:mobile,mcp:write,taches:update');

    // Sous-tâches
    Route::get   ('/taches/{tache}/sous-taches',          [TacheApiController::class, 'sousTaches'])->middleware('ability:mcp:read,mcp:write,mobile,taches:read');
    Route::post  ('/taches/{tache}/sous-taches',          [SousTacheApiController::class, 'store'])->middleware('ability:mcp:write,mobile,taches:update');
    Route::patch ('/sous-taches/{sousTache}/toggle',      [SousTacheApiController::class, 'toggle'])->middleware('ability:mcp:write,mobile,taches:update');
    Route::delete('/sous-taches/{sousTache}',             [SousTacheApiController::class, 'destroy'])->middleware('ability:mcp:write,mobile,taches:update');

    Route::get('/analyse/synthese', [AnalyseApiController::class, 'index'])
        ->middleware('ability:mcp:read,mcp:write,mobile,taches:read');

    Route::get('/ordres-ia/projets', function (Request $request) {
        abort_unless($request->user()->isManager(), 403);

        return response()->json(['data' => config('ordres_ia.projets')]);
    })->middleware('ability:mobile,mcp:read,mcp:write');

    Route::get('/ordres-ia', [OrdreIaController::class, 'index'])
        ->middleware('ability:mcp:read,mcp:write,mobile,taches:read');
    Route::middleware('ability:mcp:write,mobile')->group(function () {
        Route::post('/ordres-ia', [OrdreIaController::class, 'store']);
        Route::post('/ordres-ia/{ordre}/prendre', [OrdreIaController::class, 'prendre']);
        Route::post('/ordres-ia/{ordre}/rendre-compte', [OrdreIaController::class, 'rendreCompte']);
        Route::post('/ordres-ia/{ordre}/annuler', [OrdreIaController::class, 'annuler']);
    });

    Route::middleware('ability:mobile')->group(function () {
        Route::post('/taches/{tache}/synchroniser', SynchronisationMobileController::class);
        Route::get('/profil/connexions', [ProfilMobileController::class, 'index']);
        Route::delete('/profil/applications/{autorisation}', [ProfilMobileController::class, 'revoquer'])->whereNumber('autorisation');
        Route::post('/push/appareils', [AppareilPushController::class, 'store']);
        Route::delete('/push/appareils', [AppareilPushController::class, 'retirer']);
        Route::delete('/push/appareils/{appareil}', [AppareilPushController::class, 'destroy']);
    });

    // Dashboard KPIs
    Route::get('/dashboard/kpis', [DashboardApiController::class, 'kpis'])->middleware('ability:mcp:read,mcp:write,mobile,taches:read');

    // Progression d'une tâche
    Route::patch('/taches/{tache}/progression', [TacheApiController::class, 'majProgression'])->middleware('ability:mcp:write,mobile,taches:update');

    // Référentiels (lecture seule)
    Route::get('/membres', fn(Request $r) => response()->json([
        'data' => User::where('type_compte', 'humain')->orderBy('nom')->get()->map(fn($u) => [
            'id'          => $u->id,
            'nom_complet' => $u->nom_complet,
            'role'        => $u->role,
        ]),
    ]));

    Route::get('/agents', fn() => response()->json([
        'data' => User::where('type_compte', 'agent_ia')->orderBy('nom')->get(['id', 'nom', 'prenom', 'agent_code', 'agent_couleur'])->map(fn($u) => [
            'id'            => $u->id,
            'nom_complet'   => $u->nom_complet,
            'agent_code'    => $u->agent_code,
            'agent_couleur' => $u->agent_couleur,
        ]),
    ]));

    Route::get('/sites', fn() => response()->json([
        'data' => Site::where('actif', true)->orderBy('nom')
            ->get(['id', 'nom', 'ville'])
            ->map(fn($s) => ['id' => $s->id, 'nom' => $s->nom, 'ville' => $s->ville, 'localisation' => $s->ville]),
    ]));

    // Rapports agents (lecture tous, écriture via scope)
    Route::get('/rapports-agents',              [RapportAgentApiController::class, 'index'])->middleware('ability:mcp:read,mcp:write,mobile,rapports:create');
    Route::post('/rapports-agents',             [RapportAgentApiController::class, 'store'])->middleware('ability:mcp:write,mobile,rapports:create');
    Route::get('/rapports-agents/{rapportAgent}',[RapportAgentApiController::class, 'show'])->middleware('ability:mcp:read,mcp:write,mobile,rapports:create');

    // Sessions agents
    Route::post('/sessions/start',  [SessionAgentApiController::class, 'start'])->middleware('ability:mcp:write,mobile,sessions:manage');
    Route::post('/sessions/end',    [SessionAgentApiController::class, 'end'])->middleware('ability:mcp:write,mobile,sessions:manage');
    Route::get('/sessions/actives', [SessionAgentApiController::class, 'actives'])->middleware('ability:mcp:read,mcp:write,mobile,sessions:manage');
});
