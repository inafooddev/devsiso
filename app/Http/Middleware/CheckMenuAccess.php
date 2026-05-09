<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Route;

class CheckMenuAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Allow if user is not authenticated (though this middleware is usually applied after auth)
        if (!$user) {
            return $next($request);
        }

        // Get the current route name
        $currentRouteName = Route::currentRouteName();

        // If route has no name, we cannot check by name, allow it or handle differently
        if (!$currentRouteName) {
            return $next($request);
        }

        // Get all allowed routes for the user
        $allowedRoutes = $user->menus()->whereNotNull('route')->pluck('route')->toArray();

        // For dashboard/home, usually we want to bypass, but let's strictly check if requested.
        // Wait, if we enforce this strictly, any route not in the database will 403.
        // The user said: "Jika user akses URL tanpa izin → 403", and "Jangan ubah route lama, hanya beri contoh penggunaan middleware di beberapa route"
        // So they will only apply it to specific routes anyway.
        if (in_array($currentRouteName, $allowedRoutes)) {
            return $next($request);
        }

        // If not found in user's allowed menus, return 403
        abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    }
}
