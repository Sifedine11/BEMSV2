<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Usage route :
     *   ->middleware('role:telephoniste')
     *   ->middleware('role:admin,telephoniste')
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = Auth::user();

        if (!$user) {
            // Laisse 'auth' rediriger vers /login
            abort(401, 'Non authentifié');
        }

        $userRole = $user->role;

        if (empty($roles)) {
            return $next($request); // pas de filtre si rien passé
        }

        if (!in_array($userRole, $roles, true)) {
            abort(403, 'Accès refusé');
        }

        return $next($request);
    }
}
