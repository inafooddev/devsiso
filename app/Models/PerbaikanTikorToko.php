<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerbaikanTikorToko extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'perbaikan_tikor_toko';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'region_code',
        'area_code',
        'distributor_code',
        'sales_code',
        'customer_code',
        'latitude',
        'longitude',
        'status',
        'foto',
        'keterangan',
        'timestamp',
        'source',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'timestamp' => 'datetime',
    ];

    /**
     * Get the distributor implementasi eskalink associated with the record.
     * die.eskalink_code = distributor_code
     */
    public function distributorImplementasiEskalink()
    {
        return $this->belongsTo(DistributorImplementasiEskalink::class, 'distributor_code', 'eskalink_code');
    }

    /**
     * Get the customer prc eska associated with the record by customer_code.
     */
    public function customerPrcEska()
    {
        return $this->belongsTo(CustomerPrcEska::class, 'customer_code', 'custno');
    }

    /**
     * Get the exact customer prc eska record using both distributor_code and customer_code.
     * Use $item->exact_customer instead of $item->customerPrcEska to avoid wrong branch data.
     * Note: Tim Elite (SPV) now uses direct LEFT JOIN in its own component.
     */
    public function getExactCustomerAttribute()
    {
        if (!array_key_exists('exact_customer', $this->relations)) {
            $customer = CustomerPrcEska::where('kodecabang', $this->distributor_code)
                ->where('custno', $this->customer_code)
                ->first();
            
            $this->setRelation('exact_customer', $customer);
        }
        return $this->getRelation('exact_customer');
    }

    /**
     * Helper to query fsalesman records from the database using sales_code and distributor_code.
     * fs.SLSNO = sales_code AND fs.KD = distributor_code
     */
    public function fsalesmanQuery()
    {
        return \Illuminate\Support\Facades\DB::table('fsalesman')
            ->where('SLSNO', $this->sales_code)
            ->where('KD', $this->distributor_code);
    }

    /**
     * Scope query to only include records accessible by the user based on hierarchy & source.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \App\Models\User|null $user
     * @param string|null $source 'se' | 'elite' | null
     * @param string $tableAlias
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUserAccess($query, $user = null, $source = null, $tableAlias = 'perbaikan_tikor_toko')
    {
        return self::applyUserAccessFilter($query, $user, $source, $tableAlias);
    }

    /**
     * Apply user access filter on an Eloquent builder or Query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query
     * @param \App\Models\User|null $user
     * @param string|null $source 'se' | 'elite' | null
     * @param string $tableAlias
     * @return mixed
     */
    public static function applyUserAccessFilter($query, $user = null, $source = null, $tableAlias = 'perbaikan_tikor_toko')
    {
        $user = $user ?: auth()->user();
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if ($source) {
            $query->where("{$tableAlias}.source", $source);
        }

        $accessLevel = method_exists($user, 'getAccessLevel') ? $user->getAccessLevel() : 'nasional';

        if ($accessLevel === 'nasional') {
            return $query;
        }

        if ($accessLevel === 'supervisor') {
            $spvCode = $user->supervisor_code;

            // 1. Resolve distributor codes from vw_distributor_hierarchy
            $distCodes = \Illuminate\Support\Facades\DB::table('vw_distributor_hierarchy')
                ->where('supervisor_code', $spvCode)
                ->pluck('distributor_code')
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            // 2. Also map to distributor_implementasi_eskalink if applicable
            $eskaCodes = [];
            if (!empty($distCodes)) {
                $eskaCodes = \Illuminate\Support\Facades\DB::table('distributor_implementasi_eskalink')
                    ->whereIn('distributor_code', $distCodes)
                    ->orWhereIn('eskalink_code', $distCodes)
                    ->pluck('eskalink_code')
                    ->filter()
                    ->unique()
                    ->values()
                    ->toArray();
            }

            $allowedDistributors = array_values(array_unique(array_merge($distCodes, $eskaCodes)));

            // 3. Fallback to team_elite_code_mappings if hierarchy had no records
            $fallbackArea = null;
            $fallbackRegion = null;
            if (empty($allowedDistributors)) {
                $mapping = \Illuminate\Support\Facades\DB::table('team_elite_code_mappings')
                    ->where('team_elite_code', $spvCode)
                    ->first();
                if ($mapping) {
                    $fallbackArea = $mapping->area_code;
                    $fallbackRegion = $mapping->region_code;
                }
            }

            $query->where(function ($sub) use ($spvCode, $allowedDistributors, $fallbackArea, $fallbackRegion, $source, $tableAlias) {
                if ($source === 'elite') {
                    // For elite, the submitter sales_code is the supervisor code
                    $sub->where("{$tableAlias}.sales_code", $spvCode);
                    if (!empty($allowedDistributors)) {
                        $sub->orWhereIn("{$tableAlias}.distributor_code", $allowedDistributors);
                    }
                } else {
                    // For SE, filter by allowed distributor codes
                    if (!empty($allowedDistributors)) {
                        $sub->whereIn("{$tableAlias}.distributor_code", $allowedDistributors);
                    } elseif ($fallbackArea) {
                        $sub->where("{$tableAlias}.area_code", $fallbackArea);
                    } elseif ($fallbackRegion) {
                        $sub->where("{$tableAlias}.region_code", $fallbackRegion);
                    } else {
                        $sub->whereRaw('1 = 0');
                    }
                }
            });

            return $query;
        }

        if ($accessLevel === 'area') {
            $allowedAreas = (array) ($user->area_code ?? []);
            if (!empty($allowedAreas)) {
                $query->whereIn("{$tableAlias}.area_code", $allowedAreas);
            } else {
                $query->whereRaw('1 = 0');
            }
            return $query;
        }

        if ($accessLevel === 'region') {
            $allowedRegions = (array) ($user->region_code ?? []);
            if (!empty($allowedRegions)) {
                $query->whereIn("{$tableAlias}.region_code", $allowedRegions);
            } else {
                $query->whereRaw('1 = 0');
            }
            return $query;
        }

        return $query;
    }
}
