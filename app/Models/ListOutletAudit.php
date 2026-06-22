<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListOutletAudit extends Model
{
    use HasFactory;

    protected $table = 'list_outlet_audit';

    protected $fillable = [
        'distributor_code',
        'customer_code',
        'customer_name',
        'customer_address',
        'latitude',
        'longitude',
    ];
}
