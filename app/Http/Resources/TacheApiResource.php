<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TacheApiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'titre'         => $this->titre,
            'description'   => $this->description,
            'statut'        => $this->statut,
            'statut_libelle'=> \App\Models\Tache::libelleStatut($this->statut),
            'priorite'      => $this->priorite,
            'progression'   => $this->progression,
            'est_en_retard' => $this->estEnRetard(),
            'date_debut'    => $this->date_debut?->format('Y-m-d'),
            'date_echeance' => $this->date_echeance?->format('Y-m-d'),
            'archived_at'   => $this->archived_at?->toISOString(),
            'created_at'    => $this->created_at->toISOString(),
            'site'          => $this->whenLoaded('site', fn() => [
                'id'  => $this->site->id,
                'nom' => $this->site->nom,
            ]),
            'createur'      => $this->whenLoaded('createur', fn() => [
                'id'         => $this->createur->id,
                'nom_complet'=> $this->createur->nom_complet,
            ]),
            'responsables'  => $this->whenLoaded('responsables', fn() =>
                $this->responsables->map(fn($r) => [
                    'id'         => $r->id,
                    'nom_complet'=> $r->nom_complet,
                    'role'       => $r->role,
                ])
            ),
            'sous_taches_total'    => $this->whenLoaded('sousTaches', fn() => $this->sousTaches->count()),
            'sous_taches_terminees'=> $this->whenLoaded('sousTaches', fn() => $this->sousTaches->where('termine', true)->count()),
        ];
    }
}
