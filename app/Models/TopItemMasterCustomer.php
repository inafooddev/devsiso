<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopItemMasterCustomer extends Model
{
    use HasFactory;

    protected $table = 'top_item_master_customer';

    protected $fillable = [
        'distributor_code',
        'uniq_code',
        'custno',
        'customer_name',
        'address'
    ];
}
