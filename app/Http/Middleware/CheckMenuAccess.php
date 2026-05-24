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

        // Check access using the new matrix method
        if ($user->hasMenuAccess($currentRouteName, 'can_view')) {
            return $next($request);
        }

        // If not found in user's allowed menus, return 403
        abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    }
}
