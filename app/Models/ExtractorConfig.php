<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtractorConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'keywords',
        'header_row',
        'columns',
    ];

    protected $casts = [
        'keywords' => 'array',
        'columns' => 'array',
    ];
}
