<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigSalesInvoiceDistributor extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'config_sales_invoice_distributor';

    /**
     * Kolom yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'distributor_code',
        'config_name',
        'config'
    ];

    protected $casts = [
        'config' => 'array', // biar otomatis jadi array/json
    ];

    /**
     * Laravel secara otomatis mengelola created_at dan updated_at,
     * jadi kita tidak perlu menambahkannya di $fillable.
     * Jika tabel Anda tidak memiliki kolom timestamp, set:
     * public $timestamps = false;
     */
}