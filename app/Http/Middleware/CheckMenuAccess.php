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
        $currentRouteName = $request->route()?->getName() ?: Route::currentRouteName();

        // If route has no name, we cannot check by name, allow it or handle differently
        if (!$currentRouteName) {
            return $next($request);
        }

        // Route aliases for sub-pages or tabs that share parent menu access
        $routeAliases = [
            'others.perbaikantikor.elite' => 'others.perbaikantikor',
            'others.validasi-geotag.summary' => 'others.validasi-geotag',
        ];

        $checkRoute = $routeAliases[$currentRouteName] ?? $currentRouteName;

        // Check access using the new matrix method
        if ($user->hasMenuAccess($checkRoute, 'can_view')) {
            return $next($request);
        }

        // If not found in user's allowed menus, return 403
        abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    }
}
