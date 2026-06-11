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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('type_compte', ['humain', 'agent_ia'])->default('humain')->after('role');
            $table->string('agent_code', 40)->nullable()->after('type_compte');
            $table->string('agent_couleur', 7)->nullable()->after('agent_code');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['type_compte', 'agent_code', 'agent_couleur']);
        });
    }
};
