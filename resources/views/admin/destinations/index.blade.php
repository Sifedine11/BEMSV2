@extends('layouts.app')

@section('title','Destinations')

@section('contenu')
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-lg font-semibold">Destinations</h1>
    <a href="{{ route('admin.destinations.create') }}" class="px-3 py-2 rounded bg-gray-900 text-white text-sm">
      Nouvelle destination
    </a>
  </div>

  @if (session('status'))
    <div class="mb-4 p-3 rounded border border-green-200 bg-green-50 text-green-700">
      {{ session('status') }}
    </div>
  @endif

  <form method="GET" action="{{ route('admin.destinations.index') }}" class="mb-3">
    <div class="flex items-center gap-2">
      <input type="text" name="q" value="{{ $q }}" placeholder="Rechercher (nom, adresse, catégorie)"
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
          <th class="px-3 py-2 border-b text-left">Nom</th>
          <th class="px-3 py-2 border-b text-left">Adresse</th>
          <th class="px-3 py-2 border-b text-left">Catégorie</th>
          <th class="px-3 py-2 border-b text-right">Prix (aller)</th>
          <th class="px-3 py-2 border-b text-right">Aller-retour</th>
          <th class="px-3 py-2 border-b text-left">Statut</th>
          <th class="px-3 py-2 border-b text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($destinations as $d)
          @php
            $aller = (float) ($d->prix_aller ?? 0);
            $ar    = $aller * 2;
          @endphp
          <tr class="odd:bg-white even:bg-gray-50">
            <td class="px-3 py-2 border-b align-top">{{ $d->nom }}</td>
            <td class="px-3 py-2 border-b align-top">{{ $d->adresse }}</td>
            <td class="px-3 py-2 border-b align-top">{{ $d->categorie ?? '—' }}</td>
            <td class="px-3 py-2 border-b align-top text-right">CHF {{ number_format($aller, 2, '.', ' ') }}</td>
            <td class="px-3 py-2 border-b align-top text-right">CHF {{ number_format($ar, 2, '.', ' ') }}</td>
            <td class="px-3 py-2 border-b align-top">
              <span class="text-xs px-2 py-0.5 rounded-xl
                {{ $d->actif ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-gray-100 text-gray-600 border' }}">
                {{ $d->actif ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="px-3 py-2 border-b align-top text-right">
              <div class="inline-flex items-center gap-2">
                <a href="{{ route('admin.destinations.edit', $d) }}" class="px-2 py-1.5 text-sm rounded-xl border bg-white hover:bg-gray-50"><i class='bx bxs-edit'></i></a>
                <form method="POST" action="{{ route('admin.destinations.destroy', $d) }}"
                      onsubmit="return confirm('Supprimer cette destination ?');" class="inline">
                  @csrf
                  @method('DELETE')
                <button class="px-2 py-1.5 text-sm rounded-xl border border-red-200 bg-red-50 text-red-700 hover:bg-red-100"><i class='bx bx-trash'></i></button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="px-3 py-4 text-center text-gray-500">Aucune destination.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">
    {{ $destinations->links() }}
  </div>
@endsection
