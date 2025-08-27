<?php

namespace Database\Factories;

use App\Models\Utilisateur;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UtilisateurFactory extends Factory
{
    protected $model = Utilisateur::class;

    public function definition(): array
    {
        return [
            'nom_complet'    => $this->faker->name(),
            'email'          => $this->faker->unique()->safeEmail(),
            'mot_de_passe'   => Hash::make('password'),
            'tel_mobile'     => $this->faker->optional()->e164PhoneNumber(),
            'tel_fixe'       => $this->faker->optional()->e164PhoneNumber(),
            'role'           => 'chauffeur',
            'actif'          => 1,
            'remember_token' => Str::random(10),
        ];
    }
}
