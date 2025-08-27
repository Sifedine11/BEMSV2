@extends('layouts.app')

@section('title','Mon planning — Semaine')

@section('contenu')
<div class="max-w-6xl mx-auto">
  {{-- En-tête + navigation semaine --}}
  <div class="mb-4 flex items-center justify-between">
    <div>
      <h1 class="text-lg font-semibold">Mon planning — semaine</h1>
      <p class="text-sm text-gray-500">
        Du {{ $debut->format('d.m.Y') }} au {{ $fin->format('d.m.Y') }}
      </p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('chauffeur.planning.semaine', ['semaine' => $prevWeek]) }}"
         class="px-3 py-1.5 rounded border bg-white hover:bg-gray-50 text-sm">
        ← Semaine précédente
      </a>
      <a href="{{ route('chauffeur.planning.semaine') }}"
         class="px-3 py-1.5 rounded border bg-white hover:bg-gray-50 text-sm">
        Cette semaine
      </a>
      <a href="{{ route('chauffeur.planning.semaine', ['semaine' => $nextWeek]) }}"
         class="px-3 py-1.5 rounded border bg-white hover:bg-gray-50 text-sm">
        Semaine suivante →
      </a>
    </div>
  </div>

  {{-- Grille 7 colonnes (une par jour) sur desktop, empilé en mobile --}}
  <div class="grid grid-cols-1 md:grid-cols-7 gap-4">
    @foreach ($jours as $jour)
      @php
        $items = $byDay->get($jour, collect());
        $carb  = \Illuminate\Support\Carbon::parse($jour);
      @endphp

      <div class="rounded-xl border border-gray-200 bg-white shadow-sm flex flex-col">
        <div class="px-3 py-2 border-b">
          <div class="text-xs uppercase tracking-wide text-gray-500">
            {{ $carb->isoFormat('dddd') }}
          </div>
          <div class="text-sm font-medium text-gray-900">
            {{ $carb->format('d.m.Y') }}
          </div>
        </div>

        <div class="p-3 space-y-2">
          @forelse ($items as $course)
            <div class="rounded-lg border border-gray-200 p-3">
              <div class="flex items-center justify-between">
                <div class="text-sm font-semibold">
                  {{ \Illuminate\Support\Str::of($course->heure_depart)->substr(0,5) }}
                </div>
                @php
                  $statut = $course->statut ?? 'planifié';
                  $badgeBase = 'px-2 py-0.5 rounded text-xs';
                  $badgeClr = match ($statut) {
                    'en_cours' => 'bg-amber-50 text-amber-700 border border-amber-200',
                    'termine'  => 'bg-green-50 text-green-700 border border-green-200',
                    'annule'   => 'bg-red-50 text-red-700 border border-red-200',
                    default    => 'bg-sky-50 text-sky-700 border border-sky-200',
                  };
                @endphp
                <span class="{{ $badgeBase }} {{ $badgeClr }}">{{ ucfirst($statut) }}</span>
              </div>

              <div class="mt-1 text-sm text-gray-700">
                <div class="flex items-start gap-2">
                  <svg class="w-4 h-4 text-gray-400 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                          d="M3 7h18M8 3v4M16 3v4M5 11h14v9a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1v-9Z"/>
                  </svg>
                  <span>
                    <span class="font-medium">Client :</span>
                    @if (method_exists($course, 'client') && $course->relationLoaded('client'))
                      {{ $course->client?->nom }} {{ $course->client?->prenom }}
                    @else
                      #{{ $course->client_id }}
                    @endif
                  </span>
                </div>

                <div class="mt-1 flex items-start gap-2">
                  <svg class="w-4 h-4 text-gray-400 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                          d="M10.5 7.5 3 12l7.5 4.5V7.5ZM21 7.5l-7.5 4.5L21 16.5V7.5Z"/>
                  </svg>
                  <div class="space-y-0.5">
                    <div><span class="font-medium">Départ :</span> {{ $course->adresse_depart }}</div>
                    <div><span class="font-medium">Arrivée :</span> {{ $course->adresse_arrivee }}</div>
                  </div>
                </div>

                @if(!empty($course->type_course))
                  <div class="mt-1 text-xs text-gray-500">
                    Type : {{ $course->type_course }}
                  </div>
                @endif
              </div>
            </div>
          @empty
            <div class="text-sm text-gray-500">Aucune course</div>
          @endforelse
        </div>
      </div>
    @endforeach
  </div>

  {{-- Message global s'il n'y a vraiment aucune course de la semaine --}}
  @if ($totalSemaine === 0)
    <div class="mt-6 rounded-xl border border-gray-200 bg-white p-6 text-sm text-gray-600">
      Aucune course planifiée pour cette semaine.
    </div>
  @endif
</div>
@endsection
