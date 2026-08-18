<?php

namespace App\Livewire\Others\Insentif\Perhitungan;

use Livewire\Component;
use App\Models\InsentifKacabMapping;
use App\Models\InsentifMasterDistributor;
use Livewire\Attributes\On;

class SettingKacabMapping extends Component
{
    public $isOpen = false;
    
    public $parent_cabang = '';
    public $child_cabang = '';
    
    public $cabangList = [];

    #[On('openSettingKacabModal')]
    public function openModal()
    {
        $this->isOpen = true;
        $this->resetForm();
        $this->loadCabangList();
    }

    public function closeModal()
    {
        $this->isOpen = false;
    }

    public function loadCabangList()
    {
        // Get all unique cabang names
        $this->cabangList = InsentifMasterDistributor::select('cabang')
            ->whereNotNull('cabang')
            ->distinct()
            ->orderBy('cabang')
            ->pluck('cabang')
            ->map(function($c) { return strtoupper(trim($c)); })
            ->toArray();
    }

    public function resetForm()
    {
        $this->parent_cabang = '';
        $this->child_cabang = '';
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate([
            'parent_cabang' => 'required',
            'child_cabang' => 'required|different:parent_cabang',
        ], [
            'parent_cabang.required' => 'Cabang Induk harus dipilih.',
            'child_cabang.required' => 'Cabang Anak harus dipilih.',
            'child_cabang.different' => 'Cabang Anak tidak boleh sama dengan Cabang Induk.',
        ]);

        // Check if child is already mapped to someone else
        $existing = InsentifKacabMapping::where('child_cabang', $this->child_cabang)->first();
        if ($existing) {
            $this->addError('child_cabang', 'Cabang ini sudah ter-mapping ke ' . $existing->parent_cabang);
            return;
        }

        // Prevent circular mapping: If parent is already a child, or child is already a parent
        $isChildAlreadyParent = InsentifKacabMapping::where('parent_cabang', $this->child_cabang)->exists();
        if ($isChildAlreadyParent) {
            $this->addError('child_cabang', 'Cabang ini sudah menjadi Induk dari cabang lain. Tidak bisa menjadi Anak.');
            return;
        }

        $isParentAlreadyChild = InsentifKacabMapping::where('child_cabang', $this->parent_cabang)->exists();
        if ($isParentAlreadyChild) {
            $this->addError('parent_cabang', 'Cabang ini berstatus Anak dari cabang lain. Tidak bisa menjadi Induk.');
            return;
        }

        InsentifKacabMapping::create([
            'parent_cabang' => $this->parent_cabang,
            'child_cabang' => $this->child_cabang,
        ]);

        $this->resetForm();
        session()->flash('success', 'Mapping berhasil ditambahkan.');
        
        // Notify parent to refresh
        $this->dispatch('refreshKacabData');
    }

    public function delete($id)
    {
        InsentifKacabMapping::findOrFail($id)->delete();
        session()->flash('success', 'Mapping berhasil dihapus.');
        
        // Notify parent to refresh
        $this->dispatch('refreshKacabData');
    }

    public function render()
    {
        $mappings = InsentifKacabMapping::orderBy('parent_cabang')->orderBy('child_cabang')->get();
        return view('livewire.others.insentif.perhitungan.setting-kacab-mapping', [
            'mappings' => $mappings
        ]);
    }
}
