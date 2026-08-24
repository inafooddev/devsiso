<?php

namespace App\Livewire\Rwo\MasterCustomer\Queries;

use Illuminate\Support\Facades\DB;
use App\Livewire\Rwo\MasterCustomer\Concerns\HasHierarchyAccess;

class SummaryQueryBuilder
{
    use HasHierarchyAccess;

    public function get(array $filters)
    {
        $query = DB::table('reward_outlet as r')
            ->leftJoin('master_branches as mb', 'r.branch_name', '=', 'mb.branch_name')
            ->leftJoin('master_supervisors as ms', 'mb.supervisor_code', '=', 'ms.supervisor_code')
            ->leftJoin('master_areas as ma', 'ma.area_code', '=', 'ms.area_code')
            ->leftJoin('team_elite_code_mappings as t', 't.siso_code', '=', 'ms.supervisor_code')
            ->leftJoin('fsalesman as f', 'f.SLSNO', '=', 't.team_elite_code');

        $this->applyHierarchyAccess($query);

        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('r.region_name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('r.area_name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('r.branch_name', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['filter_region_code'])) {
            $query->where('ma.region_code', $filters['filter_region_code']);
        }

        if (!empty($filters['filter_area_code'])) {
            $query->where('ma.area_code', $filters['filter_area_code']);
        }

        $query->select(
            'ma.region_code',
            'r.region_name',
            'ma.area_code',
            'r.area_name',
            't.team_elite_code as supervisor_code',
            'f.SLSNAME as supervisor_name',
            'r.branch_name',
            DB::raw('COUNT(r.customer_code) as total_customer'),
            DB::raw("SUM(CASE WHEN 
                (r.no_hp IS NULL OR r.no_hp = '') OR
                (r.nama_pemilik_toko IS NULL OR r.nama_pemilik_toko = '') OR
                (r.nama_ktp IS NULL OR r.nama_ktp = '') OR
                (r.nik_ktp IS NULL OR r.nik_ktp = '') OR
                (r.foto_ktp IS NULL OR r.foto_ktp = '') OR
                (r.no_rekening IS NULL OR r.no_rekening = '') OR
                (r.nama_bank IS NULL OR r.nama_bank = '') OR
                (r.nama_pemilik_norek IS NULL OR r.nama_pemilik_norek = '') OR
                (r.foto_toko2 IS NULL OR r.foto_toko2 = '') OR
                (r.foto_toko3 IS NULL OR r.foto_toko3 = '') OR
                (r.latitude IS NULL OR r.latitude = '') OR
                (r.longitude IS NULL OR r.longitude = '')
            THEN 1 ELSE 0 END) as total_belum_lengkap"),
            DB::raw("SUM(CASE WHEN 
                (r.no_hp IS NOT NULL AND r.no_hp != '') AND
                (r.nama_pemilik_toko IS NOT NULL AND r.nama_pemilik_toko != '') AND
                (r.nama_ktp IS NOT NULL AND r.nama_ktp != '') AND
                (r.nik_ktp IS NOT NULL AND r.nik_ktp != '') AND
                (r.foto_ktp IS NOT NULL AND r.foto_ktp != '') AND
                (r.no_rekening IS NOT NULL AND r.no_rekening != '') AND
                (r.nama_bank IS NOT NULL AND r.nama_bank != '') AND
                (r.nama_pemilik_norek IS NOT NULL AND r.nama_pemilik_norek != '') AND
                (r.foto_toko2 IS NOT NULL AND r.foto_toko2 != '') AND
                (r.foto_toko3 IS NOT NULL AND r.foto_toko3 != '') AND
                (r.latitude IS NOT NULL AND r.latitude != '') AND
                (r.longitude IS NOT NULL AND r.longitude != '')
            THEN 1 ELSE 0 END) as total_lengkap"),
            DB::raw("SUM(CASE WHEN r.no_hp IS NULL OR r.no_hp = '' THEN 1 ELSE 0 END) as missing_no_hp"),
            DB::raw("SUM(CASE WHEN r.nama_pemilik_toko IS NULL OR r.nama_pemilik_toko = '' THEN 1 ELSE 0 END) as missing_nama_pemilik_toko"),
            DB::raw("SUM(CASE WHEN r.nama_ktp IS NULL OR r.nama_ktp = '' THEN 1 ELSE 0 END) as missing_nama_ktp"),
            DB::raw("SUM(CASE WHEN r.nik_ktp IS NULL OR r.nik_ktp = '' THEN 1 ELSE 0 END) as missing_nik_ktp"),
            DB::raw("SUM(CASE WHEN r.foto_ktp IS NULL OR r.foto_ktp = '' THEN 1 ELSE 0 END) as missing_foto_ktp"),
            DB::raw("SUM(CASE WHEN r.no_rekening IS NULL OR r.no_rekening = '' THEN 1 ELSE 0 END) as missing_no_rekening"),
            DB::raw("SUM(CASE WHEN r.nama_bank IS NULL OR r.nama_bank = '' THEN 1 ELSE 0 END) as missing_nama_bank"),
            DB::raw("SUM(CASE WHEN r.nama_pemilik_norek IS NULL OR r.nama_pemilik_norek = '' THEN 1 ELSE 0 END) as missing_nama_pemilik_norek"),
            DB::raw("SUM(CASE WHEN r.foto_toko IS NULL OR r.foto_toko = '' THEN 1 ELSE 0 END) as missing_foto_toko"),
            DB::raw("SUM(CASE WHEN r.is_valid = false OR r.is_valid IS NULL THEN 1 ELSE 0 END) as missing_is_valid")
        )
        ->groupBy('ma.region_code', 'r.region_name', 'ma.area_code', 'r.area_name', 't.team_elite_code', 'f.SLSNAME', 'r.branch_name')
        ->orderBy('ma.region_code')
        ->orderBy('ma.area_code')
        ->orderBy('t.team_elite_code')
        ->orderBy('r.branch_name');

        return $query->get();
    }
}
