<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SemarakRejekiRewardTier extends Model
{
    use HasFactory;

    protected $table = 'semarak_rejeki_reward_tiers';

    protected $fillable = [
        'kategori_wilayah',
        'kode_paket',
        'target_karton',
        'harga_per_karton',
        'nilai_transaksi',
        'nama_hadiah',
        'nilai_hadiah',
        'cost_ratio',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'target_karton' => 'integer',
        'harga_per_karton' => 'decimal:2',
        'nilai_transaksi' => 'decimal:2',
        'nilai_hadiah' => 'decimal:2',
        'cost_ratio' => 'decimal:2',
        'urutan' => 'integer',
        'is_active' => 'boolean',
    ];

    public function targets()
    {
        return $this->hasMany(SemarakRejekiSpvTarget::class, 'tier_id');
    }

    public function getRewardCategoryAttribute(): string
    {
        return self::detectCategory($this->nama_hadiah);
    }

    public static function detectCategory(?string $name): string
    {
        $upper = strtoupper(trim((string) $name));

        if (str_contains($upper, 'MOTOR') || str_contains($upper, 'YAMAHA') || str_contains($upper, 'HONDA') || str_contains($upper, 'KAWASAKI') || str_contains($upper, 'UWINFLY')) {
            return 'motor';
        }

        if (str_contains($upper, 'LAPTOP') || str_contains($upper, 'IDEAPAD') || str_contains($upper, 'MACBOOK') || str_contains($upper, 'INTEL') || str_contains($upper, 'CORE ULTRA') || str_contains($upper, 'SSD') || str_contains($upper, 'HP 14-') || str_contains($upper, 'HP 240R')) {
            return 'laptop';
        }

        if (str_contains($upper, 'TV') || str_contains($upper, 'CRYSTAL UHD') || str_contains($upper, 'THE FRAME')) {
            return 'tv';
        }

        if (str_contains($upper, 'TABLET') || str_contains($upper, 'GALAXYTAB') || str_contains($upper, 'GALAXY TAB') || str_contains($upper, 'IPAD')) {
            return 'tablet';
        }

        if (str_contains($upper, 'WATCH')) {
            return 'watch';
        }

        if (str_contains($upper, 'BUDS') || str_contains($upper, 'HEADPHONE') || str_contains($upper, 'AIRPODS') || str_contains($upper, 'EARPHONE')) {
            return 'audio';
        }

        if (str_contains($upper, 'HP ') || str_contains($upper, 'IPHONE') || str_contains($upper, 'REDMI') || str_contains($upper, 'GALAXY A') || str_contains($upper, 'XIAOMI')) {
            return 'phone';
        }

        return 'gift';
    }
}
