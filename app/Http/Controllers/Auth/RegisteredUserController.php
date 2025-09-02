<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Affiche la vue d'inscription
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * Gère l'inscription.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom_complet' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:191', 'unique:utilisateurs,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = Utilisateur::create([
            'nom_complet' => $request->nom_complet,
            'email' => $request->email,
            'mot_de_passe' => Hash::make($request->password), // ✅
            'role' => 'chauffeur', // rôle par défaut, tu peux changer
            'actif' => 1,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
