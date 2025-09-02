<?php

namespace App\Http\Controllers\Coordinateur;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use App\Models\Course;
use App\Models\Client;
use App\Models\Utilisateur;
use App\Models\Disponibilite;

class AttributionController extends Controller
{
    public function index(Request $request)
    {
        // sens du tri (par défaut DESC = plus récentes -> plus anciennes)
        $dir = strtolower($request->input('dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        // Courses à attribuer (pas de chauffeur / statut à attribuer)
        $query = Course::query()
            ->when($this->courseHas('chauffeur_id'), fn($q) => $q->whereNull('chauffeur_id'))
            ->when($this->courseHas('statut'), function ($q) {
                $q->whereIn('statut', ['importé','importe','a_attribuer','en_attente','planifié','planifie']);
            });

        $this->filterDates($query, $request);
        $this->filterClient($query, $request);

        // Tri dynamique date/heure selon $dir
        if ($this->courseHas('date_service')) {
            $query->orderBy('date_service', $dir);
        }
        if ($this->courseHas('heure_depart')) {
            $query->orderBy('heure_depart', $dir);
        }

        $courses = $query->paginate(20)->withQueryString();

        // Clients pour l'affichage (toujours safe)
        $clients = Client::query()
            ->select(['id', DB::raw("COALESCE(nom,'') as nom"), DB::raw("COALESCE(prenom,'') as prenom")])
            ->orderBy('nom')->orderBy('prenom')
            ->get();

        // === Base chauffeurs (sélection & tri dynamiques) ===
        [$userSelect, $orderExpr] = $this->userSelectAndOrder();
        $chauffeursBase = Utilisateur::query()
            ->when($this->userHas('role'), function ($q) {
                $q->where(function ($q2) {
                    $q2->where('role', 'chauffeur')
                       ->orWhere('role', 'driver'); // tolérant si autre libellé
                });
            })
            ->when($this->userHas('actif'), fn($q) => $q->where('actif', 1))
            ->when($orderExpr !== null, fn($q) => $q->orderByRaw($orderExpr))
            ->get($userSelect);

        // Injecter les chauffeurs dispo pour chaque course
        foreach ($courses as $c) {
            $date = $this->toYmd($c->date_service);
            $time = $this->toHis($c->heure_depart);
            $c->setAttribute('meta_chauffeurs_dispos', $this->findAvailableDrivers($date, $time, $chauffeursBase));
        }

        return view('coordinateur.courses.a_attribuer', [
            'courses' => $courses,
            'clients' => $clients,
            'dir'     => $dir, // pour le bouton Trier
        ]);
    }

    public function planifiees(Request $request)
    {
        $query = Course::query()
            ->when($this->courseHas('chauffeur_id'), fn($q) => $q->whereNotNull('chauffeur_id'));

        $this->filterDates($query, $request);
        $this->filterClient($query, $request);
        $this->filterChauffeur($query, $request);

        $query->when($this->courseHas('date_service'), fn($q) => $q->orderBy('date_service'))
              ->when($this->courseHas('heure_depart'), fn($q) => $q->orderBy('heure_depart'));

        $courses = $query->paginate(20)->withQueryString();

        $clients = Client::query()
            ->select(['id', DB::raw("COALESCE(nom,'') as nom"), DB::raw("COALESCE(prenom,'') as prenom")])
            ->orderBy('nom')->orderBy('prenom')->get();

        // pour l’affichage des noms chauffeur, même logique que plus haut
        [$userSelect, $orderExpr] = $this->userSelectAndOrder();
        $chauffeurs = Utilisateur::query()
            ->when($orderExpr !== null, fn($q) => $q->orderByRaw($orderExpr))
            ->get($userSelect);

        return view('coordinateur.courses.planifiees', [
            'courses'    => $courses,
            'clients'    => $clients,
            'chauffeurs' => $chauffeurs,
        ]);
    }

    public function attribuer(Request $request, Course $course)
    {
        $data = $request->validate([
            'chauffeur_id' => ['required','integer','exists:utilisateurs,id'],
        ]);

        $course->chauffeur_id = $data['chauffeur_id'];
        if ($this->courseHas('statut')) {
            $course->statut = 'planifié';
        }
        $course->save();

        return back()->with('status', 'Chauffeur attribué avec succès.');
    }

    public function chauffeursForCourse(Request $request, Course $course)
    {
        $date = $this->toYmd($course->date_service);
        $time = $this->toHis($course->heure_depart);

        [$userSelect, $orderExpr] = $this->userSelectAndOrder();
        $chauffeursBase = Utilisateur::query()
            ->when($this->userHas('role'), function ($q) {
                $q->where(function ($q2) {
                    $q2->where('role', 'chauffeur')->orWhere('role', 'driver');
                });
            })
            ->when($this->userHas('actif'), fn($q) => $q->where('actif', 1))
            ->when($orderExpr !== null, fn($q) => $q->orderByRaw($orderExpr))
            ->get($userSelect);

        $dispos = $this->findAvailableDrivers($date, $time, $chauffeursBase);

        return response()->json([
            'course_id'  => $course->id,
            'chauffeurs' => array_values($dispos),
        ]);
    }

    /* ============================ Internes ============================ */

    protected function filterDates($query, Request $request): void
    {
        if (!$this->courseHas('date_service')) return;

        $from = $request->input('date_from');
        $to   = $request->input('date_to');

        if ($from && $this->isYmd($from)) {
            $query->whereDate('date_service', '>=', $from);
        }
        if ($to && $this->isYmd($to)) {
            $query->whereDate('date_service', '<=', $to);
        }
    }

    protected function filterClient($query, Request $request): void
    {
        if (!$this->courseHas('client_id')) return;
        $cid = $request->input('client_id');
        if (is_numeric($cid)) {
            $query->where('client_id', (int)$cid);
        }
    }

    protected function filterChauffeur($query, Request $request): void
    {
        if (!$this->courseHas('chauffeur_id')) return;
        $uid = $request->input('chauffeur_id');
        if (is_numeric($uid)) {
            $query->where('chauffeur_id', (int)$uid);
        }
    }

    /**
     * Récupère les chauffeurs disponibles pour une date (Y-m-d) et une heure (H:i:s).
     */
    protected function findAvailableDrivers(?string $dateYmd, ?string $timeHis, $chauffeursBase): array
    {
        if (!$dateYmd || !$timeHis) return [];

        $disp  = new Disponibilite();
        $table = $disp->getTable();

        $colDate = $this->firstExisting($table, ['date_jour','date','jour','jour_date']);
        $colUser = $this->detectDisponibiliteUserColumn($table);
        if (!$colDate || !$colUser) return [];

        foreach (['heure_debut','heure_fin'] as $c) {
            if (!Schema::hasColumn($table, $c)) return [];
        }

        $duJour = Disponibilite::query()
            ->whereDate($colDate, $dateYmd)
            ->get([$colUser, 'heure_debut', 'heure_fin']);

        if ($duJour->isEmpty()) return [];

        $hCourse = $this->parseHms($timeHis);
        if (!$hCourse) return [];

        $ok = $duJour->filter(function ($row) use ($hCourse) {
            $hd = $this->parseHms($row->heure_debut);
            $hf = $this->parseHms($row->heure_fin);
            return $hd && $hf
                && $hd->lessThanOrEqualTo($hCourse)
                && $hf->greaterThanOrEqualTo($hCourse);
        });

        if ($ok->isEmpty()) return [];

        $ids = $ok->pluck($colUser)->unique()->values();
        $list = $chauffeursBase->whereIn('id', $ids)->values();

        $creneaux = $ok->groupBy($colUser)->map(function ($items) {
            $d = $items->first();
            $fmt = fn($h) => $this->parseHms($h)?->format('H:i');
            return $fmt($d->heure_debut).' → '.$fmt($d->heure_fin);
        });

        return $list->map(function ($u) use ($creneaux) {
            $nom = $u->nom_complet ?? $u->name ?? ('#'.$u->id);
            return [
                'id'      => $u->id,
                'nom'     => $nom,
                'creneau' => $creneaux->get($u->id) ?? null,
            ];
        })->all();
    }

    /* ------------------------- Helpers génériques ------------------------- */

    protected function userSelectAndOrder(): array
    {
        $table = (new Utilisateur())->getTable();

        $select = ['id'];
        $nameCols = [];

        if (Schema::hasColumn($table, 'nom_complet')) {
            $select[]  = 'nom_complet';
            $nameCols[] = 'nom_complet';
        }
        if (Schema::hasColumn($table, 'name')) {
            $select[]  = 'name';
            $nameCols[] = 'name';
        }

        if (empty($nameCols)) {
            return [$select, null];
        }

        $coalesce = "COALESCE(" . implode(", ", $nameCols) . ", '')";
        $orderExpr = $coalesce . " asc";

        return [$select, $orderExpr];
    }

    protected function toYmd($v): ?string
    {
        if (!$v) return null;
        try { return Carbon::parse($v)->toDateString(); } catch (\Throwable $e) { return null; }
    }

    protected function toHis($v): ?string
    {
        if (!$v) return null;
        try { return Carbon::parse($v)->format('H:i:s'); }
        catch (\Throwable $e) {
            if (is_string($v) && preg_match('/^\d{2}:\d{2}/', $v)) {
                return substr($v, 0, 5).':00';
            }
            return null;
        }
    }

    protected function parseHms($v): ?Carbon
    {
        if (!$v) return null;
        try { return Carbon::parse($v); }
        catch (\Throwable $e) {
            if (is_string($v) && preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $v)) {
                $s = strlen($v) === 5 ? $v.':00' : $v;
                try { return Carbon::createFromFormat('H:i:s', $s); } catch (\Throwable $e) { return null; }
            }
            return null;
        }
    }

    protected function firstExisting(string $table, array $cands): ?string
    {
        foreach ($cands as $c) if (Schema::hasColumn($table, $c)) return $c;
        return null;
    }

    protected function detectDisponibiliteUserColumn(string $table): ?string
    {
        foreach (['chauffeur_id','utilisateur_id','user_id','id_chauffeur','id_utilisateur'] as $c) {
            if (Schema::hasColumn($table, $c)) return $c;
        }
        $cols = Schema::getColumnListing($table);
        $cand = collect($cols)->first(function ($c) {
            if ($c === 'id') return false;
            if (!str_ends_with($c, '_id')) return false;
            $hint = stripos($c,'chauff') !== false || stripos($c,'utilis') !== false || stripos($c,'user') !== false;
            return $hint;
        });
        if ($cand) return $cand;

        $idLike = collect($cols)->filter(fn($c) => $c !== 'id' && str_ends_with($c, '_id'))->values();
        return $idLike->count() === 1 ? $idLike->first() : null;
    }

    protected function courseHas(string $col): bool
    {
        try { return Schema::hasColumn((new Course())->getTable(), $col); }
        catch (\Throwable $e) { return false; }
    }

    protected function userHas(string $col): bool
    {
        try { return Schema::hasColumn((new Utilisateur())->getTable(), $col); }
        catch (\Throwable $e) { return false; }
    }

    protected function isYmd(string $s): bool
    {
        return (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', $s);
    }
}
