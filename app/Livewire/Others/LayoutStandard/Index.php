<?php

namespace App\Livewire\Others\LayoutStandard;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.others.layout-standard.index')->layout('layouts.app');
    }
}
