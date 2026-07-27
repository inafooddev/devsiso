<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankGaransiFollowUp extends Model
{
    use HasFactory;

    protected $table = 'bank_garansi_follow_ups';

    protected $fillable = [
        'bank_garansi_id',
        'user_id',
        'status_progress',
        'catatan',
        'attachment',
    ];

    public function bankGaransi()
    {
        return $this->belongsTo(BankGaransi::class, 'bank_garansi_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
