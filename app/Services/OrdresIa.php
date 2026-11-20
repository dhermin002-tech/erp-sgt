<?php

namespace App\Services;

use App\Models\OrdreIa;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrdresIa
{
    public function emettre(array $donnees, User $manager): OrdreIa
    {
        abort_unless($manager->isManager(), 403, 'Seul le manager peut émettre un ordre.');

        return OrdreIa::create(['emis_par' => $manager->id] + $donnees);
    }

    public function prendre(OrdreIa $ordre, User $agent, string $session): OrdreIa
    {
        return DB::transaction(function () use ($ordre, $agent, $session) {
            $modifie = OrdreIa::whereKey($ordre->id)->where('statut', 'a_executer')->update([
                'statut' => 'pris',
                'pris_par_user_id' => $agent->id,
                'pris_par_agent' => $agent->agent_code,
                'session_id' => $session,
                'pris_le' => now(),
            ]);
            abort_unless($modifie === 1, 409, 'Cet ordre a déjà été pris ou annulé.');

            return $ordre->refresh();
        }, 3);
    }

    public function rendreCompte(OrdreIa $ordre, User $agent, array $donnees): OrdreIa
    {
        $ordre = DB::transaction(function () use ($ordre, $agent, $donnees) {
            $modifie = OrdreIa::whereKey($ordre->id)
                ->where('statut', 'pris')->where('pris_par_user_id', $agent->id)
                ->where('session_id', $donnees['session_id'])
                ->update([
                    'statut' => $donnees['statut'],
                    'compte_rendu' => $donnees['compte_rendu'],
                    'termine_le' => now(),
                ]);
            abort_unless($modifie === 1, 409, 'Ordre non pris par cette session ou déjà clôturé.');

            $ordre->tache->commentaires()->create([
                'user_id' => $agent->id,
                'contenu' => $donnees['compte_rendu'],
            ]);

            return $ordre->refresh();
        }, 3);

        app(NotificationPush::class)->ordreRendu($ordre);

        return $ordre;
    }

    public function annuler(OrdreIa $ordre): OrdreIa
    {
        $modifie = OrdreIa::whereKey($ordre->id)->whereIn('statut', ['a_executer', 'pris'])->update([
            'statut' => 'annule',
            'termine_le' => now(),
        ]);
        abort_unless($modifie === 1, 409, 'Cet ordre est déjà clôturé.');

        return $ordre->refresh();
    }
}
