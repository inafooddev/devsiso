<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SemarakRejekiSpvTarget extends Model
{
    use HasFactory;

    protected $table = 'semarak_rejeki_spv_targets';

    protected $fillable = [
        'tahun',
        'supervisor_code',
        'siso_code',
        'kategori_wilayah',
        'tier_id',
        'kode_paket_awal',
        'target_karton_3bln',
        'target_m1_okt',
        'target_m2_nov',
        'target_m3_des',
        'is_active',
        'catatan',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'target_karton_3bln' => 'decimal:2',
        'target_m1_okt' => 'decimal:2',
        'target_m2_nov' => 'decimal:2',
        'target_m3_des' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function tier()
    {
        return $this->belongsTo(SemarakRejekiRewardTier::class, 'tier_id');
    }
}
