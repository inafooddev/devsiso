<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterArea extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'master_areas';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'area_code';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'area_code',
        'area_name',
        'region_code',
    ];

    /**
     * Get the region that owns the area.
     */
    public function region()
    {
        return $this->belongsTo(MasterRegion::class, 'region_code', 'region_code');
    }
}
