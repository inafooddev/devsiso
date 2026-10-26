<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use App\Models\BlockedIp;
use App\Models\IpWhitelist;
use App\Models\SecurityIncident;
use Illuminate\Support\Facades\Cache;

class LogFailedLoginAttempt
{
    /**
     * Handle the event.
     */
    public function handle(Failed $event): void
    {
        $request = request();
        if (! $request) {
            return;
        }

        $ip = $request->header('CF-Connecting-IP')
            ?? ($request->header('X-Forwarded-For') ? trim(explode(',', $request->header('X-Forwarded-For'))[0]) : null)
            ?? $request->ip()
            ?? '127.0.0.1';

        // Abaikan jika IP masuk whitelist
        if (IpWhitelist::isIpWhitelisted($ip)) {
            return;
        }

        $cacheKey = "security:failed_login_count:{$ip}";
        $attempts = Cache::increment($cacheKey);

        // Set TTL 5 menit pada percobaan pertama
        if ($attempts === 1) {
            Cache::put($cacheKey, 1, now()->addMinutes(5));
        }

        $account = $event->credentials['email'] ?? $event->credentials['username'] ?? 'unknown';

        // Deteksi Brute Force jika percobaan gagal >= 5 kali
        if ($attempts >= 5) {
            $isCritical = $attempts >= 10;
            $autoBanEnabled = Cache::get('security:auto_ban_enabled', true);

            try {
                SecurityIncident::create([
                    'ip_address' => $ip,
                    'threat_type' => 'brute_force',
                    'severity' => $isCritical ? 'critical' : 'high',
                    'method' => 'POST',
                    'url' => substr($request->fullUrl(), 0, 1000),
                    'payload' => "Gagal login ke-{$attempts} dalam 5 menit. Target akun: {$account}",
                    'user_agent' => substr($request->header('User-Agent', ''), 0, 500),
                    'action_taken' => ($isCritical && $autoBanEnabled) ? 'auto_banned' : 'logged',
                ]);
            } catch (\Throwable $e) {
                // Fail-safe
            }

            // Jika mencapai 10x gagal dan auto ban aktif, ban selama 1 jam
            if ($isCritical && $autoBanEnabled) {
                BlockedIp::blockIp(
                    $ip,
                    "Auto-Ban: Terlalu banyak kegagalan login ({$attempts}x percobaan)",
                    'brute_force',
                    null,
                    now()->addHour(),
                    true
                );
            }
        }
    }
}
