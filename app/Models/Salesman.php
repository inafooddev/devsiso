<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salesman extends Model
{
    use HasFactory;

    protected $table = 'salesmans';
    protected $primaryKey = 'salesman_code';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'salesman_code',
        'distributor_code',
        'salesman_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the master distributor that this salesman belongs to.
     */
    public function masterDistributor()
    {
        return $this->belongsTo(MasterDistributor::class, 'distributor_code', 'distributor_code');
    }
}
