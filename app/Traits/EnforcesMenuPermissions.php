<?php

namespace App\Traits;

trait EnforcesMenuPermissions
{
    /**
     * Authorize action (can_view, can_edit, can_import, can_export) for the current route.
     * Aborts with 403 if unauthorized.
     */
    public function authorizeAction($action = 'can_edit')
    {
        $routeName = property_exists($this, 'menuRoute') ? $this->menuRoute : null;
        
        if (!$routeName) {
            // Check if there is a menuRoute method instead of property (sometimes useful)
            if (method_exists($this, 'getMenuRoute')) {
                $routeName = $this->getMenuRoute();
            } else {
                abort(500, "Properti \$menuRoute tidak ditemukan pada komponen ini.");
            }
        }

        if (!auth()->user()->hasMenuAccess($routeName, $action)) {
            abort(403, "Anda tidak memiliki akses untuk melakukan aksi ini ({$action}).");
        }
    }
}
