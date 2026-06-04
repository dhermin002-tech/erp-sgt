<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commentaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tache_id')->constrained('taches')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->text('contenu');
            $table->string('photo_path')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('tache_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commentaires');
    }
};
