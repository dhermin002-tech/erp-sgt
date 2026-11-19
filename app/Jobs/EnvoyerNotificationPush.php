<?php

namespace App\Jobs;

use App\Models\AppareilPush;
use App\Models\User;
use App\Services\TransportPush;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class EnvoyerNotificationPush implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(public int $appareilId, public array $message) {}

    public function handle(TransportPush $transport): void
    {
        $appareil = AppareilPush::find($this->appareilId);
        if (! $appareil || ! User::whereKey($appareil->user_id)->exists()) {
            return;
        }
        if (! $transport->envoyer($appareil, $this->message)) {
            $appareil->delete();

            return;
        }

        $appareil->update(['dernier_usage' => now()]);
    }
}
