<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Utilisateur;

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $request->validate([
        'email'    => ['required', 'email'],
        'password' => ['required', 'string'],
        'role'     => ['nullable', 'in:admin,telephoniste,coordinateur,chauffeur'],
        'remember' => ['nullable'],
    ]);

    $user = Utilisateur::where('email', $request->input('email'))->first();

    if (!$user) {
        return back()->withErrors(['email' => 'Utilisateur introuvable.'])->withInput();
    }

    // Si le champ mot_de_passe est vide/nul en DB, on initialise avec la règle: 4 chars email + ".bems"
    if (empty($user->mot_de_passe)) {
        $plain = substr($user->email, 0, 4) . '.bems';
        $user->mot_de_passe = Hash::make($plain);
        $user->save();
    }

    // Vérification du mot de passe saisi
    if (!Hash::check($request->input('password'), $user->mot_de_passe)) {
        return back()->withErrors(['password' => 'Mot de passe invalide.'])->withInput();
    }

    $remember = (bool) $request->boolean('remember');
    Auth::login($user, $remember);

    // Mémorise le rôle visuel si fourni (facultatif)
    if ($request->filled('role')) {
        $request->session()->put('role_visuel', $request->input('role'));
    }

    $request->session()->regenerate();

    return redirect()->intended(route('tableau'));
})->name('login.store');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->forget('role_visuel');
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('accueil');
})->name('logout');
