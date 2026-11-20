<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tache_statuts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tache_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('acteur_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->string('ancien', 20);
            $table->string('nouveau', 20);
            $table->timestamp('created_at');
            $table->index(['tache_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tache_statuts');
    }
};
