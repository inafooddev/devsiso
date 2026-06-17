<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtractorTempResult extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'extracted_data' => 'array',
    ];
}
