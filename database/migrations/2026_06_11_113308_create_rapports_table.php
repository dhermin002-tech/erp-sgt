<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rapports_agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('projet', 100)->index();
            $table->enum('type', ['session', 'sprint', 'bug', 'deploiement', 'audit', 'quotidien']);
            $table->string('titre', 250);
            $table->longText('contenu');
            $table->enum('statut', ['info', 'warning', 'erreur'])->default('info')->index();
            $table->string('fichier_md', 500)->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'created_at']);
            $table->index(['projet', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rapports_agents');
    }
};
