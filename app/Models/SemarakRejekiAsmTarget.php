<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SemarakRejekiAsmTarget extends Model
{
    use HasFactory;

    protected $table = 'semarak_rejeki_asm_targets';

    protected $fillable = [
        'rsm_target_id',
        'tahun',
        'area_code',
        'area_name',
        'asm_db_name',
        'max_ly',
        'target_l1_monthly',
        'growth_l1_pct',
        'reward_asm_l1',
        'target_l2_monthly',
        'growth_l2_pct',
        'reward_asm_l2',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'rsm_target_id' => 'integer',
        'tahun' => 'integer',
        'max_ly' => 'integer',
        'target_l1_monthly' => 'integer',
        'growth_l1_pct' => 'decimal:2',
        'reward_asm_l1' => 'decimal:2',
        'target_l2_monthly' => 'integer',
        'growth_l2_pct' => 'decimal:2',
        'reward_asm_l2' => 'decimal:2',
        'urutan' => 'integer',
        'is_active' => 'boolean',
    ];

    public function rsmTarget()
    {
        return $this->belongsTo(SemarakRejekiRsmTarget::class, 'rsm_target_id');
    }
}
