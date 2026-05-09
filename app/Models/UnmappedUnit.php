<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnmappedUnit extends Model
{
    use HasFactory;

    protected $fillable = ['distributor_code', 'raw_unit'];

    public function setDistributorCodeAttribute($value)
    {
        $this->attributes['distributor_code'] = strtoupper(trim($value));
    }

    public function setRawUnitAttribute($value)
    {
        $this->attributes['raw_unit'] = strtoupper(trim($value));
    }
}
