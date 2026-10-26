<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class IpWhitelist extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'label',
        'added_by',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    /**
     * Cek apakah IP masuk dalam daftar Whitelist (Cache-First).
     */
    public static function isIpWhitelisted(string $ip): bool
    {
        // Local loopback selalu aman
        if (in_array($ip, ['127.0.0.1', '::1', 'localhost'])) {
            return true;
        }

        $cacheKey = "security:whitelist_ip:{$ip}";

        return Cache::remember($cacheKey, 300, function () use ($ip) {
            return self::where('ip_address', $ip)->exists();
        });
    }

    /**
     * Tambahkan IP ke whitelist dan update cache.
     */
    public static function addIp(string $ip, string $label, ?int $addedBy = null): self
    {
        $whitelist = self::updateOrCreate(
            ['ip_address' => $ip],
            [
                'label' => $label,
                'added_by' => $addedBy,
            ]
        );

        Cache::put("security:whitelist_ip:{$ip}", true, 300);

        return $whitelist;
    }

    /**
     * Hapus IP dari whitelist dan bersihkan cache.
     */
    public static function removeIp(string $ip): bool
    {
        Cache::forget("security:whitelist_ip:{$ip}");

        return (bool) self::where('ip_address', $ip)->delete();
    }
}
