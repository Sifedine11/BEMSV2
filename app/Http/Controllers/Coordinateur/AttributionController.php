<?php

namespace App\Http\Controllers\Coordinateur;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttributionController extends Controller
{
    public function index(Request $request)
    {
        $courses = Course::query()
            ->whereNull('chauffeur_id')
            ->whereIn('statut', ['importe', 'planifie'])
            ->orderBy('date_service')
            ->orderBy('heure_depart')
            ->paginate(20);

        $chauffeurs = Utilisateur::query()
            ->where('role', 'chauffeur')
            ->where('actif', 1)
            ->orderBy('nom_complet')
            ->get(['id', 'nom_complet']);

        return view('coordinateur.courses.a_attribuer', compact('courses', 'chauffeurs'));
    }

    public function planifiees(Request $request)
    {
        $courses = Course::query()
            ->whereNotNull('chauffeur_id')
            ->whereIn('statut', ['planifie', 'confirme'])
            ->with(['client:id,nom,prenom', 'chauffeur:id,nom_complet'])
            ->orderBy('date_service')
            ->orderBy('heure_depart')
            ->paginate(20);

        return view('coordinateur.courses.planifiees', compact('courses'));
    }

    public function attribuer(Request $request, Course $course)
    {
        $data = $request->validate([
            'chauffeur_id' => ['required','integer','exists:utilisateurs,id'],
        ]);

        DB::transaction(function () use ($course, $data) {
            $course->chauffeur_id = $data['chauffeur_id'];
            if ($course->statut === 'importe') {
                $course->statut = 'planifie';
            }
            $course->save();
        });

        return back()->with('status', 'Chauffeur attribué avec succès.');
    }
}
