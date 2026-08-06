<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TargetPerSeUntukEska extends Model
{
    use HasFactory;

    /**
     * Nama tabel eksplisit.
     */
    protected $table = 'target_per_se_untuk_eska';

    /**
     * Kolom yang dapat diisi secara massal (Mass Assignment).
     */
    protected $fillable = [
        'tahun',
        'bulan',
        'region',
        'branch',
        'sellingpoint',
        'salesman',
        'outlet',
        'value',
    ];

    /**
     * Casting tipe data.
     */
    protected $casts = [
        'value' => 'decimal:2',
    ];
}
