<?php

namespace App\Http\Controllers;

use App\Models\AutorisationOAuthSgt;
use App\Models\ClientOAuthSgt;
use App\Services\OAuthSgt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OAuthSgtController extends Controller
{
    public function metadata(): JsonResponse
    {
        $issuer = rtrim(config('oauth_sgt.issuer'), '/');

        return response()->json([
            'issuer' => $issuer,
            'authorization_endpoint' => $issuer.'/oauth/authorize',
            'token_endpoint' => $issuer.'/oauth/token',
            'registration_endpoint' => $issuer.'/oauth/register',
            'revocation_endpoint' => $issuer.'/oauth/revoke',
            'response_types_supported' => ['code'],
            'grant_types_supported' => ['authorization_code', 'refresh_token'],
            'code_challenge_methods_supported' => ['S256'],
            'token_endpoint_auth_methods_supported' => ['none'],
            'revocation_endpoint_auth_methods_supported' => ['none'],
            'scopes_supported' => ['mcp:read', 'mcp:write'],
        ]);
    }

    public function resource(): JsonResponse
    {
        return response()->json([
            'resource' => config('oauth_sgt.resource'),
            'authorization_servers' => [rtrim(config('oauth_sgt.issuer'), '/')],
            'scopes_supported' => ['mcp:read', 'mcp:write'],
            'bearer_methods_supported' => ['header'],
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'client_name' => ['required', 'string', 'max:120'],
            'redirect_uris' => ['required', 'array', 'min:1', 'max:5'],
            'redirect_uris.*' => ['required', 'distinct', 'string', 'max:500'],
            'token_endpoint_auth_method' => ['sometimes', Rule::in(['none'])],
        ]);
        if ($validation->fails()) {
            return $this->json(['error' => 'invalid_client_metadata'], 400);
        }
        $donnees = $validation->validated();
        foreach ($donnees['redirect_uris'] as $uri) {
            if (! OAuthSgt::redirectAutorisee($uri)) {
                return $this->json(['error' => 'invalid_redirect_uri'], 400);
            }
        }

        $client = ClientOAuthSgt::create(['nom' => $donnees['client_name'], 'redirect_uris' => array_values($donnees['redirect_uris'])]);

        return $this->json([
            'client_id' => $client->id,
            'client_name' => $client->nom,
            'redirect_uris' => $client->redirect_uris,
            'token_endpoint_auth_method' => 'none',
            'grant_types' => ['authorization_code', 'refresh_token'],
            'response_types' => ['code'],
        ], 201);
    }

    public function consentement(Request $request)
    {
        $validation = Validator::make($request->query(), [
            'client_id' => ['required', 'uuid'],
            'redirect_uri' => ['required', 'string', 'max:500'],
            'response_type' => ['required', Rule::in(['code'])],
            'code_challenge_method' => ['required', Rule::in(['S256'])],
            'code_challenge' => ['required', 'regex:/\A[A-Za-z0-9_-]{43}\z/'],
            'state' => ['sometimes', 'string', 'max:1024'],
            'scope' => ['sometimes', 'string', 'max:100'],
            'resource' => ['sometimes', Rule::in([config('oauth_sgt.resource')])],
        ]);
        abort_if($validation->fails(), 400, 'Demande OAuth invalide.');
        $demande = $validation->validated();
        $client = ClientOAuthSgt::find($demande['client_id']);
        abort_unless($client && in_array($demande['redirect_uri'], $client->redirect_uris, true)
            && OAuthSgt::redirectAutorisee($demande['redirect_uri']), 400, 'Client ou redirection OAuth non autorisés.');
        $scopes = isset($demande['scope']) ? preg_split('/\s+/', trim($demande['scope'])) : ['mcp:read', 'mcp:write'];
        abort_if(array_diff($scopes, ['mcp:read', 'mcp:write']) !== [], 400, 'Portée OAuth invalide.');
        $nonce = bin2hex(random_bytes(16));
        $demande['nonce'] = $nonce;
        $demande['user_id'] = $request->user()->id;
        $demande['limite'] = now()->addMinutes(10)->timestamp;
        $demande['ecriture_disponible'] = in_array('mcp:write', $scopes, true);
        $request->session()->put('oauth_sgt_demande', $demande);

        return response()->view('oauth.consentement', ['client' => $client, 'demande' => $demande])
            ->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer');
    }

    public function autoriser(Request $request, OAuthSgt $oauth)
    {
        $formulaire = $request->validate([
            'nonce' => ['required', 'string'],
            'decision' => ['required', Rule::in(['accepter', 'refuser'])],
            'modifier' => ['sometimes', 'boolean'],
        ]);
        $demande = $request->session()->get('oauth_sgt_demande');
        abort_unless($demande && $demande['user_id'] === $request->user()->id
            && $demande['limite'] > now()->timestamp && hash_equals($demande['nonce'], $formulaire['nonce']),
            400, 'La demande de consentement a expiré.');
        $request->session()->forget('oauth_sgt_demande');
        $client = ClientOAuthSgt::findOrFail($demande['client_id']);
        abort_unless(in_array($demande['redirect_uri'], $client->redirect_uris, true)
            && OAuthSgt::redirectAutorisee($demande['redirect_uri']), 400);

        $parametres = $formulaire['decision'] === 'refuser'
            ? ['error' => 'access_denied']
            : ['code' => $oauth->code($client, $request->user(), $demande,
                $demande['ecriture_disponible'] && $request->boolean('modifier'))];
        if (isset($demande['state'])) {
            $parametres['state'] = $demande['state'];
        }

        return redirect()->away($demande['redirect_uri'].'?'.http_build_query($parametres, encoding_type: PHP_QUERY_RFC3986), 303)
            ->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer');
    }

    public function token(Request $request, OAuthSgt $oauth): JsonResponse
    {
        $type = $request->input('grant_type');
        if (! in_array($type, ['authorization_code', 'refresh_token'], true)) {
            return $this->json(['error' => 'unsupported_grant_type'], 400);
        }
        $validation = Validator::make($request->all(), [
            'client_id' => ['required', 'uuid'],
            'code' => [$type === 'authorization_code' ? 'required' : 'exclude', 'string', 'size:64'],
            'code_verifier' => [$type === 'authorization_code' ? 'required' : 'exclude', 'regex:/\A[A-Za-z0-9._~-]{43,128}\z/'],
            'redirect_uri' => [$type === 'authorization_code' ? 'required' : 'exclude', 'string', 'max:500'],
            'refresh_token' => [$type === 'refresh_token' ? 'required' : 'exclude', 'string', 'size:64'],
            'resource' => ['sometimes', Rule::in([config('oauth_sgt.resource')])],
        ]);
        if ($validation->fails()) {
            return $this->json(['error' => 'invalid_request'], 400);
        }
        $donnees = $validation->validated();
        $resultat = $type === 'authorization_code' ? $oauth->echanger($donnees) : $oauth->renouveler($donnees);

        return $resultat ? $this->json($resultat) : $this->json(['error' => 'invalid_grant'], 400);
    }

    public function revoke(Request $request, OAuthSgt $oauth): JsonResponse
    {
        $validation = Validator::make($request->all(), ['client_id' => ['required', 'uuid'], 'token' => ['required', 'string', 'max:512']]);
        if ($validation->fails()) {
            return $this->json(['error' => 'invalid_request'], 400);
        }
        $oauth->revoquerJeton($validation->validated()['client_id'], $validation->validated()['token']);

        return $this->json((object) []);
    }

    public function profil(Request $request, AutorisationOAuthSgt $autorisation, OAuthSgt $oauth)
    {
        $oauth->revoquerApplication($request->user(), $autorisation->id);

        return redirect()->route('profil.index')->with('success_infos', 'Application déconnectée.');
    }

    private function json(array|object $data, int $status = 200): JsonResponse
    {
        return response()->json($data, $status, ['Cache-Control' => 'no-store', 'Pragma' => 'no-cache']);
    }
}
