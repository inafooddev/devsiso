<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TargetSpvVtkp extends Model
{
    use HasFactory;

    protected $table = 'target_spv_vtkps';

    protected $fillable = [
        'bulan',
        'cabang',
        'produk_grup',
        'target',
    ];
}
