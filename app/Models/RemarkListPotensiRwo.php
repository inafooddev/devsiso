<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RemarkListPotensiRwo extends Model
{
    protected $table = 'remark_list_potensi_rwo';

    protected $fillable = [
        'kuartal',
        'distributor_code',
        'customer_code',
        'remark'
    ];
}
