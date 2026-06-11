<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SgtAgentToken extends Command
{
    protected $signature   = 'sgt:agent-token
                                {--agent= : Code de l\'agent (ex: dev-agent, qa-agent)}
                                {--expires=90 : Durée de validité en jours (défaut: 90)}
                                {--list : Lister les tokens actifs des agents}
                                {--revoke= : Révoquer un token par son ID}';

    protected $description = 'Générer, lister ou révoquer les tokens Sanctum des agents IA';

    /** Scopes accordés par agent_code */
    private array $scopesParAgent = [
        'dev-agent'      => ['taches:create', 'taches:update', 'taches:read', 'rapports:create', 'sessions:manage'],
        'qa-agent'       => ['taches:update', 'taches:read', 'rapports:create', 'sessions:manage'],
        'project-agent'  => ['taches:create', 'taches:update', 'taches:read', 'rapports:create', 'sessions:manage'],
        'design-ui-agent'=> ['taches:create', 'taches:update', 'taches:read', 'rapports:create', 'sessions:manage'],
        'audit-agent'    => ['taches:read', 'rapports:create', 'sessions:manage'],
        'expert-kt'      => ['taches:create', 'taches:update', 'taches:read', 'rapports:create', 'sessions:manage'],
        'le-doyen-kt'    => ['taches:create', 'taches:update', 'taches:read', 'rapports:create', 'sessions:manage'],
    ];

    public function handle(): int
    {
        if ($this->option('list')) {
            return $this->listerTokens();
        }

        if ($revokeId = $this->option('revoke')) {
            return $this->revoquerToken((int) $revokeId);
        }

        return $this->genererToken();
    }

    private function genererToken(): int
    {
        $agentCode = $this->option('agent');
        $expires   = (int) $this->option('expires');

        if (! $agentCode) {
            $this->error('--agent requis. Exemple : php artisan sgt:agent-token --agent=dev-agent');
            return 1;
        }

        $agent = User::where('agent_code', $agentCode)
            ->where('type_compte', 'agent_ia')
            ->first();

        if (! $agent) {
            $this->error("Agent introuvable : {$agentCode}");
            $this->line('Agents disponibles : ' . implode(', ', array_keys($this->scopesParAgent)));
            return 1;
        }

        $scopes     = $this->scopesParAgent[$agentCode] ?? ['taches:read'];
        $expiresAt  = Carbon::now()->addDays($expires);
        $tokenName  = "sgt_{$agentCode}_" . now()->format('Ymd');

        $token = $agent->createToken($tokenName, $scopes, $expiresAt);

        $this->newLine();
        $this->line('<fg=green;options=bold>✅ Token généré — COPIER MAINTENANT (affiché une seule fois)</>');
        $this->newLine();
        $this->line("  Agent    : <fg=cyan>{$agent->nom_complet}</> ({$agentCode})");
        $this->line("  Scopes   : <fg=yellow>" . implode(', ', $scopes) . "</>");
        $this->line("  Expire   : <fg=yellow>{$expiresAt->format('d/m/Y')}</> ({$expires} jours)");
        $this->newLine();
        $this->line("  TOKEN    : <fg=white;options=bold>{$token->plainTextToken}</>");
        $this->newLine();
        $this->line('  → Stocker dans .env local : SGT_TOKEN_' . strtoupper(str_replace(['-', '.'], '_', $agentCode)) . '=<token>');
        $this->line('  → Ou dans CLAUDE.md (section tokens agents, jamais commité)');
        $this->newLine();
        $this->warn('⚠ Ce token ne sera plus jamais affiché. Copiez-le maintenant.');

        return 0;
    }

    private function listerTokens(): int
    {
        $agents = User::where('type_compte', 'agent_ia')->with('tokens')->get();

        if ($agents->isEmpty()) {
            $this->warn('Aucun agent IA trouvé.');
            return 0;
        }

        foreach ($agents as $agent) {
            $tokens = $agent->tokens->filter(fn($t) => ! $t->expires_at || $t->expires_at->isFuture());
            $this->line("\n<fg=cyan>{$agent->nom_complet}</> ({$agent->agent_code})");

            if ($tokens->isEmpty()) {
                $this->line('  <fg=gray>— Aucun token actif</>');
                continue;
            }

            foreach ($tokens as $t) {
                $expire = $t->expires_at ? $t->expires_at->format('d/m/Y') : '∞';
                $this->line("  ID #{$t->id} | {$t->name} | expire: {$expire} | scopes: " . implode(', ', $t->abilities));
            }
        }

        $this->newLine();
        return 0;
    }

    private function revoquerToken(int $tokenId): int
    {
        $deleted = \Laravel\Sanctum\PersonalAccessToken::destroy($tokenId);

        if ($deleted) {
            $this->info("✅ Token #{$tokenId} révoqué.");
        } else {
            $this->error("Token #{$tokenId} introuvable.");
            return 1;
        }

        return 0;
    }
}
