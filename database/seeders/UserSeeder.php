<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['username' => 'admin',      'nom' => 'DINGOKA', 'prenom' => 'Hermin',   'role' => 'manager',     'password' => 'admin123'],
            ['username' => 'technicien1','nom' => 'OBAME',   'prenom' => 'Claude',   'role' => 'technicien',  'password' => 'tech123'],
            ['username' => 'agent1',     'nom' => 'NKOGHE',  'prenom' => 'Patrick',  'role' => 'agent',       'password' => 'agent123'],
            ['username' => 'dev1',       'nom' => 'MBOUMBA', 'prenom' => 'Kevin',    'role' => 'developpeur', 'password' => 'dev123'],
            ['username' => 'stagiaire1', 'nom' => 'IDIATA',  'prenom' => 'Marie',    'role' => 'stagiaire',   'password' => 'stage123'],
        ];

        foreach ($users as $data) {
            User::firstOrCreate(
                ['username' => $data['username']],
                array_merge($data, ['password' => Hash::make($data['password'])])
            );
        }
    }
}
