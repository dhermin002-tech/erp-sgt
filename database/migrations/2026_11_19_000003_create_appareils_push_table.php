<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appareils_push', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->enum('plateforme', ['android', 'web']);
            $table->string('empreinte', 64)->unique();
            $table->text('destination');
            $table->timestamp('dernier_usage');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appareils_push');
    }
};
