<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TargetPerDepo extends Model
{
    protected $table = 'target_per_depo';

    protected $fillable = [
        'bulan',
        'cabang',
        'reg_fest',
        'target',
        'region',
        'area',
    ];
}
