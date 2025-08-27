@extends('layouts.app')

@section('title', 'Courses planifiées')

@section('contenu')
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-lg font-semibold">Courses planifiées</h1>
    <a href="{{ route('coordinateur.courses.a_attribuer') }}" class="text-sm px-3 py-1.5 rounded bg-gray-100">
      Retour à l’attribution
    </a>
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
            <tr class="odd:bg-white even:bg-gray-50">
              <td class="px-3 py-2 border-b">{{ $course->date_service }}</td>
              <td class="px-3 py-2 border-b">{{ \Illuminate\Support\Str::of($course->heure_depart)->substr(0,5) }}</td>
              <td class="px-3 py-2 border-b">
                @if ($course->relationLoaded('client'))
                  {{ $course->client?->nom }} {{ $course->client?->prenom }}
                @else
                  #{{ $course->client_id }}
                @endif
              </td>
              <td class="px-3 py-2 border-b">
                @if ($course->relationLoaded('chauffeur'))
                  {{ $course->chauffeur?->nom_complet }}
                @else
                  #{{ $course->chauffeur_id }}
                @endif
              </td>
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
