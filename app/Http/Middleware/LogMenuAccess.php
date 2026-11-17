<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use App\Helpers\ActivityLogger;
use App\Models\Menu;
use Illuminate\Support\Facades\Cache;

class LogMenuAccess
{
    /**
     * Pemetaan nama menu untuk rute-rute mobile.
     */
    protected static array $mobileMenuMap = [
        'mobile.home' => 'Mobile: Dashboard Portal',
        'mobile.profile' => 'Mobile: Profil Pengguna',
        'mobile.jks-salesman.index' => 'Mobile: JKS Salesman',
        'mobile.skb-rwo.index' => 'Mobile: Reward Outlet SKB',
        'mobile.perbaikan.tikor-tim-elite.index' => 'Mobile: Perbaikan Tikor Tim Elite',
        'mobile.insentif.index' => 'Mobile: Insentif Sales (Monitoring)',
        'mobile.audit.index' => 'Mobile: Audit Toko',
        'mobile.portal' => 'Mobile: Portal',
        'mobile.rwo.inertia' => 'Mobile: RWO Update',
        'mobile.call-plan.index' => 'Mobile: Call Plan',
        'mobile.perbaikan.tikor.index' => 'Mobile: Perbaikan Tikor (Sales)',
        'mobile.app.monitoring-device.index' => 'Mobile: Monitoring Device',
    ];

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
            $isInertia = $request->hasHeader('X-Inertia');
            $isPartialInertia = $request->hasHeader('X-Inertia-Partial-Data');

            // Proses jika bukan Livewire, bukan partial reload Inertia, dan merupakan browser load atau Inertia visit
            if (!$request->hasHeader('X-Livewire') && !$isPartialInertia && (!$request->ajax() || $isInertia)) {
                
                $routeName = $request->route()?->getName() ?: Route::currentRouteName();
                
                if ($routeName) {
                    // 1. Cek dari database menus (Cache 1 jam untuk performa)
                    $menuName = Cache::remember('menu_name_' . $routeName, 3600, function () use ($routeName) {
                        $menu = Menu::where('route', $routeName)->first();
                        return $menu ? $menu->name : null;
                    });

                    // 2. Fallback untuk rute mobile jika tidak ada di tabel menus
                    if (!$menuName && (isset(self::$mobileMenuMap[$routeName]) || str_starts_with($routeName, 'mobile.'))) {
                        $menuName = self::$mobileMenuMap[$routeName] ?? ('Mobile: ' . Str::headline(str_replace(['mobile.', '.index'], '', $routeName)));
                    }
                    
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
