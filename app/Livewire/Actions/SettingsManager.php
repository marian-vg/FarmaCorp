<?php

namespace App\Livewire\Actions;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class SettingsManager extends Component
{
    public function render()
    {
        return view('livewire.config.settings-manager');
    }
}
