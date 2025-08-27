@extends('layouts.app')

@section('title', 'Attribution des courses')

@section('contenu')
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-lg font-semibold">Attribution des courses</h1>
    <a href="{{ route('coordinateur.courses.planifiees') }}" class="text-sm px-3 py-1.5 rounded bg-gray-100">
      Voir les courses planifiées
    </a>
  </div>

  @if (session('status'))
    <div class="mb-4 p-3 rounded border border-green-200 bg-green-50 text-green-700">
      {{ session('status') }}
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
            <tr class="odd:bg-white even:bg-gray-50">
              <td class="px-3 py-2 border-b">{{ $course->date_service }}</td>
              <td class="px-3 py-2 border-b">{{ \Illuminate\Support\Str::of($course->heure_depart)->substr(0,5) }}</td>
              <td class="px-3 py-2 border-b">
                {{-- si relation client() définie, sinon adapte --}}
                @if (method_exists($course, 'client') && $course->relationLoaded('client'))
                  {{ $course->client?->nom }} {{ $course->client?->prenom }}
                @else
                  #{{ $course->client_id }}
                @endif
              </td>
              <td class="px-3 py-2 border-b">{{ $course->adresse_depart }}</td>
              <td class="px-3 py-2 border-b">{{ $course->adresse_arrivee }}</td>
              <td class="px-3 py-2 border-b">{{ $course->type_course }}</td>
              <td class="px-3 py-2 border-b">
                <form method="POST" action="{{ route('coordinateur.courses.attribuer', $course) }}" class="flex items-center gap-2">
                  @csrf
                  <select name="chauffeur_id" class="border rounded px-2 py-1">
                    @foreach ($chauffeurs as $ch)
                      <option value="{{ $ch->id }}">{{ $ch->nom_complet }}</option>
                    @endforeach
                  </select>
                  <button class="px-3 py-1.5 rounded bg-gray-900 text-white">Attribuer</button>
                </form>
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
@endsection
