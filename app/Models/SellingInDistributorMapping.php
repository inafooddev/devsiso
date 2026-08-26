<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellingInDistributorMapping extends Model
{
    use HasFactory;

    protected $table = 'selling_in_distributor_mappings';

    protected $fillable = [
        'divisi',
        'wilayah',
        'kode_distributor',
        'distributor',
        'distributor_code'
    ];

    /**
     * Relasi ke tabel master_distributors
     */
    public function masterDistributor()
    {
        return $this->belongsTo(MasterDistributor::class, 'distributor_code', 'distributor_code');
    }
}
