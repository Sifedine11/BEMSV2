<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Benevole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class BenevoleController extends Controller
{
    public function index(Request $request)
    {
        $q    = (string) $request->query('q', '');
        $sort = $request->query('sort', 'nom');
        $dir  = strtolower($request->query('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        // Colonnes autorisées pour le tri (évite les 500 si on envoie un sort inconnu)
        $sortable = ['nom', 'prenom', 'email', 'actif'];
        if (!in_array($sort, $sortable, true)) {
            $sort = 'nom';
        }

        $query = Benevole::query();

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nom', 'like', "%{$q}%")
                    ->orWhere('prenom', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");

                // Si la colonne "telephone" n'existe pas en DB, on n'essaie pas de la filtrer
                if (Schema::hasColumn('benevoles', 'tel_mobile')) {
                    $sub->orWhere('tel_mobile', 'like', "%{$q}%");
                }
            });
        }

        $query->orderBy($sort, $dir);

        $benevoles = $query->paginate(20)->withQueryString();

        return view('admin.benevoles.index', compact('benevoles', 'q', 'sort', 'dir'));
    }

    public function create()
    {
        return view('admin.benevoles.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom'       => ['nullable', 'string', 'max:255'],
            'prenom'    => ['nullable', 'string', 'max:255'],
            'email'     => ['nullable', 'email', 'max:255', 'unique:benevoles,email'],
            'tel_mobile' => ['nullable', 'string', 'max:50'],
            'actif'     => ['nullable', 'boolean'],
        ]);

        $data['actif'] = isset($data['actif']) ? (int) $data['actif'] : 1;

        // Si la colonne n'existe pas, on retire la clé pour éviter l'INSERT qui plante
        if (!Schema::hasColumn('benevoles', 'tel_mobile')) {
            unset($data['tel_mobile']);
        }

        Benevole::create($data);

        return redirect()->route('admin.benevoles.index')->with('status', 'Bénévole créé.');
    }

    public function edit(Benevole $benevole)
    {
        return view('admin.benevoles.edit', compact('benevole'));
    }

    public function update(Request $request, Benevole $benevole)
    {
        $data = $request->validate([
            'nom'       => ['nullable', 'string', 'max:255'],
            'prenom'    => ['nullable', 'string', 'max:255'],
            'email'     => ['nullable', 'email', 'max:255', 'unique:benevoles,email,' . $benevole->id],
            'tel_mobile' => ['nullable', 'string', 'max:50'],
            'actif'     => ['nullable', 'boolean'],
        ]);

        $data['actif'] = isset($data['actif']) ? (int) $data['actif'] : 1;

        if (!Schema::hasColumn('benevoles', 'tel_mobile')) {
            unset($data['tel_mobile']);
        }

        $benevole->update($data);

        return redirect()->route('admin.benevoles.index')->with('status', 'Bénévole mis à jour.');
    }

    public function destroy(Benevole $benevole)
    {
        $benevole->delete();

        return redirect()->route('admin.benevoles.index')->with('status', 'Bénévole supprimé.');
    }

    public function show(Benevole $benevole)
    {
        return view('admin.benevoles.show', compact('benevole'));
    }
}
