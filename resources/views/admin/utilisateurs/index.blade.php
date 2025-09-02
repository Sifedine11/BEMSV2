@extends('layouts.app')

@section('title','Utilisateurs')

@section('contenu')
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-lg font-semibold">Utilisateurs</h1>
    @if (Route::has('admin.utilisateurs.create'))
      <a href="{{ route('admin.utilisateurs.create') }}"
         class="px-3 py-2 rounded-lg bg-gray-900 text-white text-sm">Nouvel utilisateur</a>
    @endif
  </div>

  @if (session('status'))
    <div class="mb-4 p-3 rounded border border-green-200 bg-green-50 text-green-700">
      {{ session('status') }}
    </div>
  @endif

  <form method="GET" action="{{ route('admin.utilisateurs.index') }}" class="mb-3">
    <div class="flex items-center gap-2">
      <input type="text"
             name="q"
             value="{{ isset($q) ? $q : request('q','') }}"
             placeholder="Rechercher (nom, email, rôle)"
             class="w-full md:w-96 rounded-lg border-gray-300">
      <button class="px-3 py-2 rounded-lg border bg-white hover:bg-gray-50">Filtrer</button>
    </div>
  </form>

  @php
    $vide = false;
    if ($utilisateurs instanceof \Illuminate\Pagination\LengthAwarePaginator || $utilisateurs instanceof \Illuminate\Contracts\Pagination\Paginator) {
        $vide = ($utilisateurs->total() ?? 0) === 0;
    } elseif ($utilisateurs instanceof \Illuminate\Support\Collection) {
        $vide = $utilisateurs->isEmpty();
    } else {
        $vide = empty($utilisateurs);
    }
  @endphp

  @if ($vide)
    <div class="p-6 bg-white border rounded-lg text-sm text-gray-600">
      Aucun utilisateur trouvé.
    </div>
  @else
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
          @foreach ($utilisateurs as $u)
            <tr class="odd:bg-white even:bg-gray-50">
              <td class="px-3 py-2 border-b">{{ $u->nom_complet }}</td>
              <td class="px-3 py-2 border-b">{{ $u->email }}</td>
              <td class="px-3 py-2 border-b uppercase text-xs tracking-wide">
                <span class="px-2 py-1 rounded-xl bg-gray-100">{{ $u->role }}</span>
              </td>
              <td class="px-3 py-2 border-b">
                @if($u->actif)
                  <span class="text-xs px-2 py-0.5 rounded-xl bg-green-50 text-green-700 border border-green-200">Oui</span>
                @else
                  <span class="text-xs px-2 py-0.5 rounded-xl bg-gray-100 text-gray-600 border">Non</span>
                @endif
              </td>
              <td class="px-3 py-2 border-b text-right">
                <div class="inline-flex gap-2">
                  @if (Route::has('admin.utilisateurs.edit'))
                    <a href="{{ route('admin.utilisateurs.edit', $u) }}"
                       class="px-2 py-1.5 text-sm rounded-xl border bg-white hover:bg-gray-50"><i class='bx bxs-edit'></i></a>
                  @endif

                  @if (Route::has('admin.utilisateurs.destroy'))
                    <form method="POST" action="{{ route('admin.utilisateurs.destroy', $u) }}"
                          onsubmit="return confirm('Supprimer cet utilisateur ?');">
                      @csrf
                      @method('DELETE')
                      <button class="px-2 py-1.5 text-sm rounded-xl border border-red-200 bg-red-50 text-red-700 hover:bg-red-100"><i class='bx bx-trash'></i></button>
                    </form>
                  @endif
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="mt-4">
      @if ($utilisateurs instanceof \Illuminate\Pagination\AbstractPaginator)
        {{ $utilisateurs->links() }}
      @endif
    </div>
  @endif
@endsection
