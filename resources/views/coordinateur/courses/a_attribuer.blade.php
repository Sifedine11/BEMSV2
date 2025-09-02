@extends('layouts.app')

@section('title', 'Attribution des courses')

@section('contenu')
<div class="flex items-center justify-between mb-3 ">
    <h1 class="text-lg font-semibold">Attribution des courses</h1>
</div>

@php
  // petit helper pour afficher HH:MM
  $fmt = function ($h) {
      try { return \Illuminate\Support\Carbon::parse($h)->format('H:i'); }
      catch (\Throwable $e) {
          return is_string($h) ? substr($h, 0, 5) : '';
      }
  };
  // map id -> "Nom Prénom" pour l'affichage client
  $clientsMap = isset($clients)
      ? collect($clients)->keyBy('id')->map(function($c){
          $nom = $c->nom ?? ($c->name ?? ('#'.$c->id));
          $prenom = $c->prenom ?? '';
          return trim($nom.' '.$prenom);
      })
      : collect();

  // --- Prépa pour barre de filtres (même style que planifiees) ---
  $clients = $clients ?? collect();

  $hasFilters = request()->filled('date_from') || request()->filled('date_to') || request()->filled('client_id');

  $chipClient = null;
  if (request('client_id')) {
    $cl = $clients->firstWhere('id', (int) request('client_id'));
    $chipClient = $cl
      ? (trim(($cl->nom ?? ($cl->name ?? '')) . ' ' . ($cl->prenom ?? '')) ?: (($cl->nom ?? $cl->prenom ?? $cl->name) ?? ('#'.request('client_id'))))
      : '#'.request('client_id');
  }

  // Tri (toggle asc/desc) — on conserve le style des boutons
  $dir = strtolower(request('dir', 'desc'));
  $isAsc = $dir === 'asc';
  $nextDir = $isAsc ? 'desc' : 'asc';
@endphp

<div class="flex items-center justify-between mb-4">
  <div class="flex items-center gap-2">
    {{-- Barre de filtres compacte (bouton à gauche) --}}
    <div x-data="{ open: false }" class="w-full">
      <div class="flex items-center gap-3">
        <button @click="open = !open"
                class="inline-flex items-center gap-1 text-sm px-3 py-1.5 rounded-lg border bg-white hover:bg-gray-50">
          <svg class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 4h18M6 12h12M10 20h4"/>
          </svg>
          Filtres
        </button>

        {{-- Bouton Trier (asc/desc) au même style --}}
        <a href="{{ route('coordinateur.courses.a_attribuer', array_merge(request()->query(), ['dir' => $nextDir, 'page'=>null])) }}"
           class="inline-flex items-center gap-1 text-sm px-3 py-1.5 rounded-lg border bg-white hover:bg-gray-50">
          @if($isAsc)
            {{-- flèche vers le haut (croissant) --}}
            <svg class="w-4 h-4 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" d="M8 7l4-4 4 4M12 3v14M4 21h16"/>
            </svg>
            Trier (croissant)
          @else
            {{-- flèche vers le bas (décroissant) --}}
            <svg class="w-4 h-4 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" d="M8 17l4 4 4-4M12 21V7M4 3h16"/>
            </svg>
            Trier (décroissant)
          @endif
        </a>

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

            <a href="{{ route('coordinateur.courses.a_attribuer') }}"
               class="px-2 py-1 rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200">
              Réinitialiser
            </a>
          @else
            <span class="text-xs text-gray-500"></span>
          @endif
        </div>
      </div>

      {{-- Formulaire repliable, étroit (identique à planifiees) --}}
      <form x-show="open" x-transition
            method="GET" action="{{ route('coordinateur.courses.a_attribuer') }}"
            class="mt-2 inline-flex flex-wrap items-end gap-2 p-3 rounded-lg border bg-white">
        {{-- garder le sens de tri actuel --}}
        <input type="hidden" name="dir" value="{{ $dir }}">
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
              @php
                $lib = trim(($cl->nom ?? ($cl->name ?? '')) . ' ' . ($cl->prenom ?? ''));
              @endphp
              <option value="{{ $cl->id }}" @selected(request('client_id')==$cl->id)>{{ $lib ?: ('#'.$cl->id) }}</option>
            @endforeach
          </select>
        </div>
        <div class="flex gap-2">
          <button class="px-3 py-2 rounded-lg bg-gray-900 text-white text-sm">Filtrer</button>
          <a href="{{ route('coordinateur.courses.a_attribuer') }}" class="px-3 py-2 rounded-lg bg-gray-100 text-sm">Reset</a>
        </div>
      </form>
    </div>
  </div>

  <a href="{{ route('coordinateur.courses.planifiees') }}"
     class="text-sm px-3 py-1.5 rounded bg-gray-100">
    Voir les courses planifiées
  </a>
</div>

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

@if ($courses->isEmpty())
  <div class="p-6 bg-white border rounded-lg text-sm text-gray-600">
    Aucune course à attribuer.
  </div>
@else
  <div class="overflow-auto bg-white border rounded-lg">
    <table class="min-w-full text-sm">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-3 py-2 border-b text-left">Date</th>
          <th class="px-3 py-2 border-b text-left">Heure</th>
          <th class="px-3 py-2 border-b text-left">Client</th>
          <th class="px-3 py-2 border-b text-left">Départ</th>
          <th class="px-3 py-2 border-b text-left">Arrivée</th>
          <th class="px-3 py-2 border-b text-left">Type</th>
          <th class="px-3 py-2 border-b text-left">Attribuer</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($courses as $course)
          @php
            // mapping A/R -> Aller/Retour
            $typeLabel = null;
            $t = strtoupper((string)($course->type_course ?? ''));
            if ($t === 'A')      $typeLabel = 'Aller';
            elseif ($t === 'R')  $typeLabel = 'Retour';
            elseif ($t !== '')   $typeLabel = $t;
          @endphp
          <tr class="odd:bg-white even:bg-gray-50 align-top">
            <td class="px-3 py-2 border-b">
              {{ \Illuminate\Support\Carbon::parse($course->date_service)->format('Y-m-d') }}
            </td>
            <td class="px-3 py-2 border-b">
              {{ $fmt($course->heure_depart) }}
            </td>
            <td class="px-3 py-2 border-b">
              @php
                $labelClient = $clientsMap->get($course->client_id) ?? ('#'.$course->client_id);
              @endphp
              {{ $labelClient }}
            </td>
            <td class="px-3 py-2 border-b">{{ $course->adresse_depart }}</td>
            <td class="px-3 py-2 border-b">{{ $course->adresse_arrivee }}</td>
            <td class="px-3 py-2 border-b">{{ $typeLabel ?? '—' }}</td>
            <td class="px-3 py-2 border-b">
              <button type="button"
                      class="px-3 py-1.5 rounded bg-gray-900 text-white text-sm"
                      data-open-modal="assign-{{ $course->id }}">
                Attribuer
              </button>

              {{-- MODALE d’attribution (carré centré, taille moyenne) --}}
              <div id="assign-{{ $course->id }}"
                   class="modal-overlay hidden fixed inset-0 z-50">
                <div class="absolute inset-0 bg-black/50" data-close-modal></div>

                <div class="modal-card absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2
                            w-full max-w-md rounded-2xl bg-white shadow-xl border border-gray-200">
                  <div class="flex items-center justify-between px-4 py-3 border-b">
                    <div class="font-semibold text-sm">Attribuer la course</div>
                    <button class="text-gray-500 hover:text-gray-700" data-close-modal aria-label="Fermer">✕</button>
                  </div>

                  <div class="px-4 py-3 text-xs text-gray-600 border-b">
                    <div><span class="font-medium">Date :</span> {{ \Illuminate\Support\Carbon::parse($course->date_service)->format('Y-m-d') }}</div>
                    <div><span class="font-medium">Heure :</span> {{ $fmt($course->heure_depart) }}</div>
                    <div class="truncate"><span class="font-medium">Départ :</span> {{ $course->adresse_depart }}</div>
                    <div class="truncate"><span class="font-medium">Arrivée :</span> {{ $course->adresse_arrivee }}</div>
                  </div>

                  <div class="px-4 py-3">
                    <div class="text-sm mb-2">Chauffeurs disponibles</div>

                    <div class="max-h-80 overflow-y-auto overscroll-contain pr-1" data-scrollbox>
                      @php
                        $dispos = $course->meta_chauffeurs_dispos ?? [];
                      @endphp

                      @if (empty($dispos))
                        <div class="text-xs text-gray-500">Aucun chauffeur disponible pour ce créneau.</div>
                      @else
                        <ul class="divide-y">
                          @foreach ($dispos as $ch)
                            <li class="py-2">
                              <form method="POST"
                                    action="{{ route('coordinateur.courses.attribuer', ['course' => $course->id]) }}"
                                    class="flex items-center justify-between gap-2">
                                @csrf
                                <div>
                                  <div class="text-sm font-medium">{{ $ch['nom'] }}</div>
                                  <div class="text-xs text-gray-500">{{ $ch['creneau'] }}</div>
                                </div>
                                <input type="hidden" name="chauffeur_id" value="{{ $ch['id'] }}">
                                <button class="px-3 py-1.5 rounded bg-gray-900 text-white text-xs">
                                  Attribuer
                                </button>
                              </form>
                            </li>
                          @endforeach
                        </ul>
                      @endif
                    </div>

                    <div class="mt-3 text-right">
                      <button class="px-3 py-1.5 rounded-lg border bg-white hover:bg-gray-50 text-sm" data-close-modal>Fermer</button>
                    </div>
                  </div>
                </div>
              </div>
              {{-- fin modale --}}
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="mt-4">
    {{ $courses->links() }}
  </div>
@endif

<script>
  // Modales Attribuer (taille moyenne, centre écran, scroll interne)
  (function(){
    const openers = document.querySelectorAll('[data-open-modal]');
    const body = document.body;

    function openModal(id) {
      const el = document.getElementById(id);
      if (!el) return;
      el.classList.remove('hidden');
      body.style.overflow = 'hidden';
    }
    function closeModal(el) {
      el.classList.add('hidden');
      body.style.overflow = '';
    }

    openers.forEach(btn => {
      btn.addEventListener('click', () => openModal(btn.getAttribute('data-open-modal')));
    });

    document.querySelectorAll('.modal-overlay').forEach(overlay => {
      overlay.addEventListener('click', (e) => {
        if (e.target.matches('[data-close-modal], .modal-overlay > .absolute.bg-black\\/50')) {
          closeModal(overlay);
        }
      });

      // empêcher le scroll de la page quand on scrolle la liste
      const box = overlay.querySelector('[data-scrollbox]');
      if (box) {
        box.addEventListener('wheel', (evt) => {
          evt.stopPropagation();
        }, { passive: false });
        box.addEventListener('touchmove', (evt) => {
          evt.stopPropagation();
        }, { passive: false });
      }
    });
  })();
</script>
@endsection
