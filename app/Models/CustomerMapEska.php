<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerMapEska extends Model
{
    use HasFactory;

    /**
     * Menentukan nama tabel secara eksplisit.
     */
    protected $table = 'customer_map_eska';

    /**
     * Kolom yang dapat diisi secara massal (Mass Assignment).
     */
    protected $fillable = [
        'bln',
        'distid',
        'branch_dist',
        'custno_dist',
        'branch',
        'custno',
    ];

    /**
     * Casting tipe data kolom tertentu.
     */
    protected $casts = [
        'bln' => 'date',
    ];
}