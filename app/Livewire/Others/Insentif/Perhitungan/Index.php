<?php

namespace App\Livewire\Others\Insentif\Perhitungan;

use Livewire\Component;

class Index extends Component
{
    public $activeTab = 'insentif-se';

    protected $queryString = ['activeTab'];

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('livewire.others.insentif.perhitungan.index')->layout('layouts.app');
    }
}
