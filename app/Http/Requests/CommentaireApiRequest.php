<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentaireApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->canAccessTache($this->route('tache'));
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('contenu'))) {
            $this->merge(['contenu' => trim(strip_tags($this->input('contenu')))]);
        }
    }

    public function rules(): array
    {
        return ['contenu' => ['required', 'string', 'max:5000']];
    }
}
