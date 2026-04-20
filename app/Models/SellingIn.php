<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellingIn extends Model
{
    use HasFactory;

    protected $table = 'selling_in';

    protected $fillable = [
        'bulan', 'tahun', 'rsm', 'region', 'area', 'kd_spv', 'nama_spv',
        'cabang', 'kd_distributor', 'nama_distributor', 'nama_distributor_fix',
        'nama_produk', 'nama_produk_mapping', 'jenis', 'reg_fes', 'kategori',
        'top_item', 'brand', 'sub_brand', 'ktn_jual', 'pcs_jual', 'value_jual',
        'ktn_retur', 'pcs_retur', 'value_retur', 'ktn_net', 'pcs_net', 'value_net'
    ];

    protected $casts = [
        'bulan' => 'date',
        'tahun' => 'integer',
        'ktn_jual' => 'float',
        'pcs_jual' => 'float',
        'value_jual' => 'float',
        'ktn_retur' => 'float',
        'pcs_retur' => 'float',
        'value_retur' => 'float',
        'ktn_net' => 'float',
        'pcs_net' => 'float',
        'value_net' => 'float',
    ];
}