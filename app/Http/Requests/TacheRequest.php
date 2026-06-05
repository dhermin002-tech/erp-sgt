<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TacheRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titre'          => 'required|string|max:255',
            'description'    => 'nullable|string|max:5000',
            'responsables'   => 'required|array|min:1',
            'responsables.*' => 'exists:users,id',
            'site_id'        => 'nullable|exists:sites,id',
            'date_debut'     => 'nullable|date',
            'date_echeance'  => 'nullable|date|after_or_equal:date_debut',
            'statut'         => 'required|in:nouveau,en_cours,en_attente,en_arret,termine',
            'progression'    => 'required|integer|min:0|max:100',
            'priorite'       => 'required|in:basse,normale,haute,urgente',
        ];
    }

    public function messages(): array
    {
        return [
            'titre.required'          => 'Le titre est obligatoire.',
            'responsables.required'   => 'Au moins un responsable est requis.',
            'responsables.*.exists'   => 'Un responsable sélectionné est invalide.',
            'date_echeance.after_or_equal' => 'La date d\'échéance doit être après la date de début.',
        ];
    }
}
