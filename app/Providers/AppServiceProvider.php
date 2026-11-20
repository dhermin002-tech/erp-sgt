<?php

namespace App\Providers;

use App\Services\TransportPush;
use App\Services\TransportPushHttp;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TransportPush::class, TransportPushHttp::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('oauth-sgt', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));

        RateLimiter::for('emission-jeton', function (Request $request) {
            $username = $request->input('username');
            $identifiant = is_string($username) ? Str::lower(trim($username)) : '';

            return Limit::perMinute(5)->by(hash('sha256', $request->ip().'|'.$identifiant));
        });

        PersonalAccessToken::created(fn (PersonalAccessToken $jeton) => Log::info(
            'Jeton Sanctum émis.',
            ['jeton_id' => $jeton->getKey(), 'compte_id' => $jeton->tokenable_id],
        ));
        PersonalAccessToken::deleted(fn (PersonalAccessToken $jeton) => Log::info(
            'Jeton Sanctum révoqué.',
            ['jeton_id' => $jeton->getKey(), 'compte_id' => $jeton->tokenable_id],
        ));
    }
}
