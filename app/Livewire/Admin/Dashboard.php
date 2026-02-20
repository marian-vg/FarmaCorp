<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Admin Dashboard'])]
#[Lazy]
class Dashboard extends Component
{
    public function placeholder()
    {
        return view('livewire.placeholders.skeleton-table');
    }

    public function render()
    {
        // $user = Auth::user;

        $users = User::all();

        return view('livewire.admin.dashboard', [
            'users' => $users,
        ]);
    }
}
