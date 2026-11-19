<?php

namespace App\Http\Requests;

use App\Models\AppareilPush;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class AppareilPushRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'plateforme' => ['required', 'in:android,web'],
            'jeton' => ['exclude_unless:plateforme,android', 'required', 'string', 'max:4096'],
            'abonnement' => ['exclude_unless:plateforme,web', 'required', 'array:endpoint,keys'],
            'abonnement.endpoint' => [
                'exclude_unless:plateforme,web', 'required', 'string', 'max:2048',
                function (string $champ, mixed $valeur, Closure $echec): void {
                    if (is_string($valeur) && ! AppareilPush::endpointAutorise($valeur)) {
                        $echec('Le serveur de notification Web Push est invalide.');
                    }
                },
            ],
            'abonnement.keys' => ['exclude_unless:plateforme,web', 'required', 'array:auth,p256dh'],
            'abonnement.keys.auth' => ['exclude_unless:plateforme,web', 'required', 'regex:/\A[A-Za-z0-9_-]{22}={0,2}\z/'],
            'abonnement.keys.p256dh' => ['exclude_unless:plateforme,web', 'required', 'regex:/\A[A-Za-z0-9_-]{87}={0,2}\z/'],
        ];
    }
}
