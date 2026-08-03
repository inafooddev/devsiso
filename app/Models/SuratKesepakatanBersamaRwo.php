<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratKesepakatanBersamaRwo extends Model
{
    protected $table = 'surat_kesepakatan_bersama_rwo';

    protected $fillable = [
        'kuartal',
        'distributor_code',
        'customer_code',
        'foto_skb',
        'is_approved',
        'reason',
        'ho_is_valid',
        'ho_notes',
    ];
}
