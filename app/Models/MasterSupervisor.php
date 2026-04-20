<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterSupervisor extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'master_supervisors';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'supervisor_code';

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
        'supervisor_code',
        'supervisor_name',
        'description',
        'area_code',
    ];

    /**
     * Get the area that the supervisor belongs to.
     */
    public function area()
    {
        return $this->belongsTo(MasterArea::class, 'area_code', 'area_code');
    }
}
