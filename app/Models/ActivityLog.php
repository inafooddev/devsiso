<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_name',
        'action',
        'description',
        'ip_address',
        'user_agent',
    ];

    /**
     * Get the badge color variant for DaisyUI based on action name.
     */
    public function getBadgeVariantAttribute(): string
    {
        $actionLower = strtolower($this->action ?? '');
        if (str_contains($actionLower, 'create') || str_contains($actionLower, 'tambah') || str_contains($actionLower, 'store') || str_contains($actionLower, 'insert') || str_contains($actionLower, 'import')) {
            return 'success';
        } elseif (str_contains($actionLower, 'update') || str_contains($actionLower, 'edit') || str_contains($actionLower, 'ubah') || str_contains($actionLower, 'mapping')) {
            return 'info';
        } elseif (str_contains($actionLower, 'delete') || str_contains($actionLower, 'hapus') || str_contains($actionLower, 'destroy') || str_contains($actionLower, 'remove')) {
            return 'error';
        } elseif (str_contains($actionLower, 'export') || str_contains($actionLower, 'download')) {
            return 'secondary';
        } elseif (str_contains($actionLower, 'login') || str_contains($actionLower, 'auth')) {
            return 'primary';
        } elseif (str_contains($actionLower, 'logout')) {
            return 'warning';
        }
        return 'neutral';
    }

    /**
     * Get a human-readable device and browser label.
     */
    public function getDeviceLabelAttribute(): string
    {
        $ua = $this->user_agent ?? '';
        if (empty($ua)) return '-';

        $os = 'Desktop';
        if (str_contains($ua, 'Android')) {
            $os = 'Android';
        } elseif (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')) {
            $os = 'iOS';
        } elseif (str_contains($ua, 'Windows')) {
            $os = 'Windows';
        } elseif (str_contains($ua, 'Macintosh')) {
            $os = 'Mac';
        } elseif (str_contains($ua, 'Linux')) {
            $os = 'Linux';
        }

        $browser = '';
        if (str_contains($ua, 'Firefox')) {
            $browser = 'Firefox';
        } elseif (str_contains($ua, 'Edg')) {
            $browser = 'Edge';
        } elseif (str_contains($ua, 'Chrome') || str_contains($ua, 'CriOS')) {
            $browser = 'Chrome';
        } elseif (str_contains($ua, 'Safari')) {
            $browser = 'Safari';
        }

        return trim("$os $browser") ?: 'Unknown';
    }

    /**
     * Determine if access was from mobile.
     */
    public function getIsMobileAttribute(): bool
    {
        $ua = $this->user_agent ?? '';
        return str_contains($ua, 'Mobile') || str_contains($ua, 'Android') || str_contains($ua, 'iPhone');
    }
}
