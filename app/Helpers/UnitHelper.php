<?php

namespace App\Helpers;

use App\Models\UnitMapping;
use Illuminate\Support\Facades\Cache;

class UnitHelper
{
    /**
     * Normalize unit string based on UnitMapping table per distributor.
     * Caches the mappings to avoid excessive DB queries during import.
     *
     * @param string|null $unit
     * @param string $distributorCode
     * @return string
     */
    public static function normalize($unit, $distributorCode)
    {
        $unit = strtoupper(trim((string) $unit));
        $distributorCode = strtoupper(trim((string) $distributorCode));
        
        if (empty($unit)) {
            return '';
        }

        // Cache mappings per distributor for 1 hour
        $cacheKey = "unit_mappings_{$distributorCode}";
        $mappings = Cache::remember($cacheKey, 3600, function () use ($distributorCode) {
            return UnitMapping::where('distributor_code', $distributorCode)
                ->pluck('mapped_unit', 'raw_unit')
                ->toArray();
        });

        if (array_key_exists($unit, $mappings)) {
            return $mappings[$unit];
        }

        return $unit;
    }

    /**
     * Clear the cache for a specific distributor or all.
     */
    public static function clearCache($distributorCode = null)
    {
        if ($distributorCode) {
            Cache::forget("unit_mappings_" . strtoupper(trim($distributorCode)));
        } else {
            // Jika tidak ada kode, kita tidak bisa menebak semua key, 
            // tapi biasanya di-clear saat management unit berubah.
            // Untuk simplisitas saat ini, kita asumsikan clear per distributor saat edit.
        }
    }
}
