<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeotagJob extends Model
{
    protected $fillable = ['original_filename', 'system_filename', 'status'];
}