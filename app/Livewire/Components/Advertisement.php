<?php

namespace App\Livewire\Components;

use Livewire\Component;

class Advertisement extends Component
{
    public function render()
    {
        $ads=\App\Models\Advertisement::get();
        return view('livewire.components.advertisement',compact('ads'));
    }
}
