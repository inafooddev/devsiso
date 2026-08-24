<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterProdukLama extends Model
{
    use HasFactory;

    protected $table = 'master_produk_lama';
    
    protected $primaryKey = 'pcode_prc';
    
    public $incrementing = false;
    
    protected $keyType = 'string';
    
    public $timestamps = false; // Assuming no created_at/updated_at based on the column listing

    protected $fillable = [
        'pcode_prc',
        'nama_produk',
        'status_product',
        'uom1',
        'uom2',
        'uom3',
        'crttopcs',
        'crttopack',
        'packtopcs',
        'pricehrt',
        'produk_line',
        'brand',
        'divisi',
        'kategory',
        'subbrand',
        'topitem',
        'promo_group'
    ];
}
