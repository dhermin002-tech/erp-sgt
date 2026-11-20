<?php

namespace App\Services;

use App\Models\AutorisationOAuthSgt;
use App\Models\ClientOAuthSgt;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;

class OAuthSgt
{
    public static function redirectAutorisee(string $uri): bool
    {
        if (in_array($uri, config('oauth_sgt.redirect_uris'), true)) {
            return true;
        }

        return app()->environment('local', 'testing')
            && filter_var($uri, FILTER_VALIDATE_URL)
            && preg_match('~\Ahttp://localhost(?::[0-9]{1,5})?/[A-Za-z0-9/_\-.]*\z~D', $uri) === 1;
    }

    public function code(ClientOAuthSgt $client, User $utilisateur, array $demande, bool $ecriture): string
    {
        $code = bin2hex(random_bytes(32));
        AutorisationOAuthSgt::create([
            'client_id' => $client->id,
            'user_id' => $utilisateur->id,
            'redirect_uri' => $demande['redirect_uri'],
            'challenge' => $demande['code_challenge'],
            'code_hash' => hash('sha256', $code),
            'code_expire_le' => now()->addSeconds(60),
            'abilities' => $ecriture ? ['mcp:read', 'mcp:write'] : ['mcp:read'],
        ]);

        return $code;
    }

    public function echanger(array $demande): ?array
    {
        return DB::transaction(function () use ($demande) {
            $autorisation = AutorisationOAuthSgt::query()->where('client_id', $demande['client_id'])
                ->where('code_hash', hash('sha256', $demande['code']))->lockForUpdate()->first();
            $preuve = rtrim(strtr(base64_encode(hash('sha256', $demande['code_verifier'], true)), '+/', '-_'), '=');
            if (! $autorisation || $autorisation->code_expire_le->lte(now())
                || $autorisation->redirect_uri !== $demande['redirect_uri']
                || ! hash_equals($autorisation->challenge, $preuve) || ! $autorisation->utilisateur) {
                return null;
            }

            $autorisation->update(['code_hash' => null, 'expire_le' => now()->addDays(30)]);

            return $this->emettre($autorisation);
        }, 3);
    }

    public function renouveler(array $demande): ?array
    {
        return DB::transaction(function () use ($demande) {
            $hash = hash('sha256', $demande['refresh_token']);
            $table = DB::table('oauth_sgt_renouvellements');
            $autorisationId = (clone $table)->where('hash', $hash)->value('autorisation_id');
            if (! $autorisationId) {
                return null;
            }
            $autorisation = AutorisationOAuthSgt::query()->whereKey($autorisationId)
                ->where('client_id', $demande['client_id'])->lockForUpdate()->first();
            $ancien = (clone $table)->where('hash', $hash)->lockForUpdate()->first();

            if (! $autorisation || ! $ancien || $autorisation->expire_le?->lte(now()) || ! $autorisation->utilisateur) {
                return null;
            }
            if ($ancien->utilise_le !== null || ! $autorisation->jeton_id) {
                $this->supprimer($autorisation);

                return null;
            }

            (clone $table)->where('hash', $hash)->update(['utilise_le' => now()]);

            return $this->emettre($autorisation);
        }, 3);
    }

    public function revoquerJeton(string $client, string $valeur): void
    {
        DB::transaction(function () use ($client, $valeur) {
            $jeton = PersonalAccessToken::findToken($valeur);
            $query = AutorisationOAuthSgt::where('client_id', $client);
            if ($jeton) {
                $query->where('jeton_id', $jeton->id);
            } else {
                $query->whereIn('id', DB::table('oauth_sgt_renouvellements')
                    ->where('hash', hash('sha256', $valeur))->select('autorisation_id'));
            }
            $autorisation = $query->lockForUpdate()->first();
            if ($autorisation) {
                $this->supprimer($autorisation);
            }
        }, 3);
    }

    public function revoquerApplication(User $utilisateur, int $id): void
    {
        DB::transaction(function () use ($utilisateur, $id) {
            $autorisation = AutorisationOAuthSgt::where('user_id', $utilisateur->id)
                ->lockForUpdate()->findOrFail($id);
            $this->supprimer($autorisation);
        }, 3);
    }

    private function emettre(AutorisationOAuthSgt $autorisation): array
    {
        $ancien = $autorisation->jeton()->first();
        $expiration = now()->addHour()->min($autorisation->expire_le);
        $jeton = $autorisation->utilisateur->createToken(
            'OAuth SGT : '.$autorisation->client->nom,
            $autorisation->abilities,
            $expiration,
        );
        $autorisation->update(['jeton_id' => $jeton->accessToken->id]);
        $ancien?->delete();

        $refresh = bin2hex(random_bytes(32));
        DB::table('oauth_sgt_renouvellements')->insert([
            'hash' => hash('sha256', $refresh), 'autorisation_id' => $autorisation->id,
        ]);

        return [
            'access_token' => explode('|', $jeton->plainTextToken, 2)[1],
            'token_type' => 'Bearer',
            'expires_in' => max(0, $expiration->getTimestamp() - now()->getTimestamp()),
            'refresh_token' => $refresh,
            'scope' => implode(' ', $autorisation->abilities),
        ];
    }

    private function supprimer(AutorisationOAuthSgt $autorisation): void
    {
        $autorisation->jeton()->first()?->delete();
        $autorisation->delete();
    }
}
