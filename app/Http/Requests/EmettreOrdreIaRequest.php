<?php

namespace App\Http\Requests;

use App\Models\OrdreIa;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmettreOrdreIaRequest extends FormRequest
{
    protected $errorBag = 'ordreIa';

    public function authorize(): bool
    {
        return $this->user()?->isManager() ?? false;
    }

    public function rules(): array
    {
        return [
            'cible' => ['required', Rule::in(OrdreIa::CIBLES)],
            'projet_dossier' => ['required', Rule::in(array_keys(config('ordres_ia.projets')))],
            'consigne' => ['required', 'string', 'max:10000'],
        ];
    }
}
