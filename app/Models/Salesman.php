<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salesman extends Model
{
    use HasFactory;

    protected $table = 'salesmans';

    protected $fillable = [
        'salesman_code',
        'distributor_code',
        'salesman_name',
        'is_active',
        'join_date',
        'foto_ktp',
        'foto_npwp',
        'bank',
        'bank_name',
        'bank_no',
        'foto_bank',
        'foto_skb',
        'is_principle',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_principle' => 'boolean',
        'join_date' => 'date',
    ];

    /**
     * Get the master distributor that this salesman belongs to.
     */
    public function masterDistributor()
    {
        return $this->belongsTo(MasterDistributor::class, 'distributor_code', 'distributor_code');
    }
}
