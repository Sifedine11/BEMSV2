<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RedirectionController extends Controller
{

    public function apresLogin(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $role = $user->role ?? null;

        switch ($role) {
            case 'admin':
                return redirect()->route('admin.utilisateurs.index');

            case 'telephoniste':
                return redirect()->route('telephoniste.import.nouveau');

            case 'coordinateur':
                return redirect()->route('coordinateur.courses.a_attribuer');

            case 'chauffeur':
                return redirect()->route('chauffeur.dispo.index');

            default:

                return view('tableau');
        }
    }
}
