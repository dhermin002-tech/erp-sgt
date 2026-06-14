<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Backfill ciblé : assigne le projet aux tâches dont le titre commence par
     * un préfixe de PROJET connu (pas un code tâche comme [QA-001] ou [SYNC-P0]).
     */
    public function up(): void
    {
        $projetsConnus = ['SGT', 'SGG', 'CUMCCO', 'CUMC-CO', 'CUMC', 'ASSG', 'AAUTOMARKET', 'AAUTO'];

        foreach ($projetsConnus as $projet) {
            DB::table('taches')
                ->whereNull('projet')
                ->where('titre', 'like', '[' . $projet . ']%')
                ->update(['projet' => $projet]);
        }
    }

    public function down(): void
    {
        // Backfill non réversible (données dérivées du titre).
    }
};
