<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JksSalesman extends Model
{
    use HasFactory;

    protected $table = 'jks_salesmans';

    protected $fillable = [
        'bulan',
        'distributor_code',
        'salesman_code',
        'customer_code',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'h7',
        'w1',
        'w2',
        'w3',
        'w4',
        'update_by',
    ];
}
