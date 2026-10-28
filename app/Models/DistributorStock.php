<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistributorStock extends Model
{
    use HasFactory;

    protected $table = 'distributor_stocks';

    protected $fillable = [
        'bulan',
        'tahun',
        'stock_date',
        'distributor_code',
        'product_id',
        'stock_awal_ktn',
        'sellin_ktn',
        'sellout_ktn',
        'stock_teoritis_ktn',
        'stock_fisik_upload_ktn',
        'stock_fisik_upload_pcs',
        'selisih_ktn',
        'status_anomali',
        'total_value',
        'file_name',
        'uploaded_by',
    ];

    protected $casts = [
        'bulan' => 'integer',
        'tahun' => 'integer',
        'stock_date' => 'date',
        'stock_awal_ktn' => 'decimal:2',
        'sellin_ktn' => 'decimal:2',
        'sellout_ktn' => 'decimal:2',
        'stock_teoritis_ktn' => 'decimal:2',
        'stock_fisik_upload_ktn' => 'decimal:2',
        'stock_fisik_upload_pcs' => 'decimal:2',
        'selisih_ktn' => 'decimal:2',
        'total_value' => 'decimal:2',
    ];

    /**
     * Relasi ke Master Distributor
     */
    public function distributor(): BelongsTo
    {
        return $this->belongsTo(MasterDistributor::class, 'distributor_code', 'distributor_code');
    }

    /**
     * Relasi ke Master Produk
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductMaster::class, 'product_id', 'product_id');
    }

    /**
     * Relasi ke User pengunggah
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by', 'id');
    }
}
