<?php

namespace Database\Seeders;

use App\Models\Site;
use Illuminate\Database\Seeder;

class SiteSeeder extends Seeder
{
    public function run(): void
    {
        $sites = [
            ['nom' => 'Siège KayTechnologie', 'ville' => 'Libreville'],
            ['nom' => 'Agence Akanda',         'ville' => 'Akanda'],
            ['nom' => 'Antenne Owendo',         'ville' => 'Owendo'],
            ['nom' => 'Site Port-Gentil',       'ville' => 'Port-Gentil'],
            ['nom' => 'Antenne Franceville',    'ville' => 'Franceville'],
        ];

        foreach ($sites as $site) {
            Site::firstOrCreate(['nom' => $site['nom']], $site);
        }
    }
}
