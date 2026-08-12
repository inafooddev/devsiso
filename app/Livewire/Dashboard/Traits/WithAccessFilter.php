<?php

namespace App\Livewire\Dashboard\Traits;

use Illuminate\Support\Facades\DB;

trait WithAccessFilter
{
    /**
     * Apply row-level security (RLS) based on the logged-in user's access level.
     * This ensures users only see data belonging to their Region, Area, or Supervisor code.
     *
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $query
     * @param string $prefix Prefix for the columns (e.g. 'vspc.' or '')
     * @param string $supervisorFallbackView If the table lacks a 'supervisor' column, use this view to resolve supervisor to cabangs.
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     */
    protected function applyAccessFilter($query, $prefix = '', $supervisorFallbackView = null)
    {
        $user = auth()->user();
        
        if (!$user) {
            return $query;
        }

        $level = $user->getAccessLevel();

        if ($level === 'supervisor') {
            if ($supervisorFallbackView) {
                $cabangs = DB::table($supervisorFallbackView)
                    ->where('supervisor', $user->supervisor_code)
                    ->distinct()
                    ->pluck('cabang')
                    ->toArray();
                $query->whereIn($prefix . 'cabang', $cabangs);
            } else {
                $query->where($prefix . 'supervisor', $user->supervisor_code);
            }
        } elseif ($level === 'area') {
            $query->whereIn($prefix . 'area', (array)$user->area_code);
        } elseif ($level === 'region') {
            $query->whereIn($prefix . 'region', (array)$user->region_code);
        }

        return $query;
    }
}
