<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesmanMapping extends Model
{
    use HasFactory;

    protected $table = 'salesman_mappings';

    protected $fillable = [
        'distributor_code',
        'salesman_code_dist',
        'salesman_name_dist',
        'salesman_code_prc',
    ];

    /**
     * Get the master distributor that owns the mapping.
     */
    public function masterDistributor()
    {
        return $this->belongsTo(MasterDistributor::class, 'distributor_code', 'distributor_code');
    }

    /**
     * Get the principal salesman associated with the mapping.
     */
    public function principalSalesman()
    {
        return $this->belongsTo(Salesman::class, 'salesman_code_prc', 'salesman_code');
    }
}
