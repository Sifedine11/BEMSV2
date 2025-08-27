@extends('layouts.app')

@section('title', 'Accès refusé')

@section('contenu')
  <div class="max-w-xl mx-auto bg-white border rounded-lg p-6">
    <h1 class="text-lg font-semibold mb-2">Accès refusé (403)</h1>
    <p class="text-sm text-gray-600">
      Vous n’avez pas les droits pour accéder à cette page.
    </p>

    <div class="mt-4 flex gap-2">
      <a href="{{ route('tableau') }}" class="px-3 py-1.5 rounded bg-gray-900 text-white">Retour au tableau</a>
      @if(auth()->check())
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button class="px-3 py-1.5 rounded bg-gray-100">Se déconnecter</button>
        </form>
      @endif
    </div>
  </div>
@endsection
