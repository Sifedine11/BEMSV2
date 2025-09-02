@extends('layouts.app')

@section('title','Éditer un client')

@section('contenu')
@php
  use Illuminate\Support\Facades\Schema;
  $hasCP = Schema::hasTable('codes_postaux');
@endphp

  <div class="max-w-3xl">
    <h1 class="text-lg font-semibold mb-4">Éditer le client</h1>

    @if ($errors->any())
      <div class="mb-4 p-3 rounded border border-red-200 bg-red-50 text-red-700">
        <ul class="list-disc ml-5">
          @foreach ($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('admin.clients.update', $client) }}" class="space-y-5 bg-white border rounded-lg p-4">
      @csrf
      @method('PUT')

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-medium text-gray-700">Nom</label>
          <input type="text" name="nom" value="{{ old('nom', $client->nom) }}" required class="mt-1 w-full rounded-lg border-gray-300">
        </div>
        <div>
          <label class="text-sm font-medium text-gray-700">Prénom</label>
          <input type="text" name="prenom" value="{{ old('prenom', $client->prenom) }}" class="mt-1 w-full rounded-lg border-gray-300">
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-medium text-gray-700">Genre</label>
          <select name="genre" class="mt-1 w-full rounded-lg border-gray-300">
            <option value="">—</option>
            <option value="Madame"  @selected(old('genre', $client->genre)==='Madame')>Madame</option>
            <option value="Monsieur" @selected(old('genre', $client->genre)==='Monsieur')>Monsieur</option>
            <option value="Autre"    @selected(old('genre', $client->genre)==='Autre')>Autre</option>
          </select>
        </div>
        <div>
          <label class="text-sm font-medium text-gray-700">Date de naissance</label>
          <input type="date" name="date_naissance" value="{{ old('date_naissance', $client->date_naissance) }}" class="mt-1 w-full rounded-lg border-gray-300">
        </div>
      </div>

      <div>
        <label class="text-sm font-medium text-gray-700">Adresse</label>
        <input type="text" name="adresse" value="{{ old('adresse', $client->adresse) }}" class="mt-1 w-full rounded-lg border-gray-300">
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-medium text-gray-700">Localité</label>
          <input type="text" name="localite" value="{{ old('localite', $client->localite) }}" class="mt-1 w-full rounded-lg border-gray-300">
        </div>

        <div>
          <label class="text-sm font-medium text-gray-700">Code postal</label>
          @if($hasCP)
            <select name="code_postal_id" class="mt-1 w-full rounded-lg border-gray-300">
              <option value="">—</option>
              @foreach(($codes ?? collect()) as $cp)
                <option value="{{ $cp->id }}" @selected(old('code_postal_id', $client->code_postal_id)==$cp->id)>{{ $cp->code }}</option>
              @endforeach
            </select>
          @else
            <input type="text" name="code_postal_id" value="{{ old('code_postal_id', $client->code_postal_id) }}" class="mt-1 w-full rounded-lg border-gray-300" placeholder="ID CP (optionnel)">
            <p class="text-xs text-gray-500 mt-1">La table codes_postaux n’a pas été détectée.</p>
          @endif
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-medium text-gray-700">Mobile</label>
          <input type="text" name="tel_mobile" value="{{ old('tel_mobile', $client->tel_mobile) }}" class="mt-1 w-full rounded-lg border-gray-300">
        </div>
        <div>
          <label class="text-sm font-medium text-gray-700">Fixe</label>
          <input type="text" name="tel_fixe" value="{{ old('tel_fixe', $client->tel_fixe) }}" class="mt-1 w-full rounded-lg border-gray-300">
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-medium text-gray-700">Contact d’urgence (nom)</label>
          <input type="text" name="contact_urgence_nom" value="{{ old('contact_urgence_nom', $client->contact_urgence_nom) }}" class="mt-1 w-full rounded-lg border-gray-300">
        </div>
        <div>
          <label class="text-sm font-medium text-gray-700">Contact d’urgence (téléphone)</label>
          <input type="text" name="contact_urgence_tel" value="{{ old('contact_urgence_tel', $client->contact_urgence_tel) }}" class="mt-1 w-full rounded-lg border-gray-300">
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-medium text-gray-700">Moyens auxiliaires</label>
          <input type="text" name="moyens_auxiliaires" value="{{ old('moyens_auxiliaires', $client->moyens_auxiliaires) }}" class="mt-1 w-full rounded-lg border-gray-300">
        </div>
        <div>
          <label class="text-sm font-medium text-gray-700">Niveau d’aide</label>
          <input type="text" name="niveau_aide" value="{{ old('niveau_aide', $client->niveau_aide) }}" class="mt-1 w-full rounded-lg border-gray-300">
        </div>
      </div>

      <div>
        <label class="text-sm font-medium text-gray-700">Consignes chauffeur (bref)</label>
        <textarea name="consignes_chauffeur" rows="3" class="mt-1 w-full rounded-lg border-gray-300">{{ old('consignes_chauffeur', $client->consignes_chauffeur) }}</textarea>
      </div>

      <div>
        <label class="text-sm font-medium text-gray-700">Consignes détaillées</label>
        <textarea name="consignes_detail" rows="5" class="mt-1 w-full rounded-lg border-gray-300">{{ old('consignes_detail', $client->consignes_detail) }}</textarea>
      </div>

      @php
        $selectedPreferes = collect(old('chauffeurs_preferes', $client->chauffeursPreferes->pluck('id')->all()))->map(fn($v)=> (int)$v)->toArray();
        $selectedRefuses  = collect(old('chauffeurs_refuses',  $client->chauffeursRefuses->pluck('id')->all()))->map(fn($v)=> (int)$v)->toArray();
      @endphp

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-medium text-gray-700">Chauffeurs préférés</label>
          <select name="chauffeurs_preferes[]" multiple size="8" class="mt-1 w-full rounded-lg border-gray-300">
            @foreach(($chauffeurs ?? collect()) as $ch)
              <option value="{{ $ch->id }}" @selected(in_array($ch->id, $selectedPreferes))>{{ $ch->nom_complet }}</option>
            @endforeach
          </select>
          <p class="text-xs text-gray-500 mt-1">Maintiens Ctrl/Cmd pour multi-sélection.</p>
        </div>

        <div>
          <label class="text-sm font-medium text-gray-700">Chauffeurs refusés</label>
          <select name="chauffeurs_refuses[]" multiple size="8" class="mt-1 w-full rounded-lg border-gray-300">
            @foreach(($chauffeurs ?? collect()) as $ch)
              <option value="{{ $ch->id }}" @selected(in_array($ch->id, $selectedRefuses))>{{ $ch->nom_complet }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="flex items-center gap-6">
        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
          <input type="checkbox" name="actif" value="1" @checked(old('actif', (bool)$client->actif)) class="h-4 w-4 rounded border-gray-300">
          <span>Actif</span>
        </label>
        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
          <input type="checkbox" name="carte_legale_tmr" value="1" @checked(old('carte_legale_tmr', (bool)$client->carte_legale_tmr)) class="h-4 w-4 rounded border-gray-300">
          <span>Carte légale TMR</span>
        </label>
      </div>

      <div class="pt-2 flex items-center gap-2">
        <button class="px-4 py-2 rounded-lg bg-gray-900 text-white">Enregistrer</button>
        <a href="{{ route('admin.clients.index') }}" class="px-4 py-2 rounded-lg border bg-white">Annuler</a>
      </div>
    </form>
  </div>
@endsection
