<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Admin Dashboard'])]
class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.admin.dashboard', [
            'user' => Auth::user(),
        ]);
    }
}
