@extends('layouts.app')

@section('contenu')
<div class="max-w-5xl mx-auto p-4">
    <h1 class="text-lg font-semibold mb-4">Mes disponibilités</h1>

    @if(session('ok'))
        <div class="mb-3 rounded-lg border border-green-200 bg-green-50 text-green-800 px-3 py-2 text-sm">
            {{ session('ok') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-3 rounded-lg border border-red-200 bg-red-50 text-red-800 px-3 py-2 text-sm">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Formulaire d’ajout --}}
    <div class="rounded-xl border border-gray-200 p-4 shadow-sm bg-white">
        <form method="POST" action="{{ route('chauffeur.dispo.store') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
            @csrf

            <div class="md:col-span-3">
                <label class="text-sm font-medium text-gray-700">Date</label>
                <input type="date" name="date" value="{{ request('date', $dateSelection) }}"
                       class="mt-1 w-full rounded-lg border-gray-300">
            </div>

            <div class="md:col-span-2">
                <label class="text-sm font-medium text-gray-700">Début</label>
                <input type="time" name="heure_debut" id="heure_debut"
                       class="mt-1 w-full rounded-lg border-gray-300" required>
            </div>

            <div class="md:col-span-2">
                <label class="text-sm font-medium text-gray-700">Fin</label>
                <input type="time" name="heure_fin" id="heure_fin"
                       class="mt-1 w-full rounded-lg border-gray-300" required>
            </div>

            <div class="md:col-span-3">
                <label class="text-sm font-medium text-gray-700">Presets</label>
                <div class="mt-1 flex flex-wrap gap-2">
                    <button type="button" data-preset="matin" class="px-3 py-1.5 rounded-lg border text-sm hover:bg-gray-50">Matin</button>
                    <button type="button" data-preset="apm" class="px-3 py-1.5 rounded-lg border text-sm hover:bg-gray-50">Après-midi</button>
                    <button type="button" data-preset="journee" class="px-3 py-1.5 rounded-lg border text-sm hover:bg-gray-50">Journée</button>
                </div>
            </div>

            <div class="md:col-span-2">
                <label class="text-sm font-medium text-gray-700">Répéter</label>
                <label class="mt-1 flex items-center gap-2 text-sm">
                    <input type="checkbox" name="repeter" value="1" class="rounded border-gray-300">
                    <span>Toutes les semaines (4)</span>
                </label>
            </div>

            <div class="md:col-span-12">
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-gray-900 text-white px-4 py-2 text-sm">
                    Ajouter
                </button>
            </div>
        </form>
        <div class="mt-3 rounded-lg border border-yellow-200 bg-yellow-50 text-yellow-800 px-3 py-2 text-sm">
            ⚠️ Attention : si vous indiquez une disponibilité jusqu’à <strong>18h</strong> (par exemple),
            cela signifie que vous acceptez de commencer une course à <strong>18h</strong>,
            donc celle-ci pourra se terminer plus tard.
        </div>
    </div>

    {{-- Liste du jour sélectionné --}}
    <div class="mt-4 rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex items-center justify-between px-4 py-3 border-b">
            <div class="font-semibold">Aujourd'hui</div>
            <form method="POST" action="{{ route('chauffeur.dispo.destroyJour') }}">
                @csrf
                @method('DELETE')
                <input type="hidden" name="date" value="{{ $dateSelection }}"/>
                <button class="text-xs px-3 py-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50">
                    Tout supprimer ce jour
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-gray-600 font-medium">Début</th>
                        <th class="px-4 py-2 text-left text-gray-600 font-medium">Fin</th>
                        <th class="px-4 py-2 text-right text-gray-600 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($creneauxDuJour as $c)
                        <tr class="border-t">
                            <td class="px-4 py-2">{{ \Illuminate\Support\Carbon::parse($c->heure_debut)->format('H:i') }}</td>
                            <td class="px-4 py-2">{{ \Illuminate\Support\Carbon::parse($c->heure_fin)->format('H:i') }}</td>
                            <td class="px-4 py-2 text-right">
                                <form method="POST" action="{{ route('chauffeur.dispo.destroy', $c->id) }}">
                                    @csrf @method('DELETE')
                                    <button class="text-xs px-3 py-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50">
                                        Supprimer
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr class="border-t">
                            <td colspan="3" class="px-4 py-6 text-center text-gray-500">Aucun créneau ce jour.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mes prochaines disponibilités --}}
    <div class="mt-6 rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="px-4 py-3 border-b font-semibold">
            Mes prochaines disponibilités
        </div>

        <div class="divide-y">
            @php
                $grouped = $prochainesDispos->groupBy(fn($c) => \Illuminate\Support\Carbon::parse($c->date_jour)->toDateString());
            @endphp

            @forelse($grouped as $jour => $items)
                <div class="px-4 py-3">
                    <div class="text-sm font-medium text-gray-700 mb-2">
                    {{ \Illuminate\Support\Str::ucfirst(\Illuminate\Support\Carbon::parse($jour)->locale('fr')->isoFormat('dddd D MMMM YYYY')) }}
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-gray-600 font-medium">Début</th>
                                    <th class="px-4 py-2 text-left text-gray-600 font-medium">Fin</th>
                                    <th class="px-4 py-2 text-right text-gray-600 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $c)
                                    <tr class="border-t">
                                        <td class="px-4 py-2">{{ \Illuminate\Support\Carbon::parse($c->heure_debut)->format('H:i') }}</td>
                                        <td class="px-4 py-2">{{ \Illuminate\Support\Carbon::parse($c->heure_fin)->format('H:i') }}</td>
                                        <td class="px-4 py-2 text-right">
                                            <form method="POST" action="{{ route('chauffeur.dispo.destroy', $c->id) }}">
                                                @csrf @method('DELETE')
                                                <button class="text-xs px-3 py-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50">
                                                    Supprimer
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="px-4 py-6 text-center text-gray-500">
                    Aucune disponibilité à venir.
                </div>
            @endforelse
        </div>
    </div>
</div>

<script>
document.querySelectorAll('[data-preset]').forEach(btn => {
    btn.addEventListener('click', () => {
        const type = btn.getAttribute('data-preset');
        const d = document.getElementById('heure_debut');
        const f = document.getElementById('heure_fin');
        if (!d || !f) return;

        if (type === 'matin')       { d.value = '08:00'; f.value = '12:00'; }
        else if (type === 'apm')    { d.value = '13:00'; f.value = '18:00'; }
        else if (type === 'journee'){ d.value = '08:00'; f.value = '18:00'; }
    });
});
</script>
@endsection
