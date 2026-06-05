<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SiteFactory extends Factory
{
    public function definition(): array
    {
        $villes = ['Libreville', 'Akanda', 'Owendo', 'Port-Gentil', 'Franceville'];

        return [
            'nom'   => $this->faker->company() . ' — ' . $this->faker->buildingNumber(),
            'ville' => $this->faker->randomElement($villes),
            'actif' => true,
        ];
    }
}
