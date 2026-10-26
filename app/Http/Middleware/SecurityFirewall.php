<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\BlockedIp;
use App\Models\IpWhitelist;
use App\Models\SecurityIncident;
use App\Services\Security\ThreatDetector;
use Illuminate\Support\Facades\Cache;

class SecurityFirewall
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $this->resolveClientIp($request);

        // 1. Cek apakah IP sedang aktif diblokir (Cache-First O(1))
        if (BlockedIp::isIpBlocked($ip)) {
            abort(403, "Akses dari alamat IP Anda ({$ip}) diblokir oleh sistem keamanan aplikasi.");
        }

        // 2. Cek apakah IP masuk dalam Whitelist (IP Aman / Kantor / Local)
        if (IpWhitelist::isIpWhitelisted($ip)) {
            return $next($request);
        }

        // 3. Analisis Pola Serangan / Threat Detection
        $threat = ThreatDetector::analyze($request);

        if ($threat !== null) {
            $autoBanEnabled = Cache::get('security:auto_ban_enabled', true);
            $shouldBan = $autoBanEnabled && ($threat['auto_ban'] ?? false);

            // Rekam insiden ke database
            try {
                SecurityIncident::create([
                    'ip_address' => $ip,
                    'threat_type' => $threat['threat_type'],
                    'severity' => $threat['severity'],
                    'method' => $request->method(),
                    'url' => substr($request->fullUrl(), 0, 1000),
                    'payload' => substr($threat['payload'] ?? '', 0, 1000),
                    'user_agent' => substr($request->header('User-Agent', ''), 0, 500),
                    'action_taken' => $shouldBan ? 'auto_banned' : 'blocked',
                ]);
            } catch (\Throwable $e) {
                // Fail-safe: Jangan biarkan error logging menghentikan proteksi
            }

            // Jika terdeteksi ancaman kritis dan Auto-Ban aktif, masukkan ke blacklist 24 jam
            if ($shouldBan) {
                BlockedIp::blockIp(
                    $ip,
                    'Auto-Ban: ' . $threat['label'],
                    $threat['threat_type'],
                    null,
                    now()->addHours(24),
                    true
                );
            }

            abort(403, "Akses Ditolak: Terdeteksi aktivitas berbahaya ({$threat['label']}).");
        }

        return $next($request);
    }

    /**
     * Dapatkan IP asli pengunjung dengan membaca header Reverse Proxy (Nginx / Cloudflare).
     */
    protected function resolveClientIp(Request $request): string
    {
        // 1. Cloudflare CDN Header
        if ($cfIp = $request->header('CF-Connecting-IP')) {
            return trim($cfIp);
        }

        // 2. Standard X-Forwarded-For (Nginx proxy)
        if ($forwarded = $request->header('X-Forwarded-For')) {
            $parts = explode(',', $forwarded);
            return trim($parts[0]);
        }

        // 3. Fallback ke IP bawaan Laravel
        return $request->ip() ?? '127.0.0.1';
    }
}
