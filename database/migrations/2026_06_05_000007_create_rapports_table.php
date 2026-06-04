<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rapports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tache_id')->constrained('taches')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->text('contenu');
            $table->date('date_intervention')->nullable();
            $table->timestamps();

            $table->index('tache_id');
        });

        Schema::create('actions_suivi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tache_id')->constrained('taches')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->string('description');
            $table->boolean('fait')->default(false);
            $table->timestamps();

            $table->index('tache_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actions_suivi');
        Schema::dropIfExists('rapports');
    }
};
