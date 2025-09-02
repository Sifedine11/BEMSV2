<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class ParametreController extends Controller
{

    public function edit(Request $request)
    {
        /** @var Utilisateur $user */
        $user = Auth::user();


        [$prefs, $persistable] = $this->getUserPreferences($user);

        return view('parametres.edit', [
            'user'        => $user,
            'prefs'       => $prefs,
            'persistable' => $persistable,
        ]);
    }


    public function index(Request $request)
    {
        return $this->edit($request);
    }

    public function update(Request $request)
    {
        /** @var Utilisateur $user */
        $user = Auth::user();

        $data = $request->validate([
            'password' => ['nullable','string','min:8','confirmed'], // password + password_confirmation
            'prefs'    => ['nullable','array'],
        ]);

        if (!empty($data['password'])) {
            $user->mot_de_passe = Hash::make($data['password']);
        }

        $incomingPrefs = (array)($data['prefs'] ?? []);
        if (Schema::hasColumn($user->getTable(), 'preferences')) {
            $current = $this->decodePrefs($user->preferences ?? null);
            $merged  = array_replace_recursive($current, $incomingPrefs);
            $user->preferences = json_encode($merged, JSON_UNESCAPED_UNICODE);
        } else {
            $current = (array) session('prefs', []);
            $merged  = array_replace_recursive($current, $incomingPrefs);
            session(['prefs' => $merged]);
        }

        $user->save();

        return back()->with('status', 'Paramètres enregistrés.');
    }


    protected function getUserPreferences(Utilisateur $user): array
    {
        if (Schema::hasColumn($user->getTable(), 'preferences')) {
            return [$this->decodePrefs($user->preferences ?? null), true];
        }
        return [(array) session('prefs', []), false];
    }

    protected function decodePrefs($raw): array
    {
        if (is_array($raw)) return $raw;
        if (is_string($raw)) {
            $arr = json_decode($raw, true);
            return is_array($arr) ? $arr : [];
        }
        return [];
    }
}
