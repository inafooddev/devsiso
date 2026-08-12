<?php

namespace App\Livewire\Dashboard\Traits;

use Illuminate\Support\Facades\DB;

trait WithAccessFilter
{
    /**
     * Apply row-level security (RLS) based on the logged-in user's access level.
     * Procedure: User -> Master Distributors -> Data Tables (by cabang)
     *
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $query
     * @param string $prefix Prefix for the cabang column (e.g. 'vspc.' or '')
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     */
    protected function applyAccessFilter($query, $prefix = '')
    {
        $user = auth()->user();
        
        if (!$user) {
            return $query;
        }

        $level = $user->getAccessLevel();

        if ($level === 'nasional') {
            return $query;
        }

        // 1. Cek hak akses ke tabel Master Distributors (via view hierarki yang benar)
        $masterQuery = DB::table('vw_distributor_hierarchy');
        
        if ($level === 'supervisor') {
            $masterQuery->where('supervisor_code', $user->supervisor_code);
        } elseif ($level === 'area') {
            $masterQuery->whereIn('area_code', (array)$user->area_code);
        } elseif ($level === 'region') {
            $masterQuery->whereIn('region_code', (array)$user->region_code);
        }

        // 2. Dapatkan daftar cabang (branch_name) yang boleh diakses
        $allowedCabangs = $masterQuery->distinct()->pluck('branch_name')->toArray();

        // 3. Filter tabel data utama hanya untuk cabang-cabang tersebut
        if (empty($allowedCabangs)) {
            // Jika tidak punya akses ke cabang manapun, berikan kondisi false
            $query->where($prefix . 'cabang', 'INVALID_ACCESS_NO_CABANG');
        } else {
            $query->whereIn($prefix . 'cabang', $allowedCabangs);
        }

        return $query;
    }
}
