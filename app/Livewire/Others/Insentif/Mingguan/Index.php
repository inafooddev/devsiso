<?php

namespace App\Livewire\Others\Insentif\Mingguan;

use Livewire\Component;

class Index extends Component
{
    public $activeTab = 'summary';

    protected $queryString = ['activeTab'];

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('livewire.others.insentif.mingguan.index')->layout('layouts.app');
    }
}
