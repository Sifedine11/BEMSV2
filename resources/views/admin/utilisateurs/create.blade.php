@extends('layouts.app')

@section('title','Nouvel utilisateur')

@section('contenu')
<div class="max-w-xl">
  <h1 class="text-lg font-semibold mb-4">Créer un utilisateur</h1>

  @if ($errors->any())
    <div class="mb-4 p-3 rounded border border-red-200 bg-red-50 text-red-700">
      <ul class="list-disc ml-5">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('admin.utilisateurs.store') }}" class="bg-white border rounded-lg p-4 space-y-3">
    @csrf

    <div>
      <label class="text-sm font-medium text-gray-700">Nom complet</label>
      <input type="text" name="nom_complet" value="{{ old('nom_complet') }}" required class="mt-1 w-full rounded-lg border-gray-300">
    </div>

    <div>
      <label class="text-sm font-medium text-gray-700">Email</label>
      <input type="email" name="email" value="{{ old('email') }}" required class="mt-1 w-full rounded-lg border-gray-300">
    </div>

    <div>
      <label class="text-sm font-medium text-gray-700">Rôle</label>
      <select name="role" class="mt-1 w-full rounded-lg border-gray-300" required>
        <option value="" disabled selected>—</option>
        @foreach($roles as $r)
          <option value="{{ $r }}" {{ old('role')===$r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
        @endforeach
      </select>
    </div>

    <div>
      <label class="text-sm font-medium text-gray-700">Mot de passe <span class="text-gray-400">(optionnel)</span></label>
      <input type="text" name="mot_de_passe" value="{{ old('mot_de_passe') }}" class="mt-1 w-full rounded-lg border-gray-300">
      <p class="text-xs text-gray-500 mt-1">Optionnel (ton login actuel ne vérifie pas le mot de passe).</p>
    </div>

    <label class="inline-flex items-center gap-2 text-sm">
      <input type="checkbox" name="actif" value="1" {{ old('actif',1) ? 'checked' : '' }} class="rounded border-gray-300">
      <span>Actif</span>
    </label>

    <div class="pt-2 flex items-center gap-2">
      <button class="px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-black">Créer</button>
      <a href="{{ route('admin.utilisateurs.index') }}" class="px-4 py-2 rounded-lg border bg-white hover:bg-gray-50">Annuler</a>
    </div>
  </form>
</div>
@endsection
