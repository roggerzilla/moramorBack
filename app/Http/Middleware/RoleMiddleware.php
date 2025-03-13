<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $role)
    {
        // Verifica si el usuario está autenticado
        if (!Auth::check()) {
            return response()->json(['message' => 'No autorizado'], 401);
        }

        // Verifica si el usuario tiene el rol requerido
        $user = Auth::user();
        if ($user->role !== $role) {
            return response()->json(['message' => 'No tienes permisos para realizar esta acción'], 403);
        }

        return $next($request);
    }
}