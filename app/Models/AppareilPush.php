<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppareilPush extends Model
{
    protected $table = 'appareils_push';

    protected $fillable = ['user_id', 'plateforme', 'empreinte', 'destination', 'dernier_usage'];

    protected $hidden = ['empreinte', 'destination'];

    protected function casts(): array
    {
        return ['destination' => 'encrypted:array', 'dernier_usage' => 'datetime'];
    }

    public static function endpointAutorise(string $endpoint): bool
    {
        if (! filter_var($endpoint, FILTER_VALIDATE_URL)) {
            return false;
        }

        $url = parse_url($endpoint);
        $hote = strtolower($url['host'] ?? '');

        return ($url['scheme'] ?? null) === 'https'
            && ($url['port'] ?? 443) === 443
            && ! array_intersect_key($url, array_flip(['user', 'pass', 'fragment']))
            && (in_array($hote, ['fcm.googleapis.com', 'updates.push.services.mozilla.com', 'web.push.apple.com'], true)
                || str_ends_with($hote, '.notify.windows.com')
                || str_ends_with($hote, '.push.apple.com'));
    }
}
