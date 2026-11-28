<?php
// Contrôle des notifications après DEPS-001 : n'affiche aucun secret, seulement OK / ERREUR.
chdir('/var/www/erp-sgt');
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$ok = true;
try {
    $scope = 'https://www.googleapis.com/auth/firebase.messaging';
    $chemin = config('push.fcm.identifiants');
    $id = $chemin
        ? new Google\Auth\Credentials\ServiceAccountCredentials($scope, $chemin)
        : Google\Auth\ApplicationDefaultCredentials::getCredentials($scope);
    $r = $id->fetchAuthToken();
    $jetonOk = is_array($r) && !empty($r['access_token']);
    echo 'FCM (projet ' . config('push.fcm.projet') . ') : ' . ($jetonOk ? 'jeton obtenu OK' : 'ERREUR') . PHP_EOL;
    $ok = $ok && $jetonOk;
} catch (Throwable $e) {
    echo 'FCM : ERREUR ' . get_class($e) . PHP_EOL;
    $ok = false;
}
try {
    new Minishlink\WebPush\WebPush(['VAPID' => config('push.vapid')]);
    echo 'Web Push (VAPID) : clés chargées OK' . PHP_EOL;
} catch (Throwable $e) {
    echo 'Web Push : ERREUR ' . get_class($e) . PHP_EOL;
    $ok = false;
}
exit($ok ? 0 : 1);
