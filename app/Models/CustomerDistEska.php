<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerDistEska extends Model
{
    use HasFactory;

    /**
     * Menentukan nama tabel secara eksplisit karena nama tabel
     * tidak mengikuti konvensi jamak standar Laravel (customer_dist_eskas).
     */
    protected $table = 'customer_dist_eska';

    /**
     * Kolom yang dapat diisi secara massal (Mass Assignment).
     */
    protected $fillable = [
        'bln',
        'distid',
        'branch',
        'custno',
        'custname',
    ];

    /**
     * Casting tipe data kolom tertentu.
     */
    protected $casts = [
        'bln' => 'date',
    ];
}