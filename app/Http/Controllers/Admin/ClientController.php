<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $q    = trim((string) $request->input('q', ''));
        $sort = $request->input('sort', 'nom');
        $dir  = strtolower($request->input('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $columns = Schema::getColumnListing('clients');

        // Tri : uniquement des colonnes qui existent dans ta table 'clients'
        $candidateSortable = [
            'nom','prenom','localite','tel_mobile','tel_fixe','actif','created_at',
        ];
        $sortable = array_values(array_intersect($candidateSortable, $columns));
        if (empty($sortable)) {
            $sortable = [$columns[0] ?? 'id'];
        }
        if (! in_array($sort, $sortable, true)) {
            $sort = $sortable[0];
        }

        $query = Client::query();

        // Recherche plein-texte sur les colonnes qui existent réellement
        if ($q !== '') {
            $query->where(function ($qq) use ($q, $columns) {
                if (in_array('nom', $columns, true))                 $qq->orWhere('nom', 'like', "%{$q}%");
                if (in_array('prenom', $columns, true))              $qq->orWhere('prenom', 'like', "%{$q}%");
                if (in_array('localite', $columns, true))            $qq->orWhere('localite', 'like', "%{$q}%");
                if (in_array('adresse', $columns, true))             $qq->orWhere('adresse', 'like', "%{$q}%");
                if (in_array('tel_mobile', $columns, true))          $qq->orWhere('tel_mobile', 'like', "%{$q}%");
                if (in_array('tel_fixe', $columns, true))            $qq->orWhere('tel_fixe', 'like', "%{$q}%");
                if (in_array('consignes_chauffeur', $columns, true)) $qq->orWhere('consignes_chauffeur', 'like', "%{$q}%");
                if (in_array('consignes_detail', $columns, true))    $qq->orWhere('consignes_detail', 'like', "%{$q}%");
            });
        }

        $clients = $query->orderBy($sort, $dir)->paginate(20)->withQueryString();

        return view('admin.clients.index', compact('clients', 'q', 'sort', 'dir'));
    }

    public function create()
    {
        $codes = $this->codePostalOptions();

        // Chauffeurs actifs (robuste : nom_complet ou name)
        $chauffeursQuery = Utilisateur::query()
            ->when(Schema::hasColumn('utilisateurs', 'role'), fn($q) => $q->where('role', 'chauffeur'))
            ->when(Schema::hasColumn('utilisateurs', 'actif'), fn($q) => $q->where('actif', 1));

        if (Schema::hasColumn('utilisateurs','nom_complet')) {
            $chauffeursQuery->orderBy('nom_complet');
            $select = ['id','nom_complet'];
        } elseif (Schema::hasColumn('utilisateurs','name')) {
            $chauffeursQuery->orderBy('name');
            // alias pour que la vue puisse toujours afficher ->nom_complet
            $select = ['id', DB::raw('name as nom_complet')];
        } else {
            $chauffeursQuery->orderBy('id');
            $select = ['id', DB::raw("CONCAT('Utilisateur #', id) as nom_complet")];
        }

        $chauffeurs = $chauffeursQuery->get($select);

        return view('admin.clients.create', compact('codes','chauffeurs'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);

        $payload = $this->onlyExistingColumns('clients', $validated);
        $payload['actif'] = $request->boolean('actif');

        $client = Client::create($payload);

        // Sync pivots si les tables existent
        $idsPreferes = collect($request->input('chauffeurs_preferes', []))->filter()->values()->all();
        $idsRefuses  = collect($request->input('chauffeurs_refuses', []))->filter()->values()->all();

        if (Schema::hasTable('clients_chauffeurs_preferes')) {
            $client->chauffeursPreferes()->sync($idsPreferes);
        }
        if (Schema::hasTable('clients_chauffeurs_refuses')) {
            $client->chauffeursRefuses()->sync($idsRefuses);
        }

        return redirect()
            ->route('admin.clients.index')
            ->with('status', 'Client créé avec succès.');
    }

    public function show(Client $client)
    {
        $client->load(['chauffeursPreferes:id,nom_complet', 'chauffeursRefuses:id,nom_complet']);
        return view('admin.clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        $codes = $this->codePostalOptions();

        // S'assurer que le code postal courant apparaisse dans la liste si manquant
        if ($client->code_postal_id && $codes->where('id', $client->code_postal_id)->count() === 0) {
            $current = DB::table('codes_postaux')
                ->select(['id', 'code_postal as code'])
                ->where('id', $client->code_postal_id)
                ->first();

            if ($current) {
                $codes->push($current);
                $codes = $codes->sortBy('code')->values();
            }
        }

        // Chauffeurs actifs (robuste)
        $chauffeursQuery = Utilisateur::query()
            ->when(Schema::hasColumn('utilisateurs', 'role'), fn($q) => $q->where('role', 'chauffeur'))
            ->when(Schema::hasColumn('utilisateurs', 'actif'), fn($q) => $q->where('actif', 1));

        if (Schema::hasColumn('utilisateurs','nom_complet')) {
            $chauffeursQuery->orderBy('nom_complet');
            $select = ['id','nom_complet'];
        } elseif (Schema::hasColumn('utilisateurs','name')) {
            $chauffeursQuery->orderBy('name');
            $select = ['id', DB::raw('name as nom_complet')];
        } else {
            $chauffeursQuery->orderBy('id');
            $select = ['id', DB::raw("CONCAT('Utilisateur #', id) as nom_complet")];
        }

        $chauffeurs = $chauffeursQuery->get($select);

        // Charger les pivots pour précocher
        $client->load(['chauffeursPreferes:id', 'chauffeursRefuses:id']);

        return view('admin.clients.edit', compact('client','codes','chauffeurs'));
    }

    public function update(Request $request, Client $client)
    {
        $validated = $this->validatePayload($request);

        $payload = $this->onlyExistingColumns('clients', $validated);
        $payload['actif'] = $request->boolean('actif');

        $client->update($payload);

        $idsPreferes = collect($request->input('chauffeurs_preferes', []))->filter()->values()->all();
        $idsRefuses  = collect($request->input('chauffeurs_refuses', []))->filter()->values()->all();

        if (Schema::hasTable('clients_chauffeurs_preferes')) {
            $client->chauffeursPreferes()->sync($idsPreferes);
        }
        if (Schema::hasTable('clients_chauffeurs_refuses')) {
            $client->chauffeursRefuses()->sync($idsRefuses);
        }

        return redirect()
            ->route('admin.clients.index')
            ->with('status', 'Client mis à jour.');
    }

    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()
            ->route('admin.clients.index')
            ->with('status', 'Client supprimé.');
    }

    private function validatePayload(Request $request): array
    {
        // Règles alignées sur ta table 'clients'
        return $request->validate([
            'nom'                  => ['required','string','max:100'],
            'prenom'               => ['nullable','string','max:100'],
            'genre'                => ['nullable','in:Madame,Monsieur,Autre'],
            'adresse'              => ['nullable','string','max:255'],
            'code_postal_id'       => ['nullable','integer'],
            'localite'             => ['nullable','string','max:100'],
            'tel_mobile'           => ['nullable','string','max:30'],
            'tel_fixe'             => ['nullable','string','max:30'],
            'contact_urgence_nom'  => ['nullable','string','max:150'],
            'contact_urgence_tel'  => ['nullable','string','max:30'],
            'moyens_auxiliaires'   => ['nullable','string','max:150'],
            'niveau_aide'          => ['nullable','string','max:100'],
            'consignes_chauffeur'  => ['nullable','string','max:255'],
            'consignes_detail'     => ['nullable','string'],
            'date_naissance'       => ['nullable','date'],
            'actif'                => ['nullable'],
            'carte_legale_tmr'     => ['nullable'],

            // Multi-sélections chauffeurs
            'chauffeurs_preferes'   => ['nullable','array'],
            'chauffeurs_preferes.*' => ['integer','exists:utilisateurs,id'],
            'chauffeurs_refuses'    => ['nullable','array'],
            'chauffeurs_refuses.*'  => ['integer','exists:utilisateurs,id'],
        ]);
    }

    private function onlyExistingColumns(string $table, array $data): array
    {
        $columns = Schema::getColumnListing($table);
        return array_intersect_key($data, array_flip($columns));
    }

    private function codePostalOptions()
    {
        if (! Schema::hasTable('codes_postaux')) {
            return collect();
        }

        return DB::table('codes_postaux')
            ->selectRaw('MIN(id) as id, code_postal as code')
            ->groupBy('code_postal')
            ->orderBy('code_postal')
            ->get();
    }
}
