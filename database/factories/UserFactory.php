<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'username'     => fake()->unique()->userName(),
            'nom'          => fake()->lastName(),
            'prenom'       => fake()->firstName(),
            'role'         => 'agent',
            'direction_ui' => 'A',
            'password'     => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function manager(): static
    {
        return $this->state(fn() => ['role' => 'manager']);
    }

    public function technicien(): static
    {
        return $this->state(fn() => ['role' => 'technicien']);
    }

    public function developpeur(): static
    {
        return $this->state(fn() => ['role' => 'developpeur']);
    }

    public function stagiaire(): static
    {
        return $this->state(fn() => ['role' => 'stagiaire']);
    }
}
