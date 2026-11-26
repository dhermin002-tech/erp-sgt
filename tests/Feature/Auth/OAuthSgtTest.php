<?php

namespace Tests\Feature\Auth;

use App\Models\AutorisationOAuthSgt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class OAuthSgtTest extends TestCase
{
    use RefreshDatabase;

    private const CALLBACK = 'https://claude.ai/api/mcp/auth_callback';

    private const PREUVE = 'recette-pkce-marqueur-public-0123456789-abcdefghij';

    public function test_metadonnees_inscription_et_controle_de_la_redirection(): void
    {
        $this->getJson('/.well-known/oauth-authorization-server')->assertOk()
            ->assertJsonPath('code_challenge_methods_supported', ['S256']);
        $this->getJson('/.well-known/oauth-protected-resource')->assertOk()
            ->assertJsonPath('authorization_servers.0', config('oauth_sgt.issuer'));
        $this->postJson('/oauth/register', ['client_name' => 'Claude', 'redirect_uris' => ['https://exemple.invalid/callback']])
            ->assertStatus(400)->assertJsonPath('error', 'invalid_redirect_uri');
        $client = $this->client();
        $this->demander($client)->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create());
        $this->demander($client, ['redirect_uri' => self::CALLBACK.'/autre'])->assertStatus(400);
    }

    public function test_pkce_est_obligatoire_et_le_refus_ne_cree_pas_de_code(): void
    {
        $client = $this->client();
        $this->actingAs(User::factory()->create());
        $this->demander($client, ['code_challenge_method' => 'plain'])->assertStatus(400);
        $this->demander($client, ['code_challenge_method' => null])->assertStatus(400);
        $this->demander($client)->assertOk()->assertSee('Autoriser Claude à accéder à votre SGT');
        $refus = $this->post('/oauth/authorize', [
            'nonce' => session('oauth_sgt_demande.nonce'), 'decision' => 'refuser',
        ]);
        $refus->assertStatus(303)->assertRedirect(self::CALLBACK.'?error=access_denied&state=recette');
        $this->assertDatabaseCount('oauth_sgt_autorisations', 0);
    }

    public function test_echange_code_unique_lie_au_client_et_a_la_preuve(): void
    {
        $client = $this->client();
        $autreClient = $this->client();
        $this->actingAs(User::factory()->create());
        $code = $this->code($client);
        $this->assertSame(hash('sha256', $code), AutorisationOAuthSgt::sole()->code_hash);
        $this->echanger($autreClient, $code)->assertStatus(400)->assertJsonPath('error', 'invalid_grant');
        $this->echanger($client, $code, ['code_verifier' => str_repeat('z', 64)])->assertStatus(400);
        $this->echanger($client, $code, ['redirect_uri' => self::CALLBACK.'/autre'])->assertStatus(400);
        $resultat = $this->echanger($client, $code)->assertOk()->assertJsonPath('scope', 'mcp:read')->json();
        $this->assertSame(['mcp:read'], PersonalAccessToken::findToken($resultat['access_token'])->abilities);
        $this->echanger($client, $code)->assertStatus(400)->assertJsonPath('error', 'invalid_grant');
    }

    public function test_expiration_et_rotation_avec_detection_de_rejeu(): void
    {
        $client = $this->client();
        $this->actingAs(User::factory()->create());
        $perime = $this->code($client);
        $this->travel(61)->seconds();
        $this->echanger($client, $perime)->assertStatus(400)->assertJsonPath('error', 'invalid_grant');
        $initial = $this->echanger($client, $this->code($client, true))->assertOk()
            ->assertJsonPath('scope', 'mcp:read mcp:write')->json();
        $demande = ['grant_type' => 'refresh_token', 'client_id' => $client, 'refresh_token' => $initial['refresh_token']];
        $renouvele = $this->postJson('/oauth/token', $demande)->assertOk()->json();
        $this->assertNotSame($initial['refresh_token'], $renouvele['refresh_token']);
        $this->assertNull(PersonalAccessToken::findToken($initial['access_token']));
        $this->postJson('/oauth/token', $demande)->assertStatus(400)->assertJsonPath('error', 'invalid_grant');
        $this->assertNull(PersonalAccessToken::findToken($renouvele['access_token']));
    }

    public function test_profil_revoque_une_application_et_les_jetons_deviennent_inutilisables(): void
    {
        $client = $this->client();
        $user = User::factory()->create();
        $this->actingAs($user);
        $resultat = $this->echanger($client, $this->code($client))->assertOk()->json();
        $autorisation = AutorisationOAuthSgt::sole();
        $this->get('/profil')->assertOk()->assertSee('Applications connectées')->assertSee('Claude');
        $url = '/profil/applications/'.$autorisation->id;
        $this->actingAs(User::factory()->create())->delete($url)->assertNotFound();
        $this->actingAs($user)->delete($url)->assertRedirect(route('profil.index'));
        $this->assertNull(PersonalAccessToken::findToken($resultat['access_token']));
        Auth::forgetGuards();
        $this->withToken($resultat['access_token'])->getJson('/api/v1/me')->assertUnauthorized();
    }

    public function test_changement_de_mot_de_passe_web_refuse_le_renouvellement_oauth(): void
    {
        $client = $this->client();
        $this->actingAs(User::factory()->create());
        $initial = $this->echanger($client, $this->code($client))->assertOk()->json();

        $this->patch(route('profil.updatePassword'), [
            'password_actuel' => 'password',
            'password' => 'mot-de-passe-recette-2026',
            'password_confirmation' => 'mot-de-passe-recette-2026',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertNull(PersonalAccessToken::findToken($initial['access_token']));
        $this->postJson('/oauth/token', [
            'grant_type' => 'refresh_token',
            'client_id' => $client,
            'refresh_token' => $initial['refresh_token'],
        ])->assertStatus(400)->assertJsonPath('error', 'invalid_grant');
    }

    public function test_revocation_machine_liee_au_client(): void
    {
        $client = $this->client();
        $autreClient = $this->client();
        $this->actingAs(User::factory()->create());
        $resultat = $this->echanger($client, $this->code($client))->assertOk()->json();
        $this->postJson('/oauth/revoke', ['client_id' => $autreClient, 'token' => $resultat['refresh_token']])->assertOk();
        $this->assertNotNull(PersonalAccessToken::findToken($resultat['access_token']));
        $this->postJson('/oauth/revoke', ['client_id' => $client, 'token' => $resultat['refresh_token']])->assertOk();
        $this->assertNull(PersonalAccessToken::findToken($resultat['access_token']));
    }

    public function test_acces_limite_a_une_heure_et_renouvellement_a_trente_jours(): void
    {
        $client = $this->client();
        $this->actingAs(User::factory()->create());
        $initial = $this->echanger($client, $this->code($client))->assertOk()
            ->assertJsonPath('expires_in', 3600)->json();
        $limite = AutorisationOAuthSgt::sole()->expire_le->timestamp;

        $this->travel(61)->minutes();
        Auth::forgetGuards();
        $this->withToken($initial['access_token'])->getJson('/api/v1/me')->assertUnauthorized();

        $demande = ['grant_type' => 'refresh_token', 'client_id' => $client, 'refresh_token' => $initial['refresh_token']];
        $renouvele = $this->postJson('/oauth/token', $demande)->assertOk()->assertJsonPath('expires_in', 3600)->json();
        $this->assertSame($limite, AutorisationOAuthSgt::sole()->expire_le->timestamp);

        $this->travelTo(now()->setTimestamp($limite - 60));
        $dernier = $this->postJson('/oauth/token', array_replace($demande, ['refresh_token' => $renouvele['refresh_token']]))
            ->assertOk()->assertJsonPath('expires_in', 60)->json();
        $this->travel(61)->seconds();
        $this->postJson('/oauth/token', array_replace($demande, ['refresh_token' => $dernier['refresh_token']]))
            ->assertStatus(400)->assertJsonPath('error', 'invalid_grant');
    }

    public function test_resource_avec_ou_sans_barre_et_csp_du_consentement(): void
    {
        $client = $this->client();
        $this->actingAs(User::factory()->create());
        $resource = config('oauth_sgt.resource');
        $this->demander($client, ['resource' => $resource])->assertOk();
        $consentement = $this->demander($client, ['resource' => $resource.'/'])->assertOk()
            ->assertHeader('Referrer-Policy', 'no-referrer');
        $this->assertStringContainsString("form-action 'self' https://claude.ai https://claude.com",
            $consentement->headers->get('Content-Security-Policy-Report-Only'));
        $this->demander($client, ['resource' => $resource.'/autre'])->assertStatus(400);
        $this->echanger($client, $this->code($client), ['resource' => $resource.'/'])->assertOk();
        $this->echanger($client, $this->code($client), ['resource' => $resource])->assertOk();
    }

    public function test_purge_seulement_les_codes_non_echanges_et_expires(): void
    {
        $client = $this->client();
        $this->actingAs(User::factory()->create());
        $this->echanger($client, $this->code($client))->assertOk();
        $expire = hash('sha256', $this->code($client));
        $this->travel(61)->seconds();
        $actif = hash('sha256', $this->code($client));
        $evenement = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events())
            ->firstWhere('description', 'sgt-purger-codes-oauth');
        $this->assertNotNull($evenement);
        $evenement->run(app());

        $this->assertDatabaseMissing('oauth_sgt_autorisations', ['code_hash' => $expire]);
        $this->assertDatabaseHas('oauth_sgt_autorisations', ['code_hash' => $actif]);
        $this->assertDatabaseHas('oauth_sgt_autorisations', ['code_hash' => null]);
    }

    private function client(): string
    {
        return $this->postJson('/oauth/register', ['client_name' => 'Claude', 'redirect_uris' => [self::CALLBACK]])
            ->assertCreated()->json('client_id');
    }

    private function demander(string $client, array $modifications = [])
    {
        $demande = $modifications + [
            'client_id' => $client, 'redirect_uri' => self::CALLBACK, 'response_type' => 'code',
            'code_challenge_method' => 'S256',
            'code_challenge' => rtrim(strtr(base64_encode(hash('sha256', self::PREUVE, true)), '+/', '-_'), '='),
            'state' => 'recette',
        ];

        return $this->get('/oauth/authorize?'.http_build_query($demande));
    }

    private function code(string $client, bool $modifier = false): string
    {
        $this->demander($client)->assertOk();
        $response = $this->post('/oauth/authorize', [
            'nonce' => session('oauth_sgt_demande.nonce'), 'decision' => 'accepter', 'modifier' => $modifier ? '1' : '0',
        ])->assertStatus(303);
        parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $parametres);
        $this->assertSame('recette', $parametres['state']);

        return $parametres['code'];
    }

    private function echanger(string $client, string $code, array $modifications = [])
    {
        return $this->postJson('/oauth/token', $modifications + [
            'client_id' => $client, 'grant_type' => 'authorization_code', 'code' => $code,
            'code_verifier' => self::PREUVE, 'redirect_uri' => self::CALLBACK,
        ]);
    }
}
