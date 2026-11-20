<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oauth_sgt_clients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nom', 120);
            $table->json('redirect_uris');
            $table->timestamps();
        });
        Schema::create('oauth_sgt_autorisations', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('client_id')->constrained('oauth_sgt_clients')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('redirect_uri', 500);
            $table->string('challenge', 43);
            $table->string('code_hash', 64)->nullable()->unique();
            $table->timestamp('code_expire_le');
            $table->json('abilities');
            $table->timestamp('expire_le')->nullable()->index();
            $table->foreignId('jeton_id')->nullable()->constrained('personal_access_tokens')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });
        Schema::create('oauth_sgt_renouvellements', function (Blueprint $table) {
            $table->string('hash', 64)->primary();
            $table->foreignId('autorisation_id')->constrained('oauth_sgt_autorisations',
                indexName: 'oauth_renouvellements_autorisation_fk')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamp('utilise_le')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oauth_sgt_renouvellements');
        Schema::dropIfExists('oauth_sgt_autorisations');
        Schema::dropIfExists('oauth_sgt_clients');
    }
};
