<?php

namespace App\Http\Controllers\Telephoniste;

use App\Http\Controllers\Controller;
use App\Http\Requests\Telephoniste\ImportCoursRequest;
use App\Models\CodePostal;
use App\Models\Client;
use App\Models\Course;
use App\Models\LigneImport;
use App\Models\LotImport;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportCoursController extends Controller
{
    // Dossier unique d’import (conforme à ton arborescence)
    private const IMPORT_DIR = 'private/imports_tmp';

    public function index()
    {
        return view('telephoniste.import.index');
    }

    /**
     * Upload + prévisualisation (20 premières lignes).
     */
    public function previsualiser(ImportCoursRequest $request)
    {
        $fichier = $request->file('fichier');

        // 1) Dossier garanti
        Storage::disk('local')->makeDirectory(self::IMPORT_DIR);

        // 2) Enregistrement sous storage/app/private/imports_tmp/...
        $nomFinal = uniqid('import_') . '_' . preg_replace('/[^\w\-.]+/u', '_', $fichier->getClientOriginalName());
        $chemin   = $fichier->storeAs(self::IMPORT_DIR, $nomFinal, 'local'); // <-- chemin relatif à storage/app

        // 3) Vérification avec Storage
        if (!Storage::disk('local')->exists($chemin)) {
            return back()->withErrors([
                'fichier' => "Fichier introuvable après l’upload (chemin: {$chemin}). Vérifie les droits d’écriture de storage/app."
            ]);
        }
        $fullPath = Storage::disk('local')->path($chemin);

        // 4) Lecture classeur
        $spreadsheet = IOFactory::load($fullPath);
        $sheet = $spreadsheet->getSheetByName('Transfert') ?? $spreadsheet->getSheet(0);
        $tableau = $sheet->toArray(null, true, true, false);

        if (empty($tableau) || count($tableau) < 2) {
            return back()->withErrors(['fichier' => 'Impossible de lire des données (onglet "Transfert").']);
        }

        // 5) Headers + aperçu
        $headers = $this->normalizeHeaders(array_map('strval', array_shift($tableau)));
        $apercu = [];
        foreach (array_slice($tableau, 0, 20) as $row) {
            $apercu[] = $this->assocByHeaders($headers, $row);
        }

        // 6) Session d’import
        $token = 'import_' . bin2hex(random_bytes(8));
        session([
            'import_token' => $token,
            $token => [
                'chemin'           => $chemin, // ex: private/imports_tmp/...
                'headers'          => $headers,
                'fichier_original' => $fichier->getClientOriginalName(),
                'total_lignes'     => count($tableau),
            ]
        ]);

        return back()->with([
            'status'           => 'Fichier chargé. Vérifie la prévisualisation puis confirme.',
            'previsualisation' => $apercu,
            'headers'          => $headers,
            'token_import'     => $token,
            'fichier_nom'      => $fichier->getClientOriginalName(),
        ]);
    }

    /**
     * Confirmation = création lot + lignes_import + courses (statut=importe).
     */
    public function confirmer(Request $request)
    {
        $request->validate([
            'token_import' => ['required','string']
        ]);

        $token = (string) $request->input('token_import');

        $session = session($token);
        if (!is_array($session)) {
            return redirect()->route('telephoniste.import.nouveau')
                ->with('status', 'Session d’import expirée ou invalide. Recommencez.');
        }

        $chemin  = $session['chemin']  ?? null;   // ex: private/imports_tmp/...
        $headers = $session['headers'] ?? null;

        if (!$chemin || !$headers) {
            return redirect()->route('telephoniste.import.nouveau')
                ->with('status', 'Données d’import manquantes. Recommencez.');
        }

        // ✅ Vérification avec Storage (même disque/chemin qu’à l’upload)
        if (!Storage::disk('local')->exists($chemin)) {
            return redirect()->route('telephoniste.import.nouveau')
                ->withErrors(['fichier' => "Fichier introuvable ({$chemin}). Le fichier a peut-être été supprimé ou déplacé. Refais la prévisualisation."]);
        }
        $fullPath = Storage::disk('local')->path($chemin);

        // Lecture
        $spreadsheet = IOFactory::load($fullPath);
        $sheet = $spreadsheet->getSheetByName('Transfert') ?? $spreadsheet->getSheet(0);
        $tableau = $sheet->toArray(null, true, true, false);
        if (empty($tableau) || count($tableau) < 2) {
            return redirect()->route('telephoniste.import.nouveau')
                ->withErrors(['fichier' => 'Impossible de relire des données.']);
        }
        array_shift($tableau); // en-têtes

        $lot = new LotImport([
            'fichier_source' => $session['fichier_original'] ?? basename($chemin),
            'importe_par_id' => auth()->id(),
            'commence_le'    => now(),
            'lignes_total'   => count($tableau),
            'lignes_ok'      => 0,
            'lignes_erreur'  => 0,
            'journal'        => [],
        ]);
        $lot->save();
        $journal = [];
        $ok = 0; $ko = 0;

        DB::beginTransaction();
        try {
            foreach ($tableau as $index => $row) {
                $assoc = $this->assocByHeaders($headers, $row);
                $ligneImport = new LigneImport([
                    'lot_import_id' => $lot->id,
                    'ligne_brute'   => $assoc,
                    'statut'        => 'ok',
                    'created_at'    => now(),
                ]);

                try {
                    $course = $this->creerCourseDepuisExcel($assoc, $lot->id);
                    $ligneImport->course_id = $course?->id;
                    $ligneImport->save();
                    $ok++;
                } catch (\Throwable $e) {
                    $ligneImport->statut = 'erreur';
                    $ligneImport->message_erreur = $e->getMessage();
                    $ligneImport->save();
                    $ko++;
                    $journal[] = "Ligne ".($index+2).": ".$e->getMessage();
                }
            }

            $lot->lignes_ok = $ok;
            $lot->lignes_erreur = $ko;
            $lot->termine_le = now();
            $lot->journal = $journal;
            $lot->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('telephoniste.import.nouveau')
                ->withErrors(['fichier' => 'Erreur durant l’import: '.$e->getMessage()]);
        } finally {
            // Nettoyage session
            session()->forget(['import_token', $token]);
        }

        return redirect()->route('telephoniste.imports.index')
            ->with('status', "Import terminé: {$ok} ok, {$ko} erreurs.");
    }

    // ---------- Helpers ----------

    private function normalizeHeaders(array $headers): array
    {
        $out = [];
        foreach ($headers as $h) {
            $h = trim((string)$h);
            $h = strtr($h, ['à'=>'a','â'=>'a','ä'=>'a','é'=>'e','è'=>'e','ê'=>'e','ë'=>'e','î'=>'i','ï'=>'i','ô'=>'o','ö'=>'o','û'=>'u','ü'=>'u','ç'=>'c','À'=>'A','Â'=>'A','Ä'=>'A','É'=>'E','È'=>'E','Ê'=>'E','Ë'=>'E','Î'=>'I','Ï'=>'I','Ô'=>'O','Ö'=>'O','Û'=>'U','Ü'=>'U','Ç'=>'C']);
            $h = strtolower($h);
            $h = preg_replace('/[^a-z0-9]+/','_', $h);
            $h = trim($h, '_');
            $out[] = $h;
        }
        return $out;
    }

    private function assocByHeaders(array $headers, array $row): array
    {
        $assoc = [];
        foreach ($headers as $i => $key) {
            $assoc[$key] = array_key_exists($i, $row) ? (is_string($row[$i]) ? trim($row[$i]) : $row[$i]) : null;
        }
        return $assoc;
    }

    private function parseDate(?string $d): ?string
    {
        if (!$d) return null;
        try { return Carbon::createFromFormat('d.m.Y', $d)->format('Y-m-d'); } catch (\Throwable $e) {}
        if (strtotime($d)) return Carbon::parse($d)->format('Y-m-d');
        return null;
    }

    private function parseTime(?string $t): ?string
    {
        if (!$t) return null;
        $t = str_replace('.', ':', $t);
        try { return Carbon::parse($t)->format('H:i:s'); } catch (\Throwable $e) { return null; }
    }

    private function parseDateTime(?string $d): ?string
    {
        if (!$d) return null;
        $d = str_replace('.', '-', $d);
        try { return Carbon::parse($d)->format('Y-m-d H:i:s'); } catch (\Throwable $e) { return null; }
    }

    private function extractCodePostalIdFromAdresse(?string $adresse): ?int
    {
        if (!$adresse) return null;
        if (preg_match('/\b(\d{4})\b/', $adresse, $m)) {
            $cp = $m[1];
            $code = CodePostal::where('code_postal', $cp)->first();
            return $code?->id;
        }
        return null;
    }

    private function findOrCreateClient(array $row): Client
    {
        $prenom = Arr::get($row, 'prenom') ?? Arr::get($row, 'pre_nom') ?? '';
        $nom    = Arr::get($row, 'nom') ?? '';
        $mobile = Arr::get($row, 'mobile') ?? null;
        $fixe   = Arr::get($row, 'fixe') ?? null;

        $client = Client::whereRaw('LOWER(nom) = ? AND LOWER(prenom) = ?', [mb_strtolower($nom), mb_strtolower($prenom)])->first();
        if ($client) {
            if (!$client->tel_mobile && $mobile) $client->tel_mobile = $mobile;
            if (!$client->tel_fixe && $fixe) $client->tel_fixe = $fixe;
            $client->save();
            return $client;
        }

        return Client::create([
            'nom'         => $nom ?: 'Inconnu',
            'prenom'      => $prenom ?: 'Inconnu',
            'tel_mobile'  => $mobile,
            'tel_fixe'    => $fixe,
            'actif'       => 1,
        ]);
    }

    private function creerCourseDepuisExcel(array $row, int $lotId): Course
    {
        $dateService   = $this->parseDate(Arr::get($row, 'date'));
        $heureDepart   = $this->parseTime(Arr::get($row, 'heure_de_depart'));
        $heureArrivee  = $this->parseTime(Arr::get($row, 'arrivee_souhaitee'));
        $type          = strtoupper((string) Arr::get($row, 'type'));
        if (!in_array($type, ['A','D','R'], true)) $type = 'A';

        $depart        = Arr::get($row, 'depart');
        $arrivee       = Arr::get($row, 'arrivee');
        $tempAt        = (string) Arr::get($row, 'tempat');
        $hclass        = $this->parseTime(Arr::get($row, 'hclass'));
        $distCalc      = $this->toDecimal(Arr::get($row, 'distance_calculee'));
        $distCyble     = $this->toDecimal(Arr::get($row, 'distance_cyble'));
        $utilPrix      = $this->toBool(Arr::get($row, 'utiliser_prix_communique'));
        $reserveLe     = $this->parseDateTime(Arr::get($row, 'reserve_le'));

        $client        = $this->findOrCreateClient($row);

        $course = new Course([
            'date_service'             => $dateService,
            'heure_depart'             => $heureDepart,
            'heure_arrivee'            => $heureArrivee,
            'type_course'              => $type,
            'statut'                   => 'importe',

            'client_id'                => $client->id,
            'chauffeur_id'             => null,
            'destination_id'           => null,

            'adresse_depart'           => $depart,
            'code_postal_depart_id'    => $this->extractCodePostalIdFromAdresse($depart),
            'adresse_arrivee'          => $arrivee,
            'code_postal_arrivee_id'   => $this->extractCodePostalIdFromAdresse($arrivee),

            'temps_attente_min'        => $this->toMinutes($tempAt),
            'heure_classement'         => $hclass,
            'distance_km'              => $distCalc,
            'distance_source_km'       => $distCyble,
            'utiliser_prix_communique' => $utilPrix,

            'commentaires'             => $this->composeCommentaires($row),
            'lot_import_id'            => $lotId,
            'reserve_le'               => $reserveLe,
        ]);
        $course->save();

        return $course;
    }

    private function composeCommentaires(array $row): ?string
    {
        $pieces = [];
        foreach ([
            'type_assurance' => 'Assurance',
            'instructions_ponctuelles_au_transporteur' => 'Instr. transporteur',
            'informations_aux_coordinateurs' => 'Info coordinateurs',
            'moyens_auxiliaires' => 'Moyens',
            'prestataire' => 'Prestataire',
            'vehicule' => 'Véhicule',
            'ville_hors_ville' => 'Zone',
            'pc' => 'PC',
            'rfm' => 'RFM',
        ] as $key => $label) {
            $val = Arr::get($row, $key);
            if ($val !== null && $val !== '') $pieces[] = $label.': '.$val;
        }
        return empty($pieces) ? null : implode(' | ', $pieces);
    }

    private function toMinutes($val): int
    {
        if ($val === null || $val === '') return 0;
        $s = (string)$val;
        if (preg_match('/^\d+$/', $s)) return (int)$s;
        try { $c = Carbon::parse($s); return $c->hour * 60 + $c->minute; } catch (\Throwable $e) { return 0; }
    }

    private function toDecimal($val): ?string
    {
        if ($val === null || $val === '') return null;
        $s = str_replace([' ', '’', "'"], '', (string)$val);
        $s = str_replace(',', '.', $s);
        return is_numeric($s) ? number_format((float)$s, 2, '.', '') : null;
    }

    private function toBool($val): bool
    {
        $s = strtoupper(trim((string)$val));
        return in_array($s, ['1','OUI','O','YES','Y','TRUE'], true);
    }
}
