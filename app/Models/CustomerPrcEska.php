<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerPrcEska extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'customer_prc_eska';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'bln',
        'custno',
        'custname',
        'custadd1',
        'ccity',
        'cterm',
        'typeout',
        'grupout',
        'gharga',
        'flagpay',
        'flagout',
        'kodecabang',
        'la',
        'lg',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'bln' => 'date',
        'la' => 'decimal:8',
        'lg' => 'decimal:8',
    ];
}