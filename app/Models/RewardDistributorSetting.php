<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardDistributorSetting extends Model
{
    protected $fillable = ['distributor_code', 'p1_months', 'p2_months'];

    protected $casts = [
        'p1_months' => 'array',
        'p2_months' => 'array',
    ];
}
