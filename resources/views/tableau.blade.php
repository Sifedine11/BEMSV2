@extends('layouts.app')

@section('title','Tableau de bord')

@section('contenu')
  @php
    $k1 = (int)($kpi_courses_du_jour ?? 0);
    $k2 = (int)($kpi_a_attribuer ?? 0);
    $k3 = (int)($kpi_chauffeurs ?? 0);
    $roleAff = $kpi_role ?? '—';

    $labels = $chart_labels ?? ['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'];
    $values = $chart_values ?? [0,0,0,0,0,0,0];
  @endphp

    <div class="flex gap-4">
      <!-- Card 1 -->
      <div class="flex-1 rounded-2xl border bg-white p-6 shadow-sm">
        <div class="text-xs font-medium uppercase tracking-wider text-gray-500">Courses du jour</div>
        <div class="mt-2 text-3xl font-bold leading-none">{{ $k1 }}</div>
        <p class="mt-2 text-sm text-gray-500">Total importées et planifiées</p>
      </div>

      <!-- Card 2 -->
      <div class="flex-1 rounded-2xl border bg-white p-6 shadow-sm">
        <div class="text-xs font-medium uppercase tracking-wider text-gray-500">À attribuer</div>
        <div class="mt-2 text-3xl font-bold leading-none">{{ $k2 }}</div>
        <p class="mt-2 text-sm text-gray-500">En attente de chauffeur</p>
      </div>

      <!-- Card 3 -->
      <div class="flex-1 rounded-2xl border bg-white p-6 shadow-sm">
        <div class="text-xs font-medium uppercase tracking-wider text-gray-500">Chauffeurs actifs</div>
        <div class="mt-2 text-3xl font-bold leading-none">{{ $k3 }}</div>
        <p class="mt-2 text-sm text-gray-500">Disponibles aujourd’hui</p>
      </div>

      <!-- Card 4 -->
      <div class="flex-1 rounded-2xl border bg-white p-6 shadow-sm">
        <div class="text-xs font-medium uppercase tracking-wider text-gray-500">Profil</div>
        <div class="mt-2 text-3xl font-bold leading-none">{{ $roleAff }}</div>
        <p class="mt-2 text-sm text-gray-500">Contexte d’accès</p>
      </div>
    </div>
    <div class="mt-6 bg-white border rounded-2xl p-4 shadow-sm">
        <div class="text-sm font-medium mb-2">Volume de courses (semaine)</div>
        <div class="w-full overflow-hidden">
            <canvas id="chartWeekly" height="500"></canvas>
        </div>
    </div>




  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <script>
    (function() {
      const ctx = document.getElementById('chartWeekly');
      if (!ctx) return;

      const labels = @json($labels);
      const values = @json($values);

      // Ligne douce + points
      new Chart(ctx, {
        type: 'line',
        data: {
          labels,
          datasets: [{
            label: 'Courses',
            data: values,
            tension: 0.35,
            borderWidth: 2,
            pointRadius: 3,
            fill: false,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: { mode: 'index', intersect: false }
          },
          scales: {
            x: {
              grid: { display: true, drawBorder: false }
            },
            y: {
              beginAtZero: true,
              grid: { display: true, drawBorder: false }
            }
          }
        }
      });
    })();
  </script>
@endsection
