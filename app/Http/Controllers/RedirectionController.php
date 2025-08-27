<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RedirectionController extends Controller
{
    /**
     * Après connexion / accès au tableau : redirige selon le rôle réel.
     * Ne JAMAIS utiliser `absolute:false` sur un redirect()->route().
     */
    public function apresLogin(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            // Non connecté -> page de login
            return redirect()->route('login');
        }

        // Rôle réel stocké en base (pas le "rôle visuel" de la sidebar)
        $role = $user->role ?? null;

        switch ($role) {
            case 'admin':
                // Section Admin (CRUD utilisateurs)
                return redirect()->route('admin.utilisateurs.index');

            case 'telephoniste':
                // Import Excel
                return redirect()->route('telephoniste.import.nouveau');

            case 'coordinateur':
                // Attribution des courses
                return redirect()->route('coordinateur.courses.a_attribuer');

            case 'chauffeur':
                // Disponibilités (ou planning si tu préfères)
                return redirect()->route('chauffeur.dispo.index');

            default:
                // Rôle inconnu : on affiche une vue "tableau" simple sans re-rediriger
                // (évite les boucles puisque /tableau pointe déjà ici).
                return view('tableau');
        }
    }
}
