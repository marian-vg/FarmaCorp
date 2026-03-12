<?php

namespace App\Livewire\Actions;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class SettingsManager extends Component
{
    public function render()
    {
        return view('livewire.config.settings-manager');
    }
}
