<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductMapping extends Model
{
    use HasFactory;

    protected $table = 'product_mappings';

    protected $fillable = [
        'distributor_code',
        'product_code_dist',
        'product_name_dist',
        'product_code_prc',
    ];

    /**
     * Get the master distributor that owns the mapping.
     */
    public function masterDistributor()
    {
        return $this->belongsTo(MasterDistributor::class, 'distributor_code', 'distributor_code');
    }
}
