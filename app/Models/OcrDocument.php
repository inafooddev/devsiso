<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OcrDocument extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'file_name', 'distributor_code', 'tanggal', 
        'raw_text', 'nominal_extracted', 'status'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }
}
