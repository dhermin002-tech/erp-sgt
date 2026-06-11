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
        Schema::create('sessions_agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('projet', 100)->index();
            $table->string('contexte', 500)->nullable();
            $table->timestamp('demarree_a');
            $table->timestamp('terminee_a')->nullable();
            $table->enum('statut', ['en_cours', 'terminee', 'interrompue'])->default('en_cours');
            $table->json('resume')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'statut']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions_agents');
    }
};
