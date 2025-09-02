@extends('layouts.app')

@section('title','Mon planning — Semaine')

@section('contenu')
@php
  use Illuminate\Support\Carbon;

  // helpers d'affichage
  $fmtTime = function ($t) {
    try { return $t ? Carbon::parse($t)->format('H:i') : '—'; }
    catch (\Throwable $e) {
      return is_string($t) && preg_match('/^\d{2}:\d{2}/', $t) ? substr($t,0,5) : '—';
    }
  };

  $fmtPrice = function ($p) {
    return is_numeric($p) ? 'CHF '.number_format((float)$p, 2, '.', ' ') : '—';
  };
@endphp

<div class="mx-auto">
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
        $carb  = Carbon::parse($jour);
        // "Lundi 1 septembre"
        $labelDate = ucfirst($carb->locale('fr')->isoFormat('dddd D MMMM'));
      @endphp

      <div class="rounded-2xl border border-gray-200 bg-white shadow-sm flex flex-col">
        <div class="px-4 py-3 border-b">
          <div class="text-sm font-semibold text-gray-900">
            {{ $labelDate }}
          </div>
        </div>

        <div class="p-3 space-y-2 max-h-[72vh] overflow-y-auto">
          @forelse ($items as $course)
            @php
              $client = isset($clients) ? $clients->get($course->client_id) : null;
              $nomClient = $client
                ? trim(($client->nom ?? '').' '.($client->prenom ?? ''))
                : ('#'.$course->client_id);

              $statut = $course->statut ?? 'planifié';
              $badgeBase = 'px-2 py-0.5 rounded text-xs';
              $badgeClr = match (strtolower($statut)) {
                'en_cours'           => 'bg-amber-50 text-amber-700 border border-amber-200',
                'terminé','termine'  => 'bg-green-50 text-green-700 border border-green-200',
                'annulé','annule'    => 'bg-red-50 text-red-700 border border-red-200',
                default              => 'bg-sky-50 text-sky-700 border border-sky-200',
              };

              $modalId = 'details-'.$course->id;
            @endphp

            <div class="rounded-xl border border-gray-200 p-3">
              <div class="flex items-center justify-between">
                <div class="text-sm font-semibold">
                  {{ $fmtTime($course->heure_depart) }}
                </div>
                <div class="mt-2 text-right">
                <button type="button"
                        data-open="{{ $modalId }}"
                        class="px-2.5 p-1 rounded bg-gray-900 text-white text-xs">
                  Détails
                </button>
              </div>
              </div>

              <div class="mt-1 text-sm text-gray-700">
                <div class="flex items-start gap-2">
                  <svg class="w-4 h-4 text-gray-400 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                          d="M3 7h18M8 3v4M16 3v4M5 11h14v9a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1v-9Z"/>
                  </svg>
                  <span><span class="font-medium">Client :</span> {{ $nomClient }}</span>
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

                @php
                   $typeLabel = null;
                    $t = strtoupper((string)($course->type_course ?? ''));
                    if ($t === 'A')      $typeLabel = 'Aller';
                    elseif ($t === 'R')  $typeLabel = 'Retour';
                    elseif ($t !== '')   $typeLabel = $t; // fallback si autre valeur
                    @endphp
                    @if($typeLabel)
                    <div class="mt-1 text-xs text-gray-500">
                        Type : {{ $typeLabel }}
                    </div>
                @endif

                <div class="mt-1">
                  <span class="font-medium text-sm">Prix :</span>
                  <span class="text-sm">{{ $fmtPrice($course->prix_aller_calcule) }}</span>
                </div>
              </div>
            </div>

            {{-- Modale Détails client (compacte, centrée) --}}
            <div id="{{ $modalId }}" class="details-modal hidden fixed inset-0 z-50">
              <div class="absolute inset-0 bg-black/50" data-close></div>
              <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2
                          w-full max-w-md rounded-2xl bg-white shadow-xl border">
                <div class="px-4 py-3 border-b flex items-center justify-between">
                  <div class="font-semibold text-sm">Détails client</div>
                  <button class="text-gray-500 hover:text-gray-700" data-close>✕</button>
                </div>
                <div class="px-4 py-3 text-sm">
                  <div><span class="text-gray-500">Nom :</span> <span class="font-medium">{{ $nomClient }}</span></div>

                  {{-- Prix et horaire --}}
                  <div class="mt-1"><span class="text-gray-500">Heure :</span> <span class="font-medium">{{ $fmtTime($course->heure_depart) }}</span></div>
                  @if($client)
                    @if(property_exists($client,'tel_mobile'))
                      <div class="mt-1"><span class="text-gray-500">Mobile :</span> <span class="font-medium">{{ $client->tel_mobile ?: '—' }}</span></div>
                    @endif
                    @if(property_exists($client,'tel_fixe'))
                      <div><span class="text-gray-500">Fixe :</span> <span class="font-medium">{{ $client->tel_fixe ?: '—' }}</span></div>
                    @endif
                    @if(property_exists($client,'moyens_auxiliaires'))
                      <div class="mt-2">
                        <span class="text-gray-500">Moyens auxiliaires :</span>
                        <div class="font-medium">{{ $client->moyens_auxiliaires ?: '—' }}</div>
                      </div>
                    @endif
                    @if(property_exists($client,'niveau_aide'))
                      <div>
                        <span class="text-gray-500">Niveau d’aide :</span>
                        <span class="font-medium">{{ $client->niveau_aide ?: '—' }}</span>
                      </div>
                    @endif
                  @endif
                </div>
                <div class="px-4 py-3 border-t text-right">
                  <button class="px-3 py-1.5 rounded border bg-white hover:bg-gray-50 text-sm" data-close>Fermer</button>
                </div>
              </div>
            </div>
            {{-- fin modale --}}
          @empty
            <div class="text-sm text-gray-500">Aucune course</div>
          @endforelse
        </div>
      </div>
    @endforeach
  </div>

  {{-- Message global d’avertissement --}}
  <div class="mt-6 rounded-lg border border-yellow-200 bg-yellow-50 text-yellow-800 px-3 py-2 text-sm">
    ⚠️ Attention : si vous souhaitez <strong>annuler une course</strong> qui vous a été attribuée,
    vous devez impérativement passer par le coordinateur :
    <a href="mailto:coordinateurs@example.email" class="underline">coordinateurs@example.email</a>.
  </div>

  {{-- Message global s'il n'y a vraiment aucune course de la semaine --}}
  @if ($totalSemaine === 0)
    <div class="mt-6 rounded-xl border border-gray-200 bg-white p-6 text-sm text-gray-600">
      Aucune course planifiée pour cette semaine.
    </div>
  @endif
</div>

<script>
  // Ouverture / fermeture des modales "Détails"
  (function(){
    const openers = document.querySelectorAll('[data-open]');
    const body = document.body;

    function open(id){
      const m = document.getElementById(id);
      if(!m) return;
      m.classList.remove('hidden');
      body.style.overflow = 'hidden';
    }
    function close(modal){
      modal.classList.add('hidden');
      body.style.overflow = '';
    }

    openers.forEach(btn => {
      btn.addEventListener('click', () => open(btn.getAttribute('data-open')));
    });

    document.querySelectorAll('.details-modal').forEach(m => {
      m.addEventListener('click', (e) => {
        if (e.target.hasAttribute('data-close')) close(m);
      });
    });
  })();
</script>
@endsection
