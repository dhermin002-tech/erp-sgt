<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actions_mobiles_synchronisees', function (Blueprint $table) {
            $table->foreignId('tache_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('operation', 40);
            $table->primary(['tache_id', 'user_id', 'operation']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actions_mobiles_synchronisees');
    }
};
