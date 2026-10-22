<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ValidasiGeotag extends Model
{
    use HasFactory;

    protected $table = 'validasi_geotag';

    protected $fillable = [
        'kodecabang',
        'custno',
        'la',
        'lg',
        'status',
        'catatan',
        'validated_by',
        'validator_name',
        'validated_at',
    ];

    protected $casts = [
        'la' => 'float',
        'lg' => 'float',
        'validated_at' => 'datetime',
    ];

    /**
     * Relasi ke User yang memvalidasi.
     */
    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Relasi ke distributor implementasi eskalink.
     */
    public function distributorImplementasiEskalink()
    {
        return $this->belongsTo(DistributorImplementasiEskalink::class, 'kodecabang', 'eskalink_code');
    }

    /**
     * Scope query untuk membatasi akses berdasarkan hierarki wilayah user login.
     */
    public function scopeForUserAccess($query, $user = null)
    {
        $user = $user ?: auth()->user();
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        $accessLevel = method_exists($user, 'getAccessLevel') ? $user->getAccessLevel() : 'nasional';

        if ($accessLevel === 'nasional') {
            return $query;
        }

        if ($accessLevel === 'supervisor') {
            $allowedDistributors = DB::table('vw_distributor_hierarchy')
                ->where('supervisor_code', $user->supervisor_code)
                ->pluck('distributor_code')
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            $eskaCodes = [];
            if (!empty($allowedDistributors)) {
                $eskaCodes = DB::table('distributor_implementasi_eskalink')
                    ->whereIn('distributor_code', $allowedDistributors)
                    ->orWhereIn('eskalink_code', $allowedDistributors)
                    ->pluck('eskalink_code')
                    ->filter()
                    ->unique()
                    ->values()
                    ->toArray();
            }

            $allowedCodes = array_values(array_unique(array_merge($allowedDistributors, $eskaCodes)));
            if (!empty($allowedCodes)) {
                return $query->whereIn('validasi_geotag.kodecabang', $allowedCodes);
            }

            return $query->whereRaw('1 = 0');
        }

        if ($accessLevel === 'area') {
            $allowedAreas = (array) ($user->area_code ?? []);
            if (empty($allowedAreas)) {
                return $query->whereRaw('1 = 0');
            }

            $eskaCodes = DB::table('master_distributors as md')
                ->join('distributor_implementasi_eskalink as die', 'die.distributor_code', '=', 'md.distributor_code')
                ->whereIn('md.area_code', $allowedAreas)
                ->pluck('die.eskalink_code')
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            return $query->whereIn('validasi_geotag.kodecabang', $eskaCodes);
        }

        if ($accessLevel === 'region') {
            $allowedRegions = (array) ($user->region_code ?? []);
            if (empty($allowedRegions) || in_array('HOINA', $allowedRegions)) {
                return $query;
            }

            $eskaCodes = DB::table('master_distributors as md')
                ->join('distributor_implementasi_eskalink as die', 'die.distributor_code', '=', 'md.distributor_code')
                ->whereIn('md.region_code', $allowedRegions)
                ->pluck('die.eskalink_code')
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            return $query->whereIn('validasi_geotag.kodecabang', $eskaCodes);
        }

        return $query;
    }
}
