<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $hasTable = fn(string $t) => Schema::hasTable($t);
        $hasCol   = function (string $t, string $c) {
            try { return Schema::hasColumn($t, $c); } catch (\Throwable $e) { return false; }
        };

        $coursesTbl = null;
        foreach (['courses', 'course', 'trajets', 'trajet'] as $t) {
            if ($hasTable($t)) { $coursesTbl = $t; break; }
        }

        $countToday = 0;
        $countAAtribuer = 0;
        $weekly = array_fill(0, 7, 0);

        if ($coursesTbl) {
            $qToday = DB::table($coursesTbl);

            $dateCol = $hasCol($coursesTbl,'date_service') ? 'date_service'
                     : ($hasCol($coursesTbl,'date') ? 'date' : null);

            if ($dateCol) {
                $qToday = $qToday->whereDate($dateCol, $today);
            }

            if ($hasCol($coursesTbl,'statut')) {
                $importPlan = ['importe','importé','importee','importée','planifie','planifié','planifiee','planifiée','planifiees','planifiées','planifier'];
                $qToday = $qToday->whereIn('statut', $importPlan);
            }

            $countToday = (int) $qToday->count();

            $qAA = DB::table($coursesTbl);
            if ($dateCol) {
                $qAA = $qAA->whereDate($dateCol, '>=', $today);
            }
            if ($hasCol($coursesTbl,'chauffeur_id')) {
                $qAA = $qAA->whereNull('chauffeur_id');
            }
            if ($hasCol($coursesTbl,'statut')) {
                $qAA = $qAA->whereIn('statut', ['importe','importé','a_attribuer','à_attribuer','en_attente']);
            }
            $countAAtribuer = (int) $qAA->count();

            $start = (clone $today)->startOfWeek(Carbon::MONDAY);
            $end   = (clone $today)->endOfWeek(Carbon::SUNDAY);
            if ($dateCol) {
                $rows = DB::table($coursesTbl)
                    ->selectRaw("DATE($dateCol) as d, COUNT(*) as c")
                    ->whereDate($dateCol, '>=', $start)
                    ->whereDate($dateCol, '<=', $end)
                    ->groupBy('d')
                    ->get();

                $map = [];
                foreach ($rows as $r) {
                    $map[$r->d] = (int) $r->c;
                }
                for ($i = 0; $i < 7; $i++) {
                    $day = (clone $start)->addDays($i)->toDateString();
                    $weekly[$i] = $map[$day] ?? 0;
                }
            }
        }

        $utilTbl = null;
        foreach (['utilisateurs', 'users', 'user'] as $t) {
            if ($hasTable($t)) { $utilTbl = $t; break; }
        }

        $chauffeursActifs = 0;
        if ($utilTbl) {
            $roleCol = $hasCol($utilTbl,'role') ? 'role' : null;
            $actifCol = $hasCol($utilTbl,'actif') ? 'actif' : null;

            $q = DB::table($utilTbl);
            if ($roleCol) {
                $q = $q->whereIn($roleCol, ['chauffeur','driver']);
            }
            if ($actifCol) {
                $q = $q->where($actifCol, 1);
            }
            $chauffeursActifs = (int) $q->count();
        }

        $dispoTbl = null;
        foreach (['creneaux_disponibilite','creneau_disponibilite','disponibilites','disponibilite'] as $t) {
            if ($hasTable($t)) { $dispoTbl = $t; break; }
        }
        if ($dispoTbl) {
            $dateCol = $hasCol($dispoTbl,'date_jour') ? 'date_jour' : ($hasCol($dispoTbl,'date') ? 'date' : null);
            $chauffCol = $hasCol($dispoTbl,'chauffeur_id') ? 'chauffeur_id' : ($hasCol($dispoTbl,'utilisateur_id') ? 'utilisateur_id' : null);
            if ($dateCol && $chauffCol) {
                $disposToday = DB::table($dispoTbl)
                    ->whereDate($dateCol, $today)
                    ->distinct()
                    ->count($chauffCol);


                if ($disposToday > 0) {
                    $chauffeursActifs = (int) $disposToday;
                }
            }
        }

        // Rôle affiché
        $roleLabel = $user?->role ? ucfirst($user->role) : '—';

        // Labels pour le graphe
        $labels = ['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'];

        return view('tableau', [
            'kpi_courses_du_jour' => $countToday,
            'kpi_a_attribuer'     => $countAAtribuer,
            'kpi_chauffeurs'      => $chauffeursActifs,
            'kpi_role'            => $roleLabel,
            'chart_labels'        => $labels,
            'chart_values'        => $weekly,
        ]);
    }
}
