<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopItemAchievement extends Model
{
    use HasFactory;

    protected $table = 'top_item_achievement';

    protected $fillable = [
        'period',
        'distributor_code',
        'uniq_code',
        'pcode_prc',
        'qty',
        'value'
    ];
}
