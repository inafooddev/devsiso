<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityIncident extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'threat_type',
        'severity',
        'method',
        'url',
        'payload',
        'user_agent',
        'action_taken',
    ];

    /**
     * Helper badge variant untuk styling DaisyUI.
     */
    public function getSeverityBadgeVariantAttribute(): string
    {
        return match (strtolower($this->severity ?? 'low')) {
            'critical' => 'error',
            'high' => 'warning',
            'medium' => 'info',
            default => 'neutral',
        };
    }

    /**
     * Label ramah manusia untuk jenis ancaman.
     */
    public function getThreatTypeLabelAttribute(): string
    {
        return match ($this->threat_type) {
            'sensitive_probe' => 'Pemeriksaan File Sensitif (.env/.git)',
            'brute_force' => 'Percobaan Brute-Force Login',
            'malicious_bot' => 'Pemindai Otomatis / Bad Bot',
            'sql_injection_attempt' => 'Pola Injeksi SQL (SQLi)',
            'xss_attempt' => 'Pola Script Injeksi (XSS)',
            default => ucfirst(str_replace('_', ' ', $this->threat_type ?? 'Anomali')),
        };
    }
}
