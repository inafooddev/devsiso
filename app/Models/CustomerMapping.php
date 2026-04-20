<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerMapping extends Model
{
    use HasFactory;

    protected $table = 'customer_mappings';
    protected $primaryKey = 'id';
    public $incrementing = true;

    // Kolom yang boleh diisi massal
    protected $fillable = [
        'distributor_code',
        'customer_code_prc',
        'customer_code_dist',
        'customer_name',
    ];

    // Laravel akan otomatis handle created_at & updated_at
    public $timestamps = true;

    // Jika kamu ingin memastikan waktu otomatis dari DB tetap digunakan
    protected $attributes = [
        'created_at' => null,
        'updated_at' => null,
    ];
}
