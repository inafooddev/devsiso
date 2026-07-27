<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Route;
use App\Helpers\ActivityLogger;
use App\Models\Menu;
use Illuminate\Support\Facades\Cache;

class LogMenuAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Hanya log jika user sudah login, method GET, dan request berhasil
        if (auth()->check() && $request->isMethod('GET') && $response->isSuccessful()) {
            
            // Abaikan request dari Livewire update dan request AJAX
            if (!$request->hasHeader('X-Livewire') && !$request->ajax()) {
                
                $routeName = Route::currentRouteName();
                
                if ($routeName) {
                    // Cek nama menu berdasarkan nama route (Cache 1 jam untuk performa)
                    $menuName = Cache::remember('menu_name_' . $routeName, 3600, function () use ($routeName) {
                        $menu = Menu::where('route', $routeName)->first();
                        return $menu ? $menu->name : null;
                    });
                    
                    if ($menuName) {
                        $path = $request->path();
                        ActivityLogger::log('Access Menu', "Mengakses halaman: {$menuName} (/{$path})");
                    }
                }
            }
        }

        return $response;
    }
}
