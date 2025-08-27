@extends('layouts.app')

@section('title','Import des courses')

@section('contenu')
  <h1 class="text-lg font-semibold mb-2">Import des courses (Excel)</h1>
  <p class="text-sm text-gray-500 mb-4">
    Dépose un fichier <strong>.xlsx</strong> (onglet <em>Transfert</em>). Les courses seront créées avec statut
    <em>importé</em> et sans chauffeur (colonne « Transporteur » ignorée).
  </p>

  {{-- Messages d'erreur / statut --}}
  @if ($errors->any())
    <div class="p-3 mb-4 bg-red-50 text-red-700 border border-red-200 rounded">
      <ul class="list-disc ml-5">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  @if(session('status'))
    <div class="p-3 mb-4 bg-green-50 text-green-700 border border-green-200 rounded">
      {{ session('status') }}
    </div>
  @endif

  {{-- 1) FORMULAIRE DE PRÉVISUALISATION --}}
  <div class="bg-white border rounded-lg p-6 mb-6">
    <form id="form-previsu"
          action="{{ route('telephoniste.import.previsualiser') }}"
          method="POST"
          enctype="multipart/form-data"
          class="space-y-4">
      @csrf

      <div>
        <label class="block text-sm mb-2 font-medium">Fichier Excel (.xlsx)</label>
        <input type="file"
               id="fichier"
               name="fichier"
               accept=".xlsx,.xls"
               required
               class="block w-full border rounded px-3 py-2">
        <p class="text-xs text-gray-500 mt-1">Onglet attendu : <em>Transfert</em>. Les autres onglets sont ignorés.</p>
      </div>

      <div class="flex items-center gap-3">
        <button type="submit"
                id="btn-previsu"
                class="px-4 py-2 bg-gray-900 text-white rounded-lg disabled:opacity-50"
                disabled>
          Prévisualiser
        </button>

        <a href="{{ route('telephoniste.imports.index') }}"
           class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg">
          Historique
        </a>
      </div>
    </form>
  </div>

  {{-- 2) APERÇU + BOUTON DE CONFIRMATION (après prévisualisation) --}}
  @if(session('previsualisation') && session('headers'))
    <div class="bg-white border rounded-lg p-6">
      <div class="flex items-center justify-between mb-3">
        <h2 class="font-medium">
          Aperçu (20 premières lignes) — {{ session('fichier_nom') }}
        </h2>
        <span class="text-xs text-gray-500">
          Lignes affichées : {{ count(session('previsualisation', [])) }}
        </span>
      </div>

      <div class="overflow-auto border rounded">
        <table class="min-w-full text-xs md:text-sm">
          <thead class="bg-gray-50">
            <tr>
              @foreach(session('headers') as $h)
                <th class="px-3 py-2 border-b text-left font-medium text-gray-600 whitespace-nowrap">{{ $h }}</th>
              @endforeach
            </tr>
          </thead>
          <tbody>
            @foreach(session('previsualisation') as $i => $row)
              <tr class="odd:bg-white even:bg-gray-50">
                @foreach(session('headers') as $h)
                  <td class="px-3 py-2 border-b align-top whitespace-nowrap">{{ $row[$h] ?? '' }}</td>
                @endforeach
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <form action="{{ route('telephoniste.import.confirmer') }}" method="POST" class="mt-4">
        @csrf
        <input type="hidden" name="token_import" value="{{ session('token_import') }}">
        <button class="px-4 py-2 bg-green-600 text-white rounded-lg">
          Confirmer l’import
        </button>
      </form>
    </div>
  @endif

  <script>
    (function(){
      const input = document.getElementById('fichier');
      const btn   = document.getElementById('btn-previsu');
      if (input && btn) {
        input.addEventListener('change', () => {
          btn.disabled = !input.files || input.files.length === 0;
        });
      }
    })();
  </script>
@endsection
