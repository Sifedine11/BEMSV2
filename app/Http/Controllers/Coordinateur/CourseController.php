<?php

namespace App\Http\Controllers\Coordinateur;

use App\Http\Controllers\Controller;

class CourseController extends Controller
{
    public function aAttribuer()
    {
        return view('coordinateur.courses.a_attribuer');
    }

    public function planifiees()
    {
        return view('coordinateur.courses.planifiees');
    }
}
