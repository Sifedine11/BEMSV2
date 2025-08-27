@php
  $roleActuel = session('role_visuel', auth()->user()->role ?? 'telephoniste');
@endphp

<header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4">
  <div class="text-sm text-gray-500">
    Connecté comme : <span class="font-medium text-gray-900">{{ auth()->user()->nom_complet ?? 'Utilisateur' }}</span>
  </div>

  <div class="flex items-center gap-3">
    {{-- Sélecteur de rôle visuel (pour adapter la sidebar) --}}
    <form action="{{ route('role.switch') }}" method="POST">
      @csrf
      <select name="role_visuel" class="border rounded px-2 py-1 text-sm" onchange="this.form.submit()">
        @foreach (['telephoniste'=>'Téléphoniste','coordinateur'=>'Coordinateur','chauffeur'=>'Chauffeur','admin'=>'Admin'] as $val => $lib)
          <option value="{{ $val }}" {{ $roleActuel===$val ? 'selected' : '' }}>{{ $lib }}</option>
        @endforeach
      </select>
    </form>
  </div>
</header>
