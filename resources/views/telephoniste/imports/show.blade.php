@extends('layouts.app')

@section('title', 'Import #'.$lot->id)

@section('contenu')
  @php
    $fmtTime = function ($t) {
      try { return $t ? \Illuminate\Support\Carbon::parse($t)->format('H:i') : '—'; }
      catch (\Throwable $e) { return is_string($t) && preg_match('/^\d{2}:\d{2}/', $t) ? substr($t,0,5) : '—'; }
    };
    $fmtDate = function ($d) {
      try { return $d ? \Illuminate\Support\Carbon::parse($d)->format('d.m.Y') : '—'; }
      catch (\Throwable $e) { return '—'; }
    };
    $fmtPrice = function ($p) {
      return is_numeric($p) ? 'CHF '.number_format((float)$p, 2, '.', ' ') : '—';
    };
    $clientsMap = collect($clients ?? [])->map(function($c){
      $nom = $c->nom ?? '';
      $prenom = $c->prenom ?? '';
      return trim($nom.' '.$prenom);
    });
  @endphp

  <div class="flex items-center justify-between mb-4">
    <h1 class="text-lg font-semibold">Import #{{ $lot->id }}</h1>
    <a href="{{ route('telephoniste.import.historique') }}"
       class="px-3 py-1.5 rounded-lg border bg-white hover:bg-gray-50 text-sm">Retour à l’historique</a>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    {{-- Carte infos lot --}}
    <div class="lg:col-span-1 bg-white border rounded-lg p-4">
      <div class="text-sm text-gray-500">Fichier</div>
      <div class="font-medium">{{ $lot->fichier_source ?? '—' }}</div>

      <div class="mt-3 text-sm text-gray-500">Importé par</div>
      <div class="font-medium">{{ $lot->importeur?->nom_complet ?? '—' }}</div>

      <div class="mt-3 text-sm text-gray-500">Démarré</div>
      <div class="font-medium">{{ optional($lot->commence_le)->format('d.m.Y H:i') ?? '—' }}</div>

      <div class="mt-3 text-sm text-gray-500">Terminé</div>
      <div class="font-medium">{{ optional($lot->termine_le)->format('d.m.Y H:i') ?? '—' }}</div>

      <div class="mt-4 grid grid-cols-3 gap-2">
        <div class="rounded-lg border p-2 text-center">
          <div class="text-xs text-gray-500">Total</div>
          <div class="font-semibold">{{ $lot->lignes_total ?? 0 }}</div>
        </div>
        <div class="rounded-lg border p-2 text-center">
          <div class="text-xs text-green-700">OK</div>
          <div class="font-semibold text-green-700">{{ $lot->lignes_ok ?? 0 }}</div>
        </div>
        <div class="rounded-lg border p-2 text-center">
          <div class="text-xs text-red-700">Erreurs</div>
          <div class="font-semibold text-red-700">{{ $lot->lignes_erreur ?? 0 }}</div>
        </div>
      </div>
    </div>

    {{-- Journal --}}
    <div class="lg:col-span-2 bg-white border rounded-lg p-4">
      <div class="font-semibold mb-2">Journal</div>
      @php $j = $lot->journal ?? []; if (is_string($j)) { $decoded = json_decode($j,true); if (json_last_error()===JSON_ERROR_NONE) $j=$decoded; } @endphp
      @if (empty($j))
        <div class="text-sm text-gray-500">Aucun message.</div>
      @else
        <ul class="list-disc ml-5 space-y-1 text-sm">
          @foreach ($j as $ligne)
            <li>{{ $ligne }}</li>
          @endforeach
        </ul>
      @endif
    </div>
  </div>

  {{-- Lignes importées (courses) --}}
  <div class="mt-4 bg-white border rounded-lg">
    <div class="px-4 py-3 border-b font-semibold">Courses importées</div>

    @if ($courses->isEmpty())
      <div class="p-4 text-sm text-gray-600">Aucune course liée à ce lot.</div>
    @else
      <div class="overflow-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-3 py-2 border-b text-left">Date</th>
              <th class="px-3 py-2 border-b text-left">Heure</th>
              <th class="px-3 py-2 border-b text-left">Client</th>
              <th class="px-3 py-2 border-b text-left">Départ</th>
              <th class="px-3 py-2 border-b text-left">Arrivée</th>
              <th class="px-3 py-2 border-b text-left">Type</th>
              <th class="px-3 py-2 border-b text-right">Prix</th>
              <th class="px-3 py-2 border-b text-left">Statut</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($courses as $c)
              @php
                $type = strtoupper((string)($c->type_course ?? ''));
                $typeLabel = $type === 'A' ? 'Aller' : ($type === 'R' ? 'Retour' : ($type ?: '—'));
                $clientLabel = $clientsMap->get($c->client_id) ?? ('#'.$c->client_id);
              @endphp
              <tr class="odd:bg-white even:bg-gray-50">
                <td class="px-3 py-2 border-b">{{ $fmtDate($c->date_service) }}</td>
                <td class="px-3 py-2 border-b">{{ $fmtTime($c->heure_depart) }}</td>
                <td class="px-3 py-2 border-b">{{ $clientLabel }}</td>
                <td class="px-3 py-2 border-b">{{ $c->adresse_depart }}</td>
                <td class="px-3 py-2 border-b">{{ $c->adresse_arrivee }}</td>
                <td class="px-3 py-2 border-b">{{ $typeLabel }}</td>
                <td class="px-3 py-2 border-b text-right">{{ $fmtPrice($c->prix_aller_calcule) }}</td>
                <td class="px-3 py-2 border-b">{{ $c->statut ?? '—' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
@endsection
