@extends('layouts.app')

@section('title','Bénévoles')

@section('contenu')
<div class="max-w-6xl mx-auto">
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-lg font-semibold">Bénévoles</h1>
    <a href="{{ route('admin.benevoles.create') }}" class="px-3 py-1.5 rounded bg-gray-900 text-white text-sm">
      Nouveau
    </a>
  </div>

  @if (session('status'))
    <div class="mb-3 p-3 rounded border border-green-200 bg-green-50 text-green-700">
      {{ session('status') }}
    </div>
  @endif

  @php
    // Valeurs par défaut si le contrôleur n'envoie pas (sécurité)
    $q    = $q    ?? request('q', '');
    $sort = $sort ?? request('sort', 'nom');
    $dir  = strtolower($dir ?? request('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
    $toggleDir = $dir === 'asc' ? 'desc' : 'asc';
  @endphp

  <form method="GET" action="{{ route('admin.benevoles.index') }}" class="mb-3">
    <div class="flex items-center gap-2">
      <input type="text" name="q" value="{{ $q }}" placeholder="Rechercher (nom, prénom, email)"
             class="w-full md:w-96 rounded-lg border-gray-300">
      <input type="hidden" name="sort" value="{{ $sort }}">
      <input type="hidden" name="dir" value="{{ $dir }}">
      <button class="px-3 py-2 rounded-lg border bg-white hover:bg-gray-50">Filtrer</button>
    </div>
  </form>

  <div class="overflow-auto bg-white border rounded-lg">
    <table class="min-w-full text-sm">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-3 py-2 border-b text-left">
            <a class="underline"
               href="{{ route('admin.benevoles.index', ['q'=>$q,'sort'=>'nom', 'dir'=>$sort==='nom' ? $toggleDir : 'asc']) }}">
              Nom
            </a>
          </th>
          <th class="px-3 py-2 border-b text-left">
            <a class="underline"
               href="{{ route('admin.benevoles.index', ['q'=>$q,'sort'=>'prenom', 'dir'=>$sort==='prenom' ? $toggleDir : 'asc']) }}">
              Prénom
            </a>
          </th>
          <th class="px-3 py-2 border-b text-left">
            <a class="underline"
               href="{{ route('admin.benevoles.index', ['q'=>$q,'sort'=>'email', 'dir'=>$sort==='email' ? $toggleDir : 'asc']) }}">
              Email
            </a>
          </th>
          <th class="px-3 py-2 border-b text-left">Téléphone</th>
          <th class="px-3 py-2 border-b text-left">
            <a class="underline"
               href="{{ route('admin.benevoles.index', ['q'=>$q,'sort'=>'actif', 'dir'=>$sort==='actif' ? $toggleDir : 'asc']) }}">
              Actif
            </a>
          </th>
          <th class="px-3 py-2 border-b text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($benevoles as $b)
          <tr class="odd:bg-white even:bg-gray-50">
            <td class="px-3 py-2 border-b">{{ $b->nom ?? '—' }}</td>
            <td class="px-3 py-2 border-b">{{ $b->prenom ?? '—' }}</td>
            <td class="px-3 py-2 border-b">{{ $b->email ?? '—' }}</td>
            <td class="px-3 py-2 border-b">{{ $b->tel_mobile }}</td>
            <td class="px-3 py-2 border-b">
              <span class="text-xs px-2 py-0.5 rounded-xl {{ (int)($b->actif ?? 0) ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-gray-100 text-gray-600 border' }}">
                {{ (int)($b->actif ?? 0) ? 'Oui' : 'Non' }}
              </span>
            </td>
            <td class="px-3 py-2 border-b text-right">
              <div class="inline-flex gap-2">
                <a href="{{ route('admin.benevoles.edit', $b) }}" class="px-2 py-1.5 text-sm rounded-xl border bg-white hover:bg-gray-50"><i class='bx bxs-edit'></i></a>
                <form method="POST" action="{{ route('admin.benevoles.destroy', $b) }}" onsubmit="return confirm('Supprimer ce bénévole ?');">
                  @csrf
                  @method('DELETE')
                    <button class="px-2 py-1.5 text-sm rounded-xl border border-red-200 bg-red-50 text-red-700 hover:bg-red-100"><i class='bx bx-trash'></i></button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="px-3 py-4 text-center text-gray-500">Aucun bénévole.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">
    {{ $benevoles->links() }}
  </div>
</div>
@endsection
