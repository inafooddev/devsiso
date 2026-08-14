<?php

namespace App\Livewire\Others\Insentif\Target;

use Livewire\Component;

class Index extends Component
{
    public $activeTab = 'target-se-value';

    protected $queryString = ['activeTab'];

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('livewire.others.insentif.target.index')->layout('layouts.app');
    }
}
