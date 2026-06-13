<?php

namespace App\Livewire\Others\PageTemplateStandard;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.others.page-template-standard.index')->layout('layouts.app');
    }
}
