<?php

namespace App\Helpers;

use App\Models\ActivityLog;

class ActivityLogger
{
    /**
     * Log an activity to the database.
     *
     * @param string $action
     * @param string|null $description
     */
    public static function log($action, $description = null)
    {
        $user = auth()->user();

        ActivityLog::create([
            'user_id' => $user ? $user->userid : null,
            'user_name' => $user ? $user->name : null,
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
