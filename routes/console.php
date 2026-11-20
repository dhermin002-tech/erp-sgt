<?php

use App\Models\AutorisationOAuthSgt;
use App\Services\NotificationPush;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(fn () => app(NotificationPush::class)->echeancesDemain())
    ->name('sgt-push-echeances-j1')
    ->dailyAt('08:00')->timezone('Africa/Libreville')->onOneServer()->withoutOverlapping();

Schedule::call(fn () => AutorisationOAuthSgt::query()->whereNotNull('code_hash')
    ->where('code_expire_le', '<=', now())->delete())
    ->name('sgt-purger-codes-oauth')->hourly()->onOneServer()->withoutOverlapping();
