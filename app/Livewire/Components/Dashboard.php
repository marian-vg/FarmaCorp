<?php

namespace App\Livewire\Components;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'User Dashboard'])]
class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.components.dashboard', [
            'user' => Auth::user(),
        ]);
    }
}
