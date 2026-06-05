<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TacheFactory extends Factory
{
    public function definition(): array
    {
        return [
            'titre'         => $this->faker->sentence(4),
            'description'   => $this->faker->paragraph(),
            'createur_id'   => User::factory(),
            'site_id'       => null,
            'date_debut'    => now()->format('Y-m-d'),
            'date_echeance' => now()->addDays(rand(3, 14))->format('Y-m-d'),
            'statut'        => 'nouveau',
            'progression'   => 0,
            'priorite'      => 'normale',
            'archived_at'   => null,
        ];
    }

    public function enRetard(): static
    {
        return $this->state(fn() => [
            'date_echeance' => now()->subDay()->format('Y-m-d'),
            'statut'        => 'en_cours',
        ]);
    }

    public function termine(): static
    {
        return $this->state(fn() => [
            'statut'      => 'termine',
            'progression' => 100,
            'archived_at' => now(),
        ]);
    }
}
