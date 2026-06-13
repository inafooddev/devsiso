<?php

namespace App\Livewire\Others\ComponentStandard;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.others.component-standard.index')->layout('layouts.app');
    }
}
