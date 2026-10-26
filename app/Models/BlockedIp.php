<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class BlockedIp extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'reason',
        'threat_type',
        'blocked_by',
        'is_auto_ban',
        'expires_at',
    ];

    protected $casts = [
        'is_auto_ban' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

    /**
     * Cek apakah IP sedang aktif diblokir (Cache-First).
     */
    public static function isIpBlocked(string $ip): bool
    {
        $cacheKey = "security:blocked_ip:{$ip}";

        return Cache::remember($cacheKey, 60, function () use ($ip) {
            $record = self::where('ip_address', $ip)->first();
            if (! $record) {
                return false;
            }

            // Jika ada masa berlaku dan sudah expired, hapus dari blacklist
            if ($record->expires_at && $record->expires_at->isPast()) {
                $record->delete();
                return false;
            }

            return true;
        });
    }

    /**
     * Blokir IP dan sinkronkan ke cache secara langsung.
     */
    public static function blockIp(string $ip, ?string $reason = null, ?string $threatType = null, ?int $blockedBy = null, ?\DateTimeInterface $expiresAt = null, bool $isAutoBan = false): self
    {
        // Anti-lockout: jangan pernah memblokir localhost agar pengembang lokal tidak terkunci
        if (in_array($ip, ['127.0.0.1', '::1', 'localhost'])) {
            return new self(['ip_address' => $ip]);
        }

        $blocked = self::updateOrCreate(
            ['ip_address' => $ip],
            [
                'reason' => $reason ?? 'Aktivitas mencurigakan',
                'threat_type' => $threatType,
                'blocked_by' => $blockedBy,
                'is_auto_ban' => $isAutoBan,
                'expires_at' => $expiresAt,
            ]
        );

        // Update Cache langsung
        $cacheKey = "security:blocked_ip:{$ip}";
        if ($expiresAt) {
            Cache::put($cacheKey, true, $expiresAt);
        } else {
            Cache::forever($cacheKey, true);
        }

        return $blocked;
    }

    /**
     * Buka blokir IP dan bersihkan cache.
     */
    public static function unblockIp(string $ip): bool
    {
        $cacheKey = "security:blocked_ip:{$ip}";
        Cache::forget($cacheKey);

        return (bool) self::where('ip_address', $ip)->delete();
    }
}
