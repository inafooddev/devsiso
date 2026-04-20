<?php

namespace App\Livewire\Pages;

use Livewire\Component;

class UnderBounce extends Component
{
    public $title = 'Under Bounce';

    public function render()
    {
        return view('livewire.pages.under-bounce')
            ->layout('layouts.app', ['title' => $this->title]);
    }
}
