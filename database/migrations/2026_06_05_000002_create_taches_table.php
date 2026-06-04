<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taches', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->foreignId('createur_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('site_id')->nullable()->constrained('sites')->onDelete('set null');
            $table->date('date_debut')->nullable();
            $table->date('date_echeance')->nullable();
            $table->enum('statut', ['nouveau', 'en_cours', 'en_attente', 'en_arret', 'termine'])->default('nouveau');
            $table->unsignedTinyInteger('progression')->default(0);
            $table->enum('priorite', ['basse', 'normale', 'haute', 'urgente'])->default('normale');
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['statut', 'deleted_at']);
            $table->index(['date_echeance', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taches');
    }
};
