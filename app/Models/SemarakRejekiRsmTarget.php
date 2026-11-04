<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SemarakRejekiRsmTarget extends Model
{
    use HasFactory;

    protected $table = 'semarak_rejeki_rsm_targets';

    protected $fillable = [
        'tahun',
        'region_code',
        'region_name',
        'rsm_db_pattern',
        'nomor_proposal',
        'max_ly',
        'target_l1_monthly',
        'growth_l1_pct',
        'reward_rsm_l1',
        'target_l2_monthly',
        'growth_l2_pct',
        'reward_rsm_l2',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'max_ly' => 'integer',
        'target_l1_monthly' => 'integer',
        'growth_l1_pct' => 'decimal:2',
        'reward_rsm_l1' => 'decimal:2',
        'target_l2_monthly' => 'integer',
        'growth_l2_pct' => 'decimal:2',
        'reward_rsm_l2' => 'decimal:2',
        'urutan' => 'integer',
        'is_active' => 'boolean',
    ];

    public function asmTargets()
    {
        return $this->hasMany(SemarakRejekiAsmTarget::class, 'rsm_target_id')->orderBy('urutan');
    }
}
