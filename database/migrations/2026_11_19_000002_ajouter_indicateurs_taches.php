<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('taches', function (Blueprint $table) {
            $table->timestamp('terminee_at')->nullable();
        });
        DB::table('taches')->where('statut', 'termine')->update([
            'terminee_at' => DB::raw('COALESCE(updated_at, created_at)'),
        ]);

        Schema::table('sous_taches', fn (Blueprint $table) => $table->index(['tache_id', 'termine'], 'idx_sous_taches_compteurs'));

        if (DB::getDriverName() === 'sqlite') {
            Schema::table('tache_user', fn (Blueprint $table) => $table->index('user_id', 'idx_tache_user_responsable'));
        }
    }

    public function down(): void
    {
        Schema::table('sous_taches', fn (Blueprint $table) => $table->dropIndex('idx_sous_taches_compteurs'));
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('tache_user', fn (Blueprint $table) => $table->dropIndex('idx_tache_user_responsable'));
        }
        Schema::table('taches', fn (Blueprint $table) => $table->dropColumn('terminee_at'));
    }
};
