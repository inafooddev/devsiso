<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListPotensiRwo extends Model
{
    protected $table = 'list_potensi_rwo';

    protected $fillable = [
        'kuartal',
        'distributor_code',
        'customer_code',
        'customer_name',
        'alamat',
        'total_target',
    ];
}
