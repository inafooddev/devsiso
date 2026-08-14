<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TargetSeValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'bulan',
        'distributor_code',
        'salesman_code',
        'target',
    ];
}
