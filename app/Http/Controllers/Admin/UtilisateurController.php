<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UtilisateurController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string)$request->get('q', ''));

        $query = Utilisateur::query();

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nom_complet', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('role', 'like', "%{$q}%");
            });
        }

        $utilisateurs = $query
            ->orderBy('nom_complet')
            ->paginate(15)
            ->withQueryString();

        return view('admin.utilisateurs.index', compact('utilisateurs', 'q'));
    }

    public function create()
    {
        $roles = ['admin','telephoniste','coordinateur','chauffeur'];
        return view('admin.utilisateurs.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $roles = ['admin','telephoniste','coordinateur','chauffeur'];

        $request->validate([
            'nom_complet' => ['required','string','max:255'],
            'email'       => [
                'required','email','max:255',
                Rule::unique((new Utilisateur)->getTable(), 'email'),
            ],
            'role'        => ['required', Rule::in($roles)],
            'actif'       => ['nullable','boolean'],
            'mot_de_passe'=> ['nullable','string','min:4'], // optionnel (login actuel ne vérifie pas)
        ]);

        $u = new Utilisateur();
        $u->nom_complet   = $request->nom_complet;
        $u->email         = $request->email;
        $u->role          = $request->role;
        $u->actif         = $request->boolean('actif') ? 1 : 0;

        if ($request->filled('mot_de_passe')) {
            $u->mot_de_passe = Hash::make($request->mot_de_passe);
        }

        $u->save();

        return redirect()
            ->route('admin.utilisateurs.index')
            ->with('status', 'Utilisateur créé avec succès.');
    }

    public function edit(Utilisateur $utilisateur)
    {
        $roles = ['admin','telephoniste','coordinateur','chauffeur'];
        return view('admin.utilisateurs.edit', compact('utilisateur','roles'));
    }

    public function update(Request $request, Utilisateur $utilisateur)
    {
        $roles = ['admin','telephoniste','coordinateur','chauffeur'];

        $request->validate([
            'nom_complet' => ['required','string','max:255'],
            'email'       => [
                'required','email','max:255',
                Rule::unique((new Utilisateur)->getTable(), 'email')->ignore($utilisateur->id),
            ],
            'role'        => ['required', Rule::in($roles)],
            'actif'       => ['nullable','boolean'],
            'mot_de_passe'=> ['nullable','string','min:4'],
        ]);

        $utilisateur->nom_complet = $request->nom_complet;
        $utilisateur->email       = $request->email;
        $utilisateur->role        = $request->role;
        $utilisateur->actif       = $request->boolean('actif') ? 1 : 0;

        if ($request->filled('mot_de_passe')) {
            $utilisateur->mot_de_passe = Hash::make($request->mot_de_passe);
        }

        $utilisateur->save();

        return redirect()
            ->route('admin.utilisateurs.index')
            ->with('status', 'Utilisateur mis à jour.');
    }

    public function destroy(Utilisateur $utilisateur)
    {
        // Évite la suppression de soi-même pour ne pas se lock out
        if (auth()->id() === $utilisateur->id) {
            return back()->withErrors(['delete' => "Vous ne pouvez pas supprimer votre propre compte."]);
        }

        $utilisateur->delete();

        return redirect()
            ->route('admin.utilisateurs.index')
            ->with('status', 'Utilisateur supprimé.');
    }
}
