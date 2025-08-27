@extends('layouts.app')

@section('title','Utilisateurs')

@section('contenu')
<div class="max-w-6xl mx-auto">
  <div class="mb-4 flex items-center justify-between">
    <div>
      <h1 class="text-lg font-semibold">Utilisateurs</h1>
      <p class="text-sm text-gray-500">Gérez les comptes et leurs rôles.</p>
    </div>
    <a href="{{ route('admin.utilisateurs.create') }}"
       class="px-3 py-2 rounded-lg bg-gray-900 text-white text-sm hover:bg-black">
      + Ajouter
    </a>
  </div>

  @if(session('status'))
    <div class="mb-4 p-3 rounded border border-green-200 bg-green-50 text-green-700">
      {{ session('status') }}
    </div>
  @endif

  @if ($errors->any())
    <div class="mb-4 p-3 rounded border border-red-200 bg-red-50 text-red-700">
      <ul class="list-disc ml-5">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="GET" action="{{ route('admin.utilisateurs.index') }}" class="mb-3">
    <div class="flex items-center gap-2">
      <input type="text" name="q" value="{{ $q }}" placeholder="Rechercher (nom, email, rôle)"
             class="w-full md:w-96 rounded-lg border-gray-300">
      <button class="px-3 py-2 rounded-lg border bg-white hover:bg-gray-50">Filtrer</button>
    </div>
  </form>

  <div class="overflow-auto bg-white border rounded-lg">
    <table class="min-w-full text-sm">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-3 py-2 border-b text-left">Nom complet</th>
          <th class="px-3 py-2 border-b text-left">Email</th>
          <th class="px-3 py-2 border-b text-left">Rôle</th>
          <th class="px-3 py-2 border-b text-left">Actif</th>
          <th class="px-3 py-2 border-b text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($utilisateurs as $u)
          <tr class="odd:bg-white even:bg-gray-50">
            <td class="px-3 py-2 border-b">{{ $u->nom_complet }}</td>
            <td class="px-3 py-2 border-b">{{ $u->email }}</td>
            <td class="px-3 py-2 border-b uppercase text-xs text-gray-600">{{ $u->role }}</td>
            <td class="px-3 py-2 border-b">
              @if ($u->actif)
                <span class="text-xs px-2 py-0.5 rounded bg-green-50 text-green-700 border border-green-200">actif</span>
              @else
                <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-600 border">inactif</span>
              @endif
            </td>
            <td class="px-3 py-2 border-b text-right">
              <a href="{{ route('admin.utilisateurs.edit', $u) }}"
                 class="px-2 py-1.5 text-sm rounded border bg-white hover:bg-gray-50">Éditer</a>

              <form action="{{ route('admin.utilisateurs.destroy', $u) }}" method="POST" class="inline"
                    onsubmit="return confirm('Supprimer cet utilisateur ?');">
                @csrf @method('DELETE')
                <button class="px-2 py-1.5 text-sm rounded border border-red-200 bg-red-50 text-red-700 hover:bg-red-100">
                  Supprimer
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="px-3 py-6 text-center text-gray-500">Aucun utilisateur.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">
    {{ $utilisateurs->links() }}
  </div>
</div>
@endsection
