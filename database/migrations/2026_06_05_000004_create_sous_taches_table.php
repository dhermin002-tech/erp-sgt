<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sous_taches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tache_id')->constrained('taches')->onDelete('cascade');
            $table->string('titre');
            $table->boolean('termine')->default(false);
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->timestamps();

            $table->index(['tache_id', 'ordre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sous_taches');
    }
};
