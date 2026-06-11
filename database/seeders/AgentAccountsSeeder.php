<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Crée les 7 comptes agents IA du système KAY TECHNOLOGIE.
 * Sécurité : type_compte = 'agent_ia' → bloqués de l'interface web par EnsureNotAgentAccount.
 * Accès uniquement via token Sanctum (généré séparément avec php artisan sgt:agent-token).
 */
class AgentAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $agents = [
            [
                'username'      => 'agent.dev',
                'nom'           => 'DevAgent KT',
                'prenom'        => '',
                'role'          => 'agent',
                'agent_code'    => 'dev-agent',
                'agent_couleur' => '#2563EB',
            ],
            [
                'username'      => 'agent.qa',
                'nom'           => 'QA Agent KT',
                'prenom'        => '',
                'role'          => 'agent',
                'agent_code'    => 'qa-agent',
                'agent_couleur' => '#15885A',
            ],
            [
                'username'      => 'agent.project',
                'nom'           => 'ProjectAgent KT',
                'prenom'        => '',
                'role'          => 'agent',
                'agent_code'    => 'project-agent',
                'agent_couleur' => '#7C3FBF',
            ],
            [
                'username'      => 'agent.design',
                'nom'           => 'DesignUI Agent KT',
                'prenom'        => '',
                'role'          => 'agent',
                'agent_code'    => 'design-ui-agent',
                'agent_couleur' => '#CC5500',
            ],
            [
                'username'      => 'agent.audit',
                'nom'           => 'Audit Agent KT',
                'prenom'        => '',
                'role'          => 'agent',
                'agent_code'    => 'audit-agent',
                'agent_couleur' => '#8C1622',
            ],
            [
                'username'      => 'agent.expert',
                'nom'           => 'Expert KT',
                'prenom'        => '',
                'role'          => 'agent',
                'agent_code'    => 'expert-kt',
                'agent_couleur' => '#C97A0A',
            ],
            [
                'username'      => 'agent.doyen',
                'nom'           => 'Le Doyen KT',
                'prenom'        => '',
                'role'          => 'agent',
                'agent_code'    => 'le-doyen-kt',
                'agent_couleur' => '#173A7A',
            ],
        ];

        foreach ($agents as $data) {
            User::updateOrCreate(
                ['username' => $data['username']],
                array_merge($data, [
                    'type_compte' => 'agent_ia',
                    'password'    => Hash::make(bin2hex(random_bytes(32))), // mot de passe inutilisable
                ])
            );
        }

        $this->command->info('✅ 7 comptes agents IA créés.');
    }
}
