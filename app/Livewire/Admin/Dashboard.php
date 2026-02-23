<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Admin Dashboard'])]
#[Lazy]
class Dashboard extends Component
{
    public string $search = '';
    public string $statusFilter = 'all'; // all, active, inactive
    public string $roleFilter = '';

    public string $newPassword = '';
    public string $newPasswordConfirmation = '';

    public array $newUserContext = [
        'name' => '',
        'email' => '',
        'role' => '',
        'password' => '',
        'password_confirmation' => '',
    ];

    public function createUser()
    {
        $this->validate([
            'newUserContext.name' => 'required|string|max:255',
            'newUserContext.email' => 'required|string|email|max:255|unique:users,email',
            'newUserContext.role' => 'required|string|exists:roles,name',
            'newUserContext.password' => 'required|string|min:8|same:newUserContext.password_confirmation',
        ]);

        $user = User::create([
            'name' => $this->newUserContext['name'],
            'email' => $this->newUserContext['email'],
            'password' => Hash::make($this->newUserContext['password']),
            'is_active' => true,
        ]);

        if ($this->newUserContext['role']) {
            $user->assignRole($this->newUserContext['role']);
        }

        $this->reset('newUserContext');

        Flux::modal('add-user')->close();
    }

    public function deactivateUser(User $user)
    {
        $user->is_active = false;
        $user->save();
    }

    public function updatePassword(User $user)
    {
        $this->validate([
            'newPassword' => 'required|min:8|same:newPasswordConfirmation',
        ]);

        $user->password = Hash::make($this->newPassword);
        $user->save();

        $this->reset(['newPassword', 'newPasswordConfirmation']);
    }

    public function placeholder()
    {
        return view('livewire.placeholders.skeleton-table');
    }

    #[Computed]
    public function roles()
    {
        return \Spatie\Permission\Models\Role::all();
    }

    public function render()
    {
        $query = User::with(['roles.permissions', 'permissions']);

        if ($this->search) {
            $searchTerm = '%' . mb_strtolower($this->search) . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->whereRaw('LOWER(name) LIKE ?', [$searchTerm])
                  ->orWhereRaw('LOWER(email) LIKE ?', [$searchTerm]);
            });
        }

        if ($this->statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        if ($this->roleFilter) {
            $query->whereHas('roles', function ($q) {
                $q->where('name', $this->roleFilter);
            });
        }

        $users = $query->get();

        return view('livewire.admin.dashboard', [
            'users' => $users,
        ]);
    }
}
