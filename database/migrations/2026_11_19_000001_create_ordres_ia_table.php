<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordres_ia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tache_id')->constrained('taches')->cascadeOnDelete()->cascadeOnUpdate();
            $table->enum('cible', ['claude_code', 'codex', 'tout_agent']);
            $table->string('projet_dossier', 100);
            $table->text('consigne');
            $table->enum('statut', ['a_executer', 'pris', 'fait', 'rejete', 'annule'])->default('a_executer');
            $table->foreignId('emis_par')->constrained('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('pris_par_user_id')->nullable()->constrained('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('pris_par_agent', 40)->nullable();
            $table->string('session_id', 100)->nullable();
            $table->timestamp('pris_le')->nullable();
            $table->timestamp('termine_le')->nullable();
            $table->text('compte_rendu')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['statut', 'cible', 'projet_dossier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordres_ia');
    }
};
