<?php

namespace App\Livewire\Others\Insentif\Mingguan;

use Livewire\Component;
use App\Models\InsentifHeaderGrup;
use App\Models\InsentifHeaderGrupDetail;
use App\Models\InsentifHeaderGrupRegion;
use App\Models\InsentifProdukGrup;
use App\Models\InsentifMasterDistributor;

class SettingHeader extends Component
{
    public $headerId = null;
    public $nama_header = '';
    public $selected_groups = [];
    public $selected_regions = [];
    public $searchGroup = '';

    // State untuk Modal Produk
    public $isProductModalOpen = false;
    public $modalProductGroup = '';
    public $modalProducts = [];

    public function resetForm()
    {
        $this->headerId = null;
        $this->nama_header = '';
        $this->selected_groups = [];
        $this->selected_regions = [];
        $this->searchGroup = '';
        $this->resetValidation();
    }

    public function edit($id)
    {
        $header = InsentifHeaderGrup::with(['details', 'regions'])->findOrFail($id);
        $this->headerId = $header->id;
        $this->nama_header = $header->nama_header;
        $this->selected_groups = $header->details->pluck('product_group_3')->toArray();
        $this->selected_regions = $header->regions->pluck('region_name')->toArray();
        $this->resetValidation();
    }

    public function delete($id)
    {
        InsentifHeaderGrup::findOrFail($id)->delete();
        session()->flash('success', 'Header Grup berhasil dihapus.');
        if ($this->headerId == $id) {
            $this->resetForm();
        }
    }

    public function save()
    {
        $this->validate([
            'nama_header' => 'required|string|max:255|unique:insentif_header_grups,nama_header' . ($this->headerId ? ',' . $this->headerId : ''),
            'selected_groups' => 'required|array|min:1',
            'selected_regions' => 'required|array|min:1',
        ]);

        // Pengecekan Eksklusivitas (Apakah grup mentah sudah dipakai oleh Header lain?)
        $alreadyUsed = InsentifHeaderGrupDetail::whereIn('product_group_3', $this->selected_groups)
            ->when($this->headerId, function ($query) {
                return $query->where('insentif_header_grup_id', '!=', $this->headerId);
            })->pluck('product_group_3')->toArray();

        if (!empty($alreadyUsed)) {
            $this->addError('selected_groups', 'Grup berikut sudah dipakai di Header lain: ' . implode(', ', $alreadyUsed));
            return;
        }

        if ($this->headerId) {
            $header = InsentifHeaderGrup::findOrFail($this->headerId);
            $header->update(['nama_header' => $this->nama_header]);
        } else {
            $header = InsentifHeaderGrup::create(['nama_header' => $this->nama_header]);
        }

        // Hapus mapping lama
        $header->details()->delete();

        // Buat mapping baru
        $details = [];
        foreach ($this->selected_groups as $pg3) {
            $details[] = [
                'insentif_header_grup_id' => $header->id,
                'product_group_3' => $pg3,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        InsentifHeaderGrupDetail::insert($details);

        // Hapus & Simpan Regions
        $header->regions()->delete();
        $regions = [];
        foreach ($this->selected_regions as $reg) {
            $regions[] = [
                'insentif_header_grup_id' => $header->id,
                'region_name' => $reg,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        InsentifHeaderGrupRegion::insert($regions);

        session()->flash('success', 'Header Grup berhasil disimpan.');
        $this->resetForm();
    }

    public function openProductModal($pg3)
    {
        $this->modalProductGroup = $pg3;
        $this->modalProducts = InsentifProdukGrup::where('product_group_3', $pg3)
            ->orderBy('prd_name')
            ->get()
            ->toArray();
        $this->isProductModalOpen = true;
    }

    public function render()
    {
        $headers = InsentifHeaderGrup::with(['details', 'regions'])->orderBy('nama_header')->get();
        
        // Ambil daftar unik product_group_3 dari master produk
        $query = InsentifProdukGrup::select('product_group_3')
            ->distinct()
            ->orderBy('product_group_3');

        if (!empty($this->searchGroup)) {
            $query->where('product_group_3', 'ilike', '%' . $this->searchGroup . '%');
        }

        $rawGroups = $query->get()->pluck('product_group_3');

        // Ambil semua mapping yang sudah ada (untuk di-disable jika sudah dipakai header lain)
        $mappedGroups = InsentifHeaderGrupDetail::all()->groupBy('product_group_3');

        // Ambil daftar Region unik
        $rawRegions = InsentifMasterDistributor::select('region_name')
            ->whereNotNull('region_name')
            ->distinct()
            ->orderBy('region_name')
            ->pluck('region_name');

        return view('livewire.others.insentif.mingguan.setting-header', [
            'headers' => $headers,
            'rawGroups' => $rawGroups,
            'mappedGroups' => $mappedGroups,
            'rawRegions' => $rawRegions,
        ]);
    }
}
