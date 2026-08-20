<?php

namespace App\Livewire\Others\Insentif\Mingguan;

use Livewire\Component;
use App\Models\InsentifMingguanProdukGrup;
use App\Models\InsentifProdukGrup;
use App\Models\InsentifMingguanPg3Mapping;
use Illuminate\Support\Facades\DB;

class SettingMappingPg3 extends Component
{
    public $mappings = [];
    public $search = '';

    public function mount()
    {
        $this->loadMappings();
    }

    public function loadMappings()
    {
        // Get all unique mingguan PG3
        $mingguanPg3s = InsentifMingguanProdukGrup::select('product_group_3')
            ->distinct()
            ->orderBy('product_group_3')
            ->pluck('product_group_3')
            ->toArray();

        // Get existing mappings
        $existingMappings = InsentifMingguanPg3Mapping::pluck('pg3_bulanan', 'pg3_mingguan')->toArray();

        // Prepare mappings state
        $this->mappings = [];
        foreach ($mingguanPg3s as $pg3) {
            $this->mappings[$pg3] = $existingMappings[$pg3] ?? '';
        }
    }

    public function saveMappings()
    {
        // Upsert logic
        DB::beginTransaction();
        try {
            // Kita bisa menggunakan upsert untuk efisiensi
            $dataToInsert = [];
            foreach ($this->mappings as $mingguan => $bulanan) {
                if (!empty(trim($bulanan))) {
                    $dataToInsert[] = [
                        'pg3_mingguan' => $mingguan,
                        'pg3_bulanan' => trim($bulanan),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // Hapus yang lama lalu insert yang baru (simple way)
            InsentifMingguanPg3Mapping::truncate();
            
            if (!empty($dataToInsert)) {
                InsentifMingguanPg3Mapping::insert($dataToInsert);
            }

            DB::commit();
            session()->flash('success', 'Mapping PG3 berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal menyimpan mapping: ' . $e->getMessage());
        }
    }

    public function render()
    {
        // Ambil pilihan PG3 Bulanan
        $bulananPg3s = InsentifProdukGrup::select('product_group_3')
            ->distinct()
            ->orderBy('product_group_3')
            ->pluck('product_group_3')
            ->toArray();

        $filteredMingguan = collect($this->mappings)->filter(function($val, $key) {
            if (empty($this->search)) return true;
            return stripos($key, $this->search) !== false;
        });

        return view('livewire.others.insentif.mingguan.setting-mapping-pg3', [
            'bulananPg3s' => $bulananPg3s,
            'filteredMingguan' => $filteredMingguan,
        ]);
    }
}
