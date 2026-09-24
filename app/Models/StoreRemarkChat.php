<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreRemarkChat extends Model
{
    protected $fillable = [
        'kuartal',
        'distributor_code',
        'customer_code',
        'user_id',
        'message',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
