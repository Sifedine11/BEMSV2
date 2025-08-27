<?php

namespace Database\Seeders;

use App\Models\Utilisateur;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UtilisateurSeeder extends Seeder
{
    public function run(): void
    {
        Utilisateur::updateOrCreate(
            ['email' => 'admin@bems.test'],
            [
                'nom_complet' => 'Admin BEMS',
                'mot_de_passe' => Hash::make('password'),
                'role' => 'admin',
                'actif' => 1,
            ]
        );
        Utilisateur::updateOrCreate(
            ['email' => 'telephoniste@bems.test'],
            [
                'nom_complet' => 'Téléphoniste Démo',
                'mot_de_passe' => Hash::make('password'),
                'role' => 'telephoniste',
                'actif' => 1,
            ]
        );
        Utilisateur::updateOrCreate(
            ['email' => 'coordinateur@bems.test'],
            [
                'nom_complet' => 'Coordinateur Démo',
                'mot_de_passe' => Hash::make('password'),
                'role' => 'coordinateur',
                'actif' => 1,
            ]
        );
        Utilisateur::updateOrCreate(
            ['email' => 'chauffeur@bems.test'],
            [
                'nom_complet' => 'Chauffeur Démo',
                'mot_de_passe' => Hash::make('password'),
                'role' => 'chauffeur',
                'actif' => 1,
            ]
        );
    }
}
