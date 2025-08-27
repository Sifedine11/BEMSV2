@php
  use Illuminate\Support\Facades\Route;

  // Rôle réel de l'utilisateur connecté
  $role = auth()->user()->role ?? null;

  // Lien "safe" (si la route n'existe pas encore pendant le dev)
  function lien($name, $fallback = '#') {
      return Route::has($name) ? route($name) : $fallback;
  }

  // État actif pour le menu
  function actif($patterns) {
      foreach ((array)$patterns as $p) {
          if (request()->routeIs($p)) return 'bg-blue-50 text-blue-700';
      }
      return 'text-gray-700 hover:bg-gray-50';
  }
  function iconeClasse($patterns) {
      return request()->routeIs(...(array)$patterns) ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600';
  }
@endphp

<aside class="w-64 h-screen bg-white border-r border-gray-200 flex flex-col">
  <div class="h-16 flex items-center px-4 border-b border-gray-200">
    <span class="text-lg font-bold text-gray-800">Association BEMS</span>
  </div>

  <nav class="flex-1 overflow-y-auto py-4">
    <ul class="space-y-1">

      {{-- COMMUN : Tableau de bord --}}
      <li>
        <a href="{{ lien('tableau') }}"
           class="group flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium {{ actif('tableau') }}">
            <i class='bx bx-home' ></i>
          <span>Tableau de bord</span>
        </a>
      </li>

      @if($role === 'telephoniste')
        {{-- Téléphoniste : Import Excel, Historique --}}
        <li>
          <a href="{{ lien('telephoniste.import.nouveau') }}"
             class="group flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium {{ actif('telephoniste.import.*') }}">
            <i class='bx bx-import' ></i>
            <span>Import Excel</span>
          </a>
        </li>
        <li>
          <a href="{{ lien('telephoniste.imports.index') }}"
             class="group flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium {{ actif('telephoniste.imports.index') }}">
            <i class='bx bx-book-open' ></i>
            <span>Historique</span>
          </a>
        </li>

      @elseif($role === 'coordinateur')
        {{-- Coordinateur : Attribution, Planning Chauffeur --}}
        <li>
          <a href="{{ lien('coordinateur.courses.a_attribuer') }}"
             class="group flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium {{ actif('coordinateur.courses.a_attribuer') }}">
            <i class='bx bx-calendar-plus' ></i>
            <span>Attribution des courses</span>
          </a>
        </li>
        <li>
          <a href="{{ lien('coordinateur.courses.planifiees') }}"
             class="group flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium {{ actif('coordinateur.courses.planifiees') }}">
            <i class='bx bx-calendar'></i>
            <span>Planning Chauffeur</span>
          </a>
        </li>

      @elseif($role === 'chauffeur')
        {{-- Chauffeur : Mon planning, Disponibilités --}}
        <li>
          <a href="{{ lien('chauffeur.planning.semaine') }}"
             class="group flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium {{ actif('chauffeur.planning.*') }}">
            <i class='bx bx-calendar'></i>
            <span>Mon planning</span>
          </a>
        </li>
        <li>
          <a href="{{ lien('chauffeur.dispo.index') }}"
             class="group flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium {{ actif('chauffeur.dispo.*') }}">
            <i class='bx bx-stopwatch'></i>
            <span>Disponibilités</span>
          </a>
        </li>

      @elseif($role === 'admin')
        {{-- Admin : Bénévoles, Clients, Destinations, Utilisateurs --}}
        <li>
          <a href="{{ lien('admin.benevoles.index') }}"
             class="group flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium {{ actif('admin.benevoles.*') }}">
            <i class='bx bx-group'></i>
            <span>Bénévoles</span>
          </a>
        </li>
        <li>
          <a href="{{ lien('admin.clients.index') }}"
             class="group flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium {{ actif('admin.clients.*') }}">
            <i class='bx bx-male-female'></i>
            <span>Clients</span>
          </a>
        </li>
        <li>
          <a href="{{ lien('admin.destinations.index') }}"
             class="group flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium {{ actif('admin.destinations.*') }}">
            <i class='bx bx-map'></i>
            <span>Destinations</span>
          </a>
        </li>
        <li>
          <a href="{{ lien('admin.utilisateurs.index') }}"
             class="group flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium {{ actif('admin.utilisateurs.*') }}">
            <i class='bx bxs-user-detail'></i>
            <span>Utilisateurs</span>
          </a>
        </li>
      @endif

      {{-- Commun : Mon profil / Paramètres --}}
      <li class="pt-4 mt-4 border-t text-xs uppercase tracking-wider text-gray-400 px-3">Compte</li>

      <li>
        <a href="{{ lien('profile.edit') }}"
           class="group flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium {{ actif('profile.*') }}">
            <i class='bx bx-user'></i>
          <span>Mon profil</span>
        </a>
      </li>
      <li>
        <a href="{{ lien('parametres.index') }}"
           class="group flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium {{ actif('parametres.*') }}">
            <i class='bx bx-cog' ></i>
          <span>Paramètre</span>
        </a>
      </li>

      {{-- Déconnexion --}}
      <li class="px-3">
        <form method="POST" action="{{ route('logout') }}" class="mt-2">
          @csrf
          <button type="submit"
                  class="w-full text-left flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium text-red-600 hover:bg-red-50">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M16 13v-2H7V8l-5 4 5 4v-3h9Z M20 3h-8v2h8v14h-8v2h8a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2Z"/></svg>
            Se déconnecter
          </button>
        </form>
      </li>
    </ul>
  </nav>
</aside>
