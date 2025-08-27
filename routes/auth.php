<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Utilisateur;

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => ['required', 'email'],
        'role'  => ['nullable', 'in:admin,telephoniste,coordinateur,chauffeur'],
    ]);

    $user = Utilisateur::where('email', $request->input('email'))->first();
    if (!$user) {
        return back()->withErrors(['email' => 'Utilisateur introuvable.'])->withInput();
    }

    $remember = (bool) $request->boolean('remember');
    Auth::login($user, $remember);

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
