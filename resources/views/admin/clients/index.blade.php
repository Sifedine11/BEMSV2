@extends('layouts.app')

@section('title','Clients')

@section('contenu')
@php
  use Illuminate\Support\Facades\Schema;
  $hasLocalite = Schema::hasColumn('clients','localite');
  $hasMobile   = Schema::hasColumn('clients','tel_mobile');
  $hasFixe     = Schema::hasColumn('clients','tel_fixe');
@endphp

  <div class="flex items-center justify-between mb-4">
    <h1 class="text-lg font-semibold">Clients</h1>
    <a href="{{ route('admin.clients.create') }}"
       class="px-3 py-2 rounded-lg bg-gray-900 text-white text-sm">Nouveau client</a>
  </div>

  @if (session('status'))
    <div class="mb-4 p-3 rounded border border-green-200 bg-green-50 text-green-700">
      {{ session('status') }}
    </div>
  @endif

  <form method="GET" action="{{ route('admin.clients.index') }}" class="mb-3">
    <div class="flex items-center gap-2">
      <input type="text" name="q" value="{{ $q }}" placeholder="Rechercher (nom, prénom, localité, mobile, fixe)"
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
            <a href="{{ route('admin.clients.index', ['q'=>$q,'sort'=>'nom','dir'=>$sort==='nom' && $dir==='asc' ? 'desc':'asc']) }}"
               class="hover:underline">Nom</a>
          </th>
          <th class="px-3 py-2 border-b text-left">
            <a href="{{ route('admin.clients.index', ['q'=>$q,'sort'=>'prenom','dir'=>$sort==='prenom' && $dir==='asc' ? 'desc':'asc']) }}"
               class="hover:underline">Prénom</a>
          </th>

          @if($hasLocalite)
            <th class="px-3 py-2 border-b text-left">Localité</th>
          @endif

          @if($hasMobile)
            <th class="px-3 py-2 border-b text-left">Mobile</th>
          @endif

          @if($hasFixe)
            <th class="px-3 py-2 border-b text-left">Fixe</th>
          @endif

          <th class="px-3 py-2 border-b text-left">
            <a href="{{ route('admin.clients.index', ['q'=>$q,'sort'=>'actif','dir'=>$sort==='actif' && $dir==='asc' ? 'desc':'asc']) }}"
               class="hover:underline">Actif</a>
          </th>
          <th class="px-3 py-2 border-b text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($clients as $c)
          <tr class="odd:bg-white even:bg-gray-50">
            <td class="px-3 py-2 border-b align-top">{{ $c->nom ?? '—' }}</td>
            <td class="px-3 py-2 border-b align-top">{{ $c->prenom ?? '—' }}</td>

            @if($hasLocalite)
              <td class="px-3 py-2 border-b align-top">{{ $c->localite ?? '—' }}</td>
            @endif

            @if($hasMobile)
              <td class="px-3 py-2 border-b align-top">{{ $c->tel_mobile ?? '—' }}</td>
            @endif

            @if($hasFixe)
              <td class="px-3 py-2 border-b align-top">{{ $c->tel_fixe ?? '—' }}</td>
            @endif
            <td class="px-3 py-2 border-b align-top">
            <span class="text-xs px-2 py-0.5 rounded-xl {{ (int)($c->actif ?? 0) ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-gray-100 text-gray-600 border' }}">
                {{ (int)($c->actif ?? 0) ? 'Oui' : 'Non' }}
            </span>
            </td>

            <td class="px-3 py-2 border-b align-top text-right">
              <div class="inline-flex gap-2">

                <a href="{{ route('admin.clients.edit', $c) }}" class="px-2 py-1.5 text-sm rounded-xl border bg-white hover:bg-gray-50"><i class='bx bxs-edit'></i></a>
                <form method="POST" action="{{ route('admin.clients.destroy', $c) }}"
                      onsubmit="return confirm('Supprimer ce client ?')">
                  @csrf @method('DELETE')
                    <button class="px-2 py-1.5 text-sm rounded-xl border border-red-200 bg-red-50 text-red-700 hover:bg-red-100"><i class='bx bx-trash'></i></button>                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="px-3 py-6 text-center text-gray-500">Aucun client.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">
    {{ $clients->links() }}
  </div>
@endsection
