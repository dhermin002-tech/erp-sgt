<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tache;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SynchronisationMobileController extends Controller
{
    public function __invoke(Request $request, Tache $tache, TacheApiController $taches, SousTacheApiController $sousTaches): Response
    {
        $type = $request->validate(['type' => ['required', 'in:statut,progression,sousTache,commentaire,rapport']])['type'];
        if ($type === 'commentaire' && is_string($request->input('contenu'))) {
            $request->merge(['contenu' => trim(strip_tags($request->input('contenu')))]);
        }
        $regles = match ($type) {
            'commentaire', 'rapport' => ['operation' => ['required', 'alpha_num:ascii', 'max:40'], 'contenu' => ['required', 'string', 'max:5000']],
            'statut' => [
                'avant' => ['required', Rule::in(['nouveau', 'en_cours', 'en_attente', 'en_arret', 'termine'])],
                'statut' => ['required', Rule::in(['nouveau', 'en_cours', 'en_attente', 'en_arret', 'termine'])],
            ],
            'progression' => ['avant' => ['required', 'integer', 'between:0,100'], 'progression' => ['required', 'integer', 'between:0,100']],
            'sousTache' => ['avant' => ['required', 'boolean'], 'termine' => ['required', 'boolean'], 'id' => ['required', 'integer', 'min:1']],
        };
        $request->validate($regles);

        DB::transaction(function () use ($request, $tache, $type, $taches, $sousTaches) {
            $tache = Tache::query()->lockForUpdate()->findOrFail($tache->id);
            abort_unless($request->user()->canAccessTache($tache), 403);
            if (in_array($type, ['commentaire', 'rapport'], true)) {
                $inseree = DB::table('actions_mobiles_synchronisees')->insertOrIgnore([
                    'tache_id' => $tache->id, 'user_id' => $request->user()->id, 'operation' => $request->input('operation'),
                ]);
                if ($inseree === 1) {
                    $relation = $type === 'commentaire' ? $tache->commentaires() : $tache->rapports();
                    $relation->create(['user_id' => $request->user()->id, 'contenu' => $request->input('contenu')]);
                }

                return;
            }
            if ($type === 'sousTache') {
                $sousTache = $tache->sousTaches()->lockForUpdate()->findOrFail($request->integer('id'));
                if ((bool) $sousTache->termine === $request->boolean('termine')) {
                    return;
                }
                abort_unless((bool) $sousTache->termine === $request->boolean('avant'), 409);
                $sousTaches->toggle($request, $sousTache);

                return;
            }

            $valeur = $type === 'statut' ? $request->string('statut')->toString() : $request->integer('progression');
            $avant = $type === 'statut' ? $request->string('avant')->toString() : $request->integer('avant');
            $actuel = $type === 'statut' ? $tache->statut : (int) $tache->progression;
            if ($type === 'statut' && $valeur === 'termine') {
                abort_unless($request->user()->isManager(), 403);
            }
            if ($actuel === $valeur) {
                return;
            }
            abort_unless($actuel === $avant, 409);
            $response = $type === 'statut' ? $taches->changerStatut($request, $tache) : $taches->majProgression($request, $tache);
            abort_if($response->getStatusCode() >= 400, $response->getStatusCode());
        }, 3);

        return response()->noContent();
    }
}
