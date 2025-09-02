@extends('layouts.app')

@section('title','Paramètres')

@section('contenu')
<div class="max-w-3xl">
  <h1 class="text-lg font-semibold mb-4">Paramètres</h1>

  @if (session('status'))
    <div class="mb-4 p-3 rounded border border-green-200 bg-green-50 text-green-700">
      {{ session('status') }}
    </div>
  @endif

  @if ($errors->any())
    <div class="mb-4 p-3 rounded border border-red-200 bg-red-50 text-red-700">
      <ul class="list-disc ml-5">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Section Profil (mot de passe) --}}
  <div class="bg-white border rounded-lg p-4 mb-6">
    <h2 class="text-base font-semibold mb-3"><i class='bx bx-user'></i></h2>
    <form method="POST" action="{{ route('parametres.update') }}" class="space-y-3">
      @csrf
      @method('PUT')
      <div>
        <label class="text-sm font-medium text-gray-700">Nouveau mot de passe</label>
        <input type="password" name="password" class="mt-1 w-full rounded-lg border-gray-300">
      </div>
      <div>
        <label class="text-sm font-medium text-gray-700">Confirmer le mot de passe</label>
        <input type="password" name="password_confirmation" class="mt-1 w-full rounded-lg border-gray-300">
      </div>
      <button class="px-4 py-2 rounded-lg bg-gray-900 text-white">Enregistrer</button>
    </form>
  </div>

  {{-- Section Réglages --}}
  <div class="bg-white border rounded-lg p-4">
    <h2 class="text-base font-semibold mb-3"><i class='bx bx-cog'></i></h2>
    <form method="POST" action="{{ route('parametres.update') }}" class="space-y-3">
      @csrf
      @method('PUT')

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-medium text-gray-700">Thème</label>
          @php $theme = $prefs['theme'] ?? 'clair'; @endphp
          <select name="prefs[theme]" class="mt-1 w-full rounded-lg border-gray-300">
            <option value="clair"  @selected($theme==='clair')>Clair</option>
            <option value="sombre" @selected($theme==='sombre')>Sombre</option>
          </select>
        </div>
        <div>
          <label class="text-sm font-medium text-gray-700">Taille du texte</label>
          @php $tx = $prefs['taille_texte'] ?? 'm'; @endphp
          <select name="prefs[taille_texte]" class="mt-1 w-full rounded-lg border-gray-300">
            <option value="s" @selected($tx==='s')>Petit</option>
            <option value="m" @selected($tx==='m')>Moyen</option>
            <option value="l" @selected($tx==='l')>Grand</option>
          </select>
        </div>
      </div>

      <div class="text-xs text-gray-500">
        Stockage des réglages :
        @if($persistable)
          <span class="text-green-700">en base (colonne <code>preferences</code>)</span>
        @else
          <span class="text-amber-700">en session (colonne manquante)</span>
        @endif
      </div>

      <button class="px-4 py-2 rounded-lg bg-gray-900 text-white">Enregistrer</button>
    </form>
  </div>
</div>
@endsection
