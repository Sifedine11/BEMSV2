@extends('layouts.app')

@section('title','Nouvelle destination')

@section('contenu')
  <div class="max-w-2xl">
    <h1 class="text-lg font-semibold mb-4">Nouvelle destination</h1>

    @if ($errors->any())
      <div class="mb-4 rounded border border-red-200 bg-red-50 text-red-700 p-3 text-sm">
        <ul class="list-disc ml-5 space-y-1">
          @foreach($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('admin.destinations.store') }}" class="bg-white border rounded-lg p-4 space-y-4">
      @csrf

      <div>
        <label class="text-sm font-medium text-gray-700">Nom</label>
        <input type="text" name="nom" value="{{ old('nom') }}" class="mt-1 w-full rounded-lg border-gray-300" required>
      </div>

      <div>
        <label class="text-sm font-medium text-gray-700">Adresse</label>
        <input type="text" name="adresse" value="{{ old('adresse') }}" class="mt-1 w-full rounded-lg border-gray-300" required>
      </div>

      <div>
        <label class="text-sm font-medium text-gray-700">Catégorie</label>
        <input type="text" name="categorie" value="{{ old('categorie','autre') }}" class="mt-1 w-full rounded-lg border-gray-300" placeholder="ex: hôpital, clinique, cabinet, domicile…">
        <p class="text-xs text-gray-500 mt-1">Si vide, la catégorie sera définie à <strong>autre</strong>.</p>
      </div>

      <div>
        <label class="text-sm font-medium text-gray-700">Code postal</label>
        <select name="code_postal_id" class="mt-1 w-full rounded-lg border-gray-300" required>
          <option value="" disabled selected>—</option>
          @foreach($codes as $cp)
            <option value="{{ $cp->id }}" {{ (string)old('code_postal_id')===(string)$cp->id ? 'selected' : '' }}>
              {{ $cp->code }}
            </option>
          @endforeach
        </select>
        <p class="text-xs text-gray-500 mt-1">
          Le tarif sera automatiquement récupéré depuis le code postal choisi (pas de saisie de prix).
        </p>
      </div>

      <div class="flex items-center gap-2">
        <input type="checkbox" name="actif" value="1" id="actif" class="rounded border-gray-300" {{ old('actif', true) ? 'checked' : '' }}>
        <label for="actif" class="text-sm text-gray-700">Active</label>
      </div>

      <div class="flex items-center gap-2">
        <a href="{{ route('admin.destinations.index') }}" class="px-4 py-2 rounded-lg border">Annuler</a>
        <button class="px-4 py-2 rounded-lg bg-gray-900 text-white">Enregistrer</button>
      </div>
    </form>
  </div>
@endsection
