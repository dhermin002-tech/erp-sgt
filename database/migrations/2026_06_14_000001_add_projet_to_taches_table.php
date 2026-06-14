<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('taches', function (Blueprint $table) {
            // Projet d'appartenance de la tâche (SGT, SGG, CUMCCO...) — pour filtrage et identification
            $table->string('projet', 40)->nullable()->index()->after('titre');
        });
    }

    public function down(): void
    {
        Schema::table('taches', function (Blueprint $table) {
            $table->dropColumn('projet');
        });
    }
};
