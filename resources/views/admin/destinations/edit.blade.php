@extends('layouts.app')

@section('title','Modifier une destination')

@section('contenu')
  <div class="max-w-2xl">
    <h1 class="text-lg font-semibold mb-4">Modifier la destination</h1>

    @if (session('status'))
      <div class="mb-4 rounded border border-green-200 bg-green-50 text-green-700 p-3 text-sm">
        {{ session('status') }}
      </div>
    @endif

    @if ($errors->any())
      <div class="mb-4 rounded border border-red-200 bg-red-50 text-red-700 p-3 text-sm">
        <ul class="list-disc ml-5 space-y-1">
          @foreach($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('admin.destinations.update', $destination) }}" class="bg-white border rounded-lg p-4 space-y-4">
      @csrf
      @method('PUT')

      <div>
        <label class="text-sm font-medium text-gray-700">Nom</label>
        <input type="text" name="nom" value="{{ old('nom', $destination->nom) }}" class="mt-1 w-full rounded-lg border-gray-300" required>
      </div>

      <div>
        <label class="text-sm font-medium text-gray-700">Adresse</label>
        <input type="text" name="adresse" value="{{ old('adresse', $destination->adresse) }}" class="mt-1 w-full rounded-lg border-gray-300" required>
      </div>

      <div>
        <label class="text-sm font-medium text-gray-700">Catégorie</label>
        <input type="text" name="categorie" value="{{ old('categorie', $destination->categorie ?? 'autre') }}" class="mt-1 w-full rounded-lg border-gray-300" placeholder="ex: hôpital, clinique, cabinet, domicile…">
        <p class="text-xs text-gray-500 mt-1">Si vide, la catégorie sera définie à <strong>autre</strong>.</p>
      </div>

      <div>
        <label class="text-sm font-medium text-gray-700">Code postal</label>
        <select name="code_postal_id" class="mt-1 w-full rounded-lg border-gray-300" required>
          <option value="" disabled>—</option>
          @foreach($codes as $cp)
            <option value="{{ $cp->id }}"
              {{ (string)old('code_postal_id', $destination->code_postal_id)===(string)$cp->id ? 'selected' : '' }}>
              {{ $cp->code }}
            </option>
          @endforeach
        </select>
        <p class="text-xs text-gray-500 mt-1">
          Le tarif (aller/retour) sera recalculé automatiquement selon le code postal choisi.
        </p>
      </div>

      <div class="flex items-center gap-2">
        <input type="checkbox" name="actif" value="1" id="actif" class="rounded border-gray-300" {{ old('actif', (int)$destination->actif) ? 'checked' : '' }}>
        <label for="actif" class="text-sm text-gray-700">Active</label>
      </div>

      <div class="flex items-center gap-2">
        <a href="{{ route('admin.destinations.index') }}" class="px-4 py-2 rounded-lg border">Annuler</a>
        <button class="px-4 py-2 rounded-lg bg-gray-900 text-white">Mettre à jour</button>
      </div>
    </form>
  </div>
@endsection
