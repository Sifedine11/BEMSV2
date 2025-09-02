<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DestinationController extends Controller
{
    public function index(Request $request)
    {
        $q    = trim((string) $request->input('q', ''));
        $sort = $request->input('sort', 'nom');
        $dir  = $request->input('dir', 'asc');

        $sortable = ['nom', 'categorie', 'actif', 'prix_aller', 'created_at'];
        if (! in_array($sort, $sortable, true)) {
            $sort = 'nom';
        }
        $dir = strtolower($dir) === 'desc' ? 'desc' : 'asc';

        $query = Destination::query();

        if ($q !== '') {
            $query->where(function ($qq) use ($q) {
                $qq->where('nom', 'like', "%{$q}%")
                   ->orWhere('adresse', 'like', "%{$q}%")
                   ->orWhere('categorie', 'like', "%{$q}%");
            });
        }

        $destinations = $query->orderBy($sort, $dir)->paginate(20)->withQueryString();

        return view('admin.destinations.index', compact('destinations', 'q', 'sort', 'dir'));
    }

    public function create()
    {
        $codes = $this->codePostalOptions();
        return view('admin.destinations.create', compact('codes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        // Checkboxes & valeurs par défaut
        $data['actif']     = $request->boolean('actif');
        $data['categorie'] = $data['categorie'] ?? 'autre';

        // Récupérer le tarif depuis la table codes_postaux
        $cp = DB::table('codes_postaux')
            ->where('id', $data['code_postal_id'])
            ->select('tarif_forfaitaire')
            ->first();

        $tarif = (float) ($cp->tarif_forfaitaire ?? 0);

        // Aller/retour identiques ; l’index affiche l’AR = 2x aller
        $data['prix_aller']  = $tarif;
        $data['prix_retour'] = $tarif;

        Destination::create($data);

        return redirect()
            ->route('admin.destinations.index')
            ->with('status', 'Destination créée avec succès.');
    }

    public function show(Destination $destination)
    {
        return view('admin.destinations.show', compact('destination'));
    }

    public function edit(Destination $destination)
    {
        $codes = $this->codePostalOptions();

        // S’assurer que le CP courant est dans la liste
        if ($destination->code_postal_id) {
            $current = DB::table('codes_postaux')
                ->select(['id', 'code_postal as code'])
                ->where('id', $destination->code_postal_id)
                ->first();

            if ($current && !$codes->contains('id', $current->id)) {
                $codes->push($current);
                $codes = $codes->sortBy('code')->values();
            }
        }

        return view('admin.destinations.edit', compact('destination', 'codes'));
    }

    public function update(Request $request, Destination $destination)
    {
        $data = $request->validate($this->rules());

        $data['actif']     = $request->boolean('actif');
        $data['categorie'] = $data['categorie'] ?? 'autre';

        $cp = DB::table('codes_postaux')
            ->where('id', $data['code_postal_id'])
            ->select('tarif_forfaitaire')
            ->first();

        $tarif = (float) ($cp->tarif_forfaitaire ?? 0);

        $data['prix_aller']  = $tarif;
        $data['prix_retour'] = $tarif;

        $destination->update($data);

        return redirect()
            ->route('admin.destinations.index')
            ->with('status', 'Destination mise à jour.');
    }

    public function destroy(Destination $destination)
    {
        $destination->delete();

        return redirect()
            ->route('admin.destinations.index')
            ->with('status', 'Destination supprimée.');
    }

    private function rules(): array
    {
        return [
            'nom'            => ['required', 'string', 'max:255'],
            'adresse'        => ['required', 'string', 'max:255'],
            'categorie'      => ['nullable', 'string', 'max:100'],
            'code_postal_id' => ['required', 'integer', 'exists:codes_postaux,id'],
            'actif'          => ['nullable'], // checkbox
        ];
    }

    /**
     * Options de CP sans doublons :
     * on groupe par `code_postal` (aliasé en `code` pour les vues),
     * on prend un id arbitraire (MIN(id)) par code.
     */
    private function codePostalOptions()
    {
        return DB::table('codes_postaux')
            ->selectRaw('MIN(id) as id, code_postal as code')
            ->groupBy('code_postal')
            ->orderBy('code_postal')
            ->get();
    }
}
