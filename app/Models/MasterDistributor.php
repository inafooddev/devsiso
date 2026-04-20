<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterDistributor extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'master_distributors';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'distributor_code';

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
        'distributor_code',
        'distributor_name',
        'resign_date',
        'join_date',
        'latitude',
        'longitude',
        'is_active',
        'region_code',
        'region_name',
        'area_code',
        'area_name',
        'supervisor_code',
        'supervisor_name',
        'branch_code',
        'branch_name',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'resign_date' => 'date',
        'join_date' => 'date',
    ];

    /**
     * NOTE: Karena tabel ini bersifat denormalisasi (summary),
     * Anda perlu membuat Model Observers untuk tabel-tabel master lainnya
     * (MasterRegion, MasterArea, MasterSupervisor, MasterBranch).
     *
     * Saat data di tabel master tersebut di-update (misalnya, region_name berubah),
     * observer akan secara otomatis memperbarui semua baris yang relevan di tabel master_distributors ini.
     */

    /**
     * Get the region that owns the distributor.
     */
    public function region()
    {
        return $this->belongsTo(MasterRegion::class, 'region_code', 'region_code');
    }

    /**
     * Get the area that owns the distributor.
     */
    public function area()
    {
        return $this->belongsTo(MasterArea::class, 'area_code', 'area_code');
    }

    /**
     * Get the supervisor that owns the distributor.
     */
    public function supervisor()
    {
        return $this->belongsTo(MasterSupervisor::class, 'supervisor_code', 'supervisor_code');
    }

    /**
     * Get the branch that owns the distributor.
     */
    public function branch()
    {
        return $this->belongsTo(MasterBranch::class, 'branch_code', 'branch_code');
    }

}
