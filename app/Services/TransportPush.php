<?php

namespace App\Services;

use App\Models\AppareilPush;

interface TransportPush
{
    /**
     * Retourne false si l'appareil est définitivement désinscrit.
     */
    public function envoyer(AppareilPush $appareil, array $message): bool;
}
