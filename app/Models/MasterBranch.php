<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterBranch extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'master_branches';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'branch_code';

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
        'branch_code',
        'branch_name',
        'supervisor_code',
    ];

    /**
     * Get the supervisor that the branch belongs to.
     */
    public function supervisor()
    {
        return $this->belongsTo(MasterSupervisor::class, 'supervisor_code', 'supervisor_code');
    }
}
