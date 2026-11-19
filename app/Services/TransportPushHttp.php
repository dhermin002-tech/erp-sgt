<?php

namespace App\Services;

use App\Models\AppareilPush;
use Google\Auth\ApplicationDefaultCredentials;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use RuntimeException;
use Throwable;

class TransportPushHttp implements TransportPush
{
    public function envoyer(AppareilPush $appareil, array $message): bool
    {
        try {
            return $appareil->plateforme === 'android'
                ? $this->envoyerAndroid($appareil->destination['jeton'], $message)
                : $this->envoyerWeb($appareil->destination, $message);
        } catch (Throwable) {
            throw new RuntimeException('Échec du transport de notification '.$appareil->plateforme.'.');
        }
    }

    private function envoyerAndroid(string $jeton, array $message): bool
    {
        $projet = config('push.fcm.projet');
        if (! is_string($projet) || ! preg_match('/\A[a-z0-9-]+\z/', $projet)) {
            throw new RuntimeException('Projet FCM invalide.');
        }

        $accesChiffre = Cache::remember('sgt:push:fcm:'.$projet, 3000, function () {
            $scope = 'https://www.googleapis.com/auth/firebase.messaging';
            $chemin = config('push.fcm.identifiants');
            $identifiants = $chemin
                ? new ServiceAccountCredentials($scope, $chemin)
                : ApplicationDefaultCredentials::getCredentials($scope);
            $reponse = $identifiants->fetchAuthToken();

            return Crypt::encryptString($reponse['access_token'] ?? throw new RuntimeException('Accès FCM indisponible.'));
        });

        $reponse = Http::withToken(Crypt::decryptString($accesChiffre))->connectTimeout(3)->timeout(8)->withoutRedirecting()
            ->post("https://fcm.googleapis.com/v1/projects/{$projet}/messages:send", [
                'message' => [
                    'token' => $jeton,
                    'notification' => ['title' => $message['titre'], 'body' => $message['texte']],
                    'data' => ['tache_id' => (string) $message['tache_id'], 'type' => $message['type']],
                ],
            ]);

        if (collect($reponse->json('error.details', []))->contains('errorCode', 'UNREGISTERED')) {
            return false;
        }

        if ($reponse->status() === 401) {
            Cache::forget('sgt:push:fcm:'.$projet);
        }
        if (! $reponse->successful()) {
            throw new RuntimeException('Rejet FCM.');
        }

        return true;
    }

    private function envoyerWeb(array $destination, array $message): bool
    {
        if (! AppareilPush::endpointAutorise($destination['endpoint'])) {
            return false;
        }

        $transport = new WebPush(['VAPID' => config('push.vapid')], ['TTL' => 300, 'urgency' => 'normal'], 8, [
            'connect_timeout' => 3,
            'allow_redirects' => false,
        ]);
        $rapport = $transport->sendOneNotification(Subscription::create($destination), json_encode($message, JSON_THROW_ON_ERROR));
        if ($rapport->isSubscriptionExpired()) {
            return false;
        }
        if (! $rapport->isSuccess()) {
            throw new RuntimeException('Rejet Web Push.');
        }

        return true;
    }
}
