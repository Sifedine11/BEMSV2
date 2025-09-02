@extends('layouts.app')

@section('title','Nouveau bénévole')

@section('contenu')
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-lg font-semibold">Nouveau bénévole</h1>
    <a href="{{ route('admin.benevoles.index') }}" class="px-3 py-2 rounded bg-gray-100 text-sm">← Retour</a>
  </div>

  @if ($errors->any())
    <div class="mb-4 p-3 rounded border border-red-200 bg-red-50 text-red-700">
      <ul class="list-disc ml-5 space-y-1">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('admin.benevoles.store') }}" class="bg-white border rounded-lg p-6 space-y-4">
    @csrf

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="text-sm font-medium text-gray-700">Nom <span class="text-red-600">*</span></label>
        <input type="text" name="nom" value="{{ old('nom') }}" required
               class="mt-1 w-full rounded-lg border-gray-300">
      </div>
      <div>
        <label class="text-sm font-medium text-gray-700">Prénom <span class="text-red-600">*</span></label>
        <input type="text" name="prenom" value="{{ old('prenom') }}" required
               class="mt-1 w-full rounded-lg border-gray-300">
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="text-sm font-medium text-gray-700">Email</label>
        <input type="email" name="email" value="{{ old('email') }}"
               class="mt-1 w-full rounded-lg border-gray-300">
      </div>
      <div>
        <label class="text-sm font-medium text-gray-700">Téléphone</label>
        <input type="text" name="tel_mobile" value="{{ old('tel_mobile') }}"
               class="mt-1 w-full rounded-lg border-gray-300">
      </div>
    </div>

    <div>
      <input type="hidden" name="actif" value="0">
      <label class="inline-flex items-center gap-2 text-sm text-gray-700">
        <input type="checkbox" name="actif" value="1" {{ old('actif', 1) ? 'checked' : '' }}
               class="h-4 w-4 rounded border-gray-300">
        <span>Actif</span>
      </label>
    </div>

    <div class="pt-2">
      <button type="submit"
              class="px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-black">
        Créer le bénévole
      </button>
    </div>
  </form>
@endsection
