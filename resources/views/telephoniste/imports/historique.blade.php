@extends('layouts.app')

@section('title','Historique des imports')

@section('contenu')
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-lg font-semibold">Historique des imports</h1>
    <a href="{{ route('telephoniste.import.nouveau') }}"
       class="px-3 py-2 rounded-lg bg-gray-900 text-white text-sm">Nouvel import</a>
  </div>

  @if (session('status'))
    <div class="mb-4 p-3 rounded border border-green-200 bg-green-50 text-green-700">
      {{ session('status') }}
    </div>
  @endif

  @php
    // Détection "vide" compatible paginator / collection / array
    $vide = false;
    if ($lots instanceof \Illuminate\Pagination\LengthAwarePaginator || $lots instanceof \Illuminate\Contracts\Pagination\Paginator) {
        $vide = (($lots->total() ?? 0) === 0);
    } elseif ($lots instanceof \Illuminate\Support\Collection) {
        $vide = $lots->isEmpty();
    } else {
        $vide = empty($lots);
    }
  @endphp

  @if ($vide)
    <div class="p-6 bg-white border rounded-lg text-sm text-gray-600">
      Aucun import pour l’instant.
    </div>
  @else
    <div class="overflow-auto bg-white border rounded-lg">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-3 py-2 border-b text-left">#</th>
            <th class="px-3 py-2 border-b text-left">Fichier</th>
            <th class="px-3 py-2 border-b text-left">Importé par</th>
            <th class="px-3 py-2 border-b text-left">Début</th>
            <th class="px-3 py-2 border-b text-left">Fin</th>
            <th class="px-3 py-2 border-b text-right">Total</th>
            <th class="px-3 py-2 border-b text-right text-green-700">OK</th>
            <th class="px-3 py-2 border-b text-right text-red-700">Erreurs</th>
            <th class="px-3 py-2 border-b text-left">Journal</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($lots as $lot)
            <tr class="odd:bg-white even:bg-gray-50">
              <td class="px-3 py-2 border-b align-top">#{{ $lot->id }}</td>
              <td class="px-3 py-2 border-b align-top">{{ $lot->fichier_source }}</td>
              <td class="px-3 py-2 border-b align-top">
                {{ $lot->importeur?->nom_complet ?? '—' }}
              </td>
              <td class="px-3 py-2 border-b align-top">
                {{ optional($lot->commence_le)->format('d.m.Y H:i') ?? '—' }}
              </td>
              <td class="px-3 py-2 border-b align-top">
                {{ optional($lot->termine_le)->format('d.m.Y H:i') ?? '—' }}
              </td>
              <td class="px-3 py-2 border-b align-top text-right">{{ $lot->lignes_total }}</td>
              <td class="px-3 py-2 border-b align-top text-right text-green-700 font-medium">{{ $lot->lignes_ok }}</td>
              <td class="px-3 py-2 border-b align-top text-right text-red-700 font-medium">{{ $lot->lignes_erreur }}</td>
              <td class="px-3 py-2 border-b align-top">
                @php $j = $lot->journal ?? []; @endphp
                @if (empty($j))
                  <span class="text-gray-400">—</span>
                @else
                  <details class="text-xs">
                    <summary class="cursor-pointer text-gray-700">Voir</summary>
                    <ul class="list-disc ml-5 mt-1 space-y-1">
                      @foreach ($j as $ligne)
                        <li>{{ $ligne }}</li>
                      @endforeach
                    </ul>
                  </details>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="mt-4">
      {{-- Liens de pagination si paginator --}}
      @if ($lots instanceof \Illuminate\Pagination\AbstractPaginator)
        {{ $lots->links() }}
      @endif
    </div>
  @endif
@endsection
