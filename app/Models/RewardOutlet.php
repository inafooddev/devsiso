<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RewardOutlet extends Model
{
    use HasFactory;

    protected $table = 'reward_outlet';

    protected $fillable = [
        'region_code',
        'region_name',
        'area_code',
        'area_name',
        'branch_name',
        'eskalink_code',
        'customer_code',
        'customer_name',
        'alamat',
        'no_hp',
        'latitude',
        'longitude',
        'nama_pemilik_toko',
        'nama_ktp',
        'nik_ktp',
        'foto_ktp',
        'nama_bank',
        'no_rekening',
        'nama_pemilik_norek',
        'foto_toko',
        'foto_toko2',
        'foto_toko3',
        'keterangan',
        'is_valid',
    ];

    protected $casts = [
        'is_valid' => 'boolean',
    ];
}
