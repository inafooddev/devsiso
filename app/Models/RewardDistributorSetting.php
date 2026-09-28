<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardDistributorSetting extends Model
{
    protected $fillable = ['distributor_code', 'is_participating', 'p1_months', 'p2_months'];

    protected $casts = [
        'is_participating' => 'boolean',
        'p1_months' => 'array',
        'p2_months' => 'array',
    ];
}
