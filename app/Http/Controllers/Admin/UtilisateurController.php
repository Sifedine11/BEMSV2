<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UtilisateurController extends Controller
{

    private function roles(): array
    {
        return ['admin', 'telephoniste', 'coordinateur', 'chauffeur'];
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $utilisateurs = Utilisateur::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('nom_complet', 'like', "%{$q}%")
                       ->orWhere('email', 'like', "%{$q}%")
                       ->orWhere('role', 'like', "%{$q}%");
                });
            })
            ->orderBy('nom_complet')
            ->paginate(20)
            ->withQueryString();

        return view('admin.utilisateurs.index', [
            'utilisateurs' => $utilisateurs,
            'q'            => $q,
        ]);
    }

    public function create()
    {
        $roles = $this->roles();
        return view('admin.utilisateurs.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom_complet'  => ['required','string','max:255'],
            'email'        => ['required','email','max:255','unique:utilisateurs,email'],
            'role'         => ['required', Rule::in($this->roles())],
            'mot_de_passe' => ['nullable','string','min:8'],
            'actif'        => ['nullable','boolean'],
        ]);

        $plain = $data['mot_de_passe'] ?? 'password';

        $utilisateur = new Utilisateur();
        $utilisateur->nom_complet  = $data['nom_complet'];
        $utilisateur->email        = $data['email'];
        $utilisateur->role         = $data['role'];
        $utilisateur->actif        = (int) ($data['actif'] ?? 1);
        $utilisateur->mot_de_passe = Hash::make($plain);
        $utilisateur->save();

        return redirect()
            ->route('admin.utilisateurs.index')
            ->with('status', 'Utilisateur créé avec succès.');
    }

    public function edit(Utilisateur $utilisateur)
    {
        $roles = $this->roles();
        return view('admin.utilisateurs.edit', compact('utilisateur', 'roles'));
    }

    public function update(Request $request, Utilisateur $utilisateur)
    {
        $data = $request->validate([
            'nom_complet'  => ['required','string','max:255'],
            'email'        => ['required','email','max:255', Rule::unique('utilisateurs','email')->ignore($utilisateur->id)],
            'role'         => ['required', Rule::in($this->roles())],
            'mot_de_passe' => ['nullable','string','min:8'],
            'actif'        => ['nullable','boolean'],
        ]);

        $utilisateur->nom_complet = $data['nom_complet'];
        $utilisateur->email       = $data['email'];
        $utilisateur->role        = $data['role'];
        $utilisateur->actif       = (int) ($data['actif'] ?? $utilisateur->actif);

        if (!empty($data['mot_de_passe'])) {
            $utilisateur->mot_de_passe = Hash::make($data['mot_de_passe']);
        }

        $utilisateur->save();

        return redirect()
            ->route('admin.utilisateurs.index')
            ->with('status', 'Utilisateur mis à jour.');
    }

    public function destroy(Utilisateur $utilisateur)
    {
        $utilisateur->delete();

        return redirect()
            ->route('admin.utilisateurs.index')
            ->with('status', 'Utilisateur supprimé.');
    }
}
