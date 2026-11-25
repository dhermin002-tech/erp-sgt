<?php

namespace Tests\Feature\Membres;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MotDePasseMembreTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_refuse_un_champ_mot_de_passe_meme_vide(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $hash = $manager->password;
        Sanctum::actingAs($manager, ['mobile']);

        foreach (['mot-de-passe-factice', null] as $password) {
            $this->patchJson('/api/v1/gestion/membres/'.$manager->id, [
                'nom' => 'Recette', 'role' => 'manager',
                'password' => $password, 'password_confirmation' => $password,
            ])->assertUnprocessable()->assertJsonValidationErrors('password');
        }
        $this->assertSame($hash, $manager->fresh()->password);
    }

    public function test_changer_le_mot_de_passe_depuis_le_web_revoque_les_jetons_du_membre(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $membre = User::factory()->create(['role' => 'technicien']);
        $membre->createToken('recette mobile', ['mobile']);
        $membre->createToken('recette MCP', ['mcp:read']);
        $jetonManager = $manager->createToken('recette manager', ['mobile']);

        $this->actingAs($manager)->patch(route('membres.update', $membre), [
            'nom' => $membre->nom, 'role' => $membre->role,
            'password' => 'nouveau-factice', 'password_confirmation' => 'nouveau-factice',
        ])->assertRedirect(route('membres.index'))->assertSessionHas('success');

        $this->assertTrue(Hash::check('nouveau-factice', $membre->fresh()->password));
        $this->assertSame(0, $membre->tokens()->count());
        $this->assertDatabaseHas('personal_access_tokens', ['id' => $jetonManager->accessToken->id]);
    }
}
