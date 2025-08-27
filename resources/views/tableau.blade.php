@extends('layouts.app')

@section('contenu')
  <h1 class="text-xl font-semibold mb-4">Tableau de bord</h1>
  <ul class="list-disc ml-6">
    <li><a class="text-blue-600 underline" href="{{ route('telephoniste.import.nouveau') }}">Import Téléphoniste</a></li>
    <li><a class="text-blue-600 underline" href="{{ url('/telephoniste/imports') }}">Historique Imports</a></li>
  </ul>
@endsection
