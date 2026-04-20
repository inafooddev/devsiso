<?php

namespace App\Livewire\Pages;

use Livewire\Component;

class UnderConstruction extends Component
{
    public $title = 'Under Construction';

    public function render()
    {
        return view('livewire.pages.under-construction')
            ->layout('layouts.app', ['title' => $this->title]); // ganti dengan nama layout kamu
    }
}
