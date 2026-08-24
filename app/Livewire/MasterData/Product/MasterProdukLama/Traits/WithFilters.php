<?php

namespace App\Livewire\MasterData\Product\MasterProdukLama\Traits;

use App\Models\MasterProdukLama;

trait WithFilters
{
    public $search = '';
    public $statusFilter = '';
    public $kategoriFilter = '';
    public $topItemFilter = '';
    public $subbrandFilter = '';
    public $divisiFilter = '';

    public function resetFilters()
    {
        $this->reset([
            'search',
            'statusFilter',
            'kategoriFilter',
            'topItemFilter',
            'subbrandFilter',
            'divisiFilter',
        ]);
        
        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }

    public function updatingSearch()
    {
        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }

    public function updatingStatusFilter()
    {
        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }

    public function updatingKategoriFilter()
    {
        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }

    public function updatingTopItemFilter()
    {
        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }

    public function updatingSubbrandFilter()
    {
        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }

    public function updatingDivisiFilter()
    {
        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }
}
