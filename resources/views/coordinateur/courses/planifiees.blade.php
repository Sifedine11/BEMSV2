@extends('layouts.app')

@section('title', 'Courses planifiées')

@section('contenu')
  <div class="flex items-center justify-between mb-3">
    <h1 class="text-lg font-semibold">Courses planifiées</h1>
    <a href="{{ route('coordinateur.courses.a_attribuer') }}" class="text-sm px-3 py-1.5 rounded bg-gray-100">
      Attribution des courses
    </a>
  </div>

  @php
    $clients    = $clients    ?? collect();
    $chauffeurs = $chauffeurs ?? collect();

    $hasFilters = request()->filled('date_from') || request()->filled('date_to') || request()->filled('chauffeur_id') || request()->filled('client_id');

    $chipClient = null;
    if (request('client_id')) {
      $cl = $clients->firstWhere('id', (int) request('client_id'));
      $chipClient = $cl ? trim(($cl->nom ?? '').' '.($cl->prenom ?? '')) ?: ($cl->nom ?? $cl->prenom ?? ('#'.request('client_id'))) : '#'.request('client_id');
    }
    $chipChauffeur = null;
    if (request('chauffeur_id')) {
      $ch = $chauffeurs->firstWhere('id', (int) request('chauffeur_id'));
      $chipChauffeur = $ch->nom_complet ?? $ch->name ?? ('#'.request('chauffeur_id'));
    }
  @endphp

  {{-- Barre de filtres compacte (bouton à gauche) --}}
  <div x-data="{ open: false }" class="mb-3">
    <div class="flex items-center gap-3">
      <button @click="open = !open"
              class="inline-flex items-center gap-1 text-sm px-3 py-1.5 rounded-lg border bg-white hover:bg-gray-50">
        <svg class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 4h18M6 12h12M10 20h4"/>
        </svg>
        Filtres
      </button>

      <div class="flex flex-wrap items-center gap-2 text-xs">
        @if($hasFilters)
          <span class="px-2 py-1 rounded-full bg-gray-100 text-gray-700">Filtres actifs :</span>

          @if(request('date_from'))
            <a href="{{ request()->fullUrlWithQuery(['date_from'=>null, 'page'=>null]) }}"
               class="px-2 py-1 rounded-full bg-white border text-gray-700 hover:bg-gray-50">
              Début : {{ request('date_from') }} ✕
            </a>
          @endif

          @if(request('date_to'))
            <a href="{{ request()->fullUrlWithQuery(['date_to'=>null, 'page'=>null]) }}"
               class="px-2 py-1 rounded-full bg-white border text-gray-700 hover:bg-gray-50">
              Fin : {{ request('date_to') }} ✕
            </a>
          @endif

          @if(request('client_id'))
            <a href="{{ request()->fullUrlWithQuery(['client_id'=>null, 'page'=>null]) }}"
               class="px-2 py-1 rounded-full bg-white border text-gray-700 hover:bg-gray-50">
              Client : {{ $chipClient }} ✕
            </a>
          @endif

          @if(request('chauffeur_id'))
            <a href="{{ request()->fullUrlWithQuery(['chauffeur_id'=>null, 'page'=>null]) }}"
               class="px-2 py-1 rounded-full bg-white border text-gray-700 hover:bg-gray-50">
              Chauffeur : {{ $chipChauffeur }} ✕
            </a>
          @endif

          <a href="{{ route('coordinateur.courses.planifiees') }}"
             class="px-2 py-1 rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200">
            Réinitialiser
          </a>
        @else
          <span class="text-xs text-gray-500"></span>
        @endif
      </div>
    </div>

    {{-- Formulaire repliable, étroit --}}
    <form x-show="open" x-transition
          method="GET" action="{{ route('coordinateur.courses.planifiees') }}"
          class="mt-2 inline-flex flex-wrap items-end gap-2 p-3 rounded-lg border bg-white">
      <div class="w-40">
        <label class="text-xs text-gray-600">Date début</label>
        <input type="date" name="date_from" value="{{ request('date_from') }}"
               class="mt-1 w-full rounded-lg border-gray-300 text-sm">
      </div>
      <div class="w-40">
        <label class="text-xs text-gray-600">Date fin</label>
        <input type="date" name="date_to" value="{{ request('date_to') }}"
               class="mt-1 w-full rounded-lg border-gray-300 text-sm">
      </div>
      <div class="w-56">
        <label class="text-xs text-gray-600">Client</label>
        <select name="client_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
          <option value="">— Tous</option>
          @foreach($clients as $cl)
            @php $lib = trim(($cl->nom ?? '').' '.($cl->prenom ?? '')); @endphp
            <option value="{{ $cl->id }}" @selected(request('client_id')==$cl->id)>{{ $lib ?: ('#'.$cl->id) }}</option>
          @endforeach
        </select>
      </div>
      <div class="w-56">
        <label class="text-xs text-gray-600">Chauffeur</label>
        <select name="chauffeur_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm">
          <option value="">— Tous</option>
          @foreach($chauffeurs as $ch)
            <option value="{{ $ch->id }}" @selected(request('chauffeur_id')==$ch->id)>{{ $ch->nom_complet ?? $ch->name ?? ('#'.$ch->id) }}</option>
          @endforeach
        </select>
      </div>
      <div class="flex gap-2">
        <button class="px-3 py-2 rounded-lg bg-gray-900 text-white text-sm">Filtrer</button>
        <a href="{{ route('coordinateur.courses.planifiees') }}" class="px-3 py-2 rounded-lg bg-gray-100 text-sm">Reset</a>
      </div>
    </form>
  </div>

  @if ($courses->isEmpty())
    <div class="p-6 bg-white border rounded-lg text-sm text-gray-600">
      Aucune course planifiée.
    </div>
  @else
    <div class="overflow-auto bg-white border rounded-lg">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-3 py-2 border-b text-left">Date</th>
            <th class="px-3 py-2 border-b text-left">Heure</th>
            <th class="px-3 py-2 border-b text-left">Client</th>
            <th class="px-3 py-2 border-b text-left">Chauffeur</th>
            <th class="px-3 py-2 border-b text-left">Départ</th>
            <th class="px-3 py-2 border-b text-left">Arrivée</th>
            <th class="px-3 py-2 border-b text-left">Statut</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($courses as $course)
            @php
              $dateAff  = $course->date_service ? \Illuminate\Support\Carbon::parse($course->date_service)->format('Y-m-d') : '—';
              $heureAff = $course->heure_depart ? \Illuminate\Support\Carbon::parse($course->heure_depart)->format('H:i') : '—';
              $cl = $clients->firstWhere('id', $course->client_id);
              $clientNom = $cl ? trim(($cl->nom ?? '').' '.($cl->prenom ?? '')) ?: ($cl->nom ?? $cl->prenom ?? ('#'.$course->client_id)) : '#'.$course->client_id;
              $ch = $chauffeurs->firstWhere('id', $course->chauffeur_id);
              $chauffeurNom = $ch->nom_complet ?? $ch->name ?? ($course->chauffeur_id ? '#'.$course->chauffeur_id : '—');
            @endphp
            <tr class="odd:bg-white even:bg-gray-50">
              <td class="px-3 py-2 border-b">{{ $dateAff }}</td>
              <td class="px-3 py-2 border-b">{{ $heureAff }}</td>
              <td class="px-3 py-2 border-b">{{ $clientNom }}</td>
              <td class="px-3 py-2 border-b">{{ $chauffeurNom }}</td>
              <td class="px-3 py-2 border-b">{{ $course->adresse_depart }}</td>
              <td class="px-3 py-2 border-b">{{ $course->adresse_arrivee }}</td>
              <td class="px-3 py-2 border-b">{{ $course->statut }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="mt-4">
      {{ $courses->links() }}
    </div>
  @endif
@endsection
