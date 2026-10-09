<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratKesepakatanBersamaRwo extends Model
{
    protected $table = 'surat_kesepakatan_bersama_rwo';

    protected $fillable = [
        'kuartal',
        'tahun',
        'distributor_code',
        'customer_code',
        'foto_skb',
        'is_approved',
        'reason',
        'ho_is_valid',
        'ho_notes',
        'manager_is_approved',
        'manager_notes',
        'manager_approved_by',
        'manager_approved_at',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'ho_is_valid' => 'boolean',
        'manager_is_approved' => 'boolean',
        'manager_approved_at' => 'datetime',
    ];
}
