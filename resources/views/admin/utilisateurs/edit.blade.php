@extends('layouts.app')

@section('title','Éditer utilisateur')

@section('contenu')
  <div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-lg font-semibold">Éditer l’utilisateur</h1>
      <a href="{{ route('admin.utilisateurs.index') }}" class="px-3 py-2 rounded bg-gray-100 text-sm">Retour</a>
    </div>

    @if ($errors->any())
      <div class="mb-4 p-3 rounded border border-red-200 bg-red-50 text-red-700">
        <ul class="list-disc ml-5">
          @foreach ($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('admin.utilisateurs.update', $utilisateur) }}" class="bg-white border rounded-lg p-6 space-y-4">
      @csrf
      @method('PUT')

      <div>
        <label class="text-sm font-medium text-gray-700">Nom complet</label>
        <input type="text" name="nom_complet" value="{{ old('nom_complet', $utilisateur->nom_complet) }}" required class="mt-1 w-full rounded-lg border-gray-300">
      </div>

      <div>
        <label class="text-sm font-medium text-gray-700">Email</label>
        <input type="email" name="email" value="{{ old('email', $utilisateur->email) }}" required class="mt-1 w-full rounded-lg border-gray-300">
      </div>

      <div>
        <label class="text-sm font-medium text-gray-700">Rôle</label>
        <select name="role" class="mt-1 w-full rounded-lg border-gray-300" required>
          @foreach($roles as $r)
            <option value="{{ $r }}" {{ old('role', $utilisateur->role)===$r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="text-sm font-medium text-gray-700">
          Nouveau mot de passe <span class="text-gray-400">(laisser vide pour ne pas changer)</span>
        </label>
        <input type="text" name="mot_de_passe" value="{{ old('mot_de_passe') }}" class="mt-1 w-full rounded-lg border-gray-300">
      </div>

      <div class="flex items-center gap-2">
        <input type="checkbox" id="actif" name="actif" value="1" {{ old('actif', (bool)$utilisateur->actif) ? 'checked' : '' }} class="rounded border-gray-300">
        <label for="actif" class="text-sm text-gray-700">Actif</label>
      </div>

      <div class="flex items-center gap-2">
        <button class="px-4 py-2 rounded-lg bg-gray-900 text-white">Enregistrer</button>
        <a href="{{ route('admin.utilisateurs.index') }}" class="px-4 py-2 rounded-lg bg-gray-100">Annuler</a>
      </div>
    </form>
  </div>
@endsection
