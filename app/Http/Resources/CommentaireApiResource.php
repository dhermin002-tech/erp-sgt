<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentaireApiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'contenu' => $this->contenu,
            'auteur' => $this->user ? [
                'id' => $this->user->id,
                'nom_complet' => $this->user->nom_complet,
            ] : null,
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
