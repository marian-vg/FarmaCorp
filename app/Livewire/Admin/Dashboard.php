<?php

namespace App\Livewire\Admin;

use App\Models\Profile;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Lazy;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.app', ['title' => 'Admin Dashboard'])]
#[Lazy]
class Dashboard extends Component
{
    public string $search = '';

    public string $statusFilter = 'all'; // all, active, inactive

    public string $roleFilter = '';

    public int $alertDays = 30;

    public string $newPassword = '';

    public string $newPasswordConfirmation = '';

    public array $newUserContext = [
        'name' => '',
        'email' => '',
        'role' => '',
        'password' => '',
        'password_confirmation' => '',
    ];

    public ?User $editingUser = null;

    public array $editUserContext = [
        'name' => '',
        'email' => '',
        'is_active' => false,
    ];

    public array $selectedRoles = [];

    public array $selectedPermissions = [];

    public array $selectedProfiles = [];

    public function mount()
    {
        $setting = \App\Models\Setting::where('key', 'alert_days')->first();
        if ($setting) {
            $this->alertDays = (int) $setting->value;
        }
    }

    public function saveAlertDays()
    {
        $this->validate(['alertDays' => 'required|integer|min:1|max:365']);
        \App\Models\Setting::updateOrCreate(
            ['key' => 'alert_days'],
            ['value' => (string) $this->alertDays]
        );
        Flux::toast('Configuración guardada correctamente.');
    }

    #[Computed]
    public function roles()
    {
        return Role::all();
    }

    #[Computed]
    public function permissions()
    {
        return Permission::all();
    }

    #[Computed]
    public function allProfiles()
    {
        return Profile::all();
    }

    public function editRoles(User $user)
    {
        $this->editingUser = $user;
        $this->selectedRoles = $user->roles->pluck('name')->toArray();
        Flux::modal('edit-roles')->show();
    }

    public function saveRoles()
    {
        if ($this->editingUser) {
            $this->editingUser->syncRoles($this->selectedRoles);
            Flux::modal('edit-roles')->close();
        }
    }

    public function editPermissions(User $user)
    {
        $this->editingUser = $user;
        $this->selectedPermissions = $user->getDirectPermissions()->pluck('name')->toArray();
        Flux::modal('edit-permissions')->show();
    }

    public function savePermissions()
    {
        if ($this->editingUser) {
            $this->editingUser->syncPermissions($this->selectedPermissions);
            Flux::modal('edit-permissions')->close();
        }
    }

    public function editProfiles(User $user)
    {
        $this->editingUser = $user;
        $this->selectedProfiles = $user->profiles->pluck('id')->toArray();
        Flux::modal('edit-profiles')->show();
    }

    public function saveProfiles()
    {
        if ($this->editingUser) {
            $this->editingUser->profiles()->sync($this->selectedProfiles);
            Flux::modal('edit-profiles')->close();
        }
    }

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

    public function reactivateUser(User $user)
    {
        $user->is_active = true;
        $user->save();
    }

    public function editUser(User $user)
    {
        $this->editingUser = $user;
        $this->editUserContext = [
            'name' => $user->name,
            'email' => $user->email,
            'is_active' => (bool) $user->is_active,
        ];
        $this->selectedRoles = $user->roles->pluck('name')->toArray();
        $this->selectedPermissions = $user->getDirectPermissions()->pluck('name')->toArray();

        Flux::modal('edit-user')->show();
    }

    public function updateUser()
    {
        if (! $this->editingUser) {
            return;
        }

        $this->validate([
            'editUserContext.name' => 'required|string|max:255',
            'editUserContext.email' => 'required|string|email|max:255|unique:users,email,'.$this->editingUser->id,
            'editUserContext.is_active' => 'boolean',
        ]);

        $this->editingUser->update([
            'name' => $this->editUserContext['name'],
            'email' => $this->editUserContext['email'],
            'is_active' => $this->editUserContext['is_active'],
        ]);

        $this->editingUser->syncRoles($this->selectedRoles);
        $this->editingUser->syncPermissions($this->selectedPermissions);

        Flux::modal('edit-user')->close();
        $this->reset(['editUserContext', 'selectedRoles', 'selectedPermissions', 'editingUser']);
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

    public function render()
    {
        $query = User::with(['roles.permissions', 'permissions']);

        if ($this->search) {
            $searchTerm = '%'.mb_strtolower($this->search).'%';
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

        $expiringMedicines = \App\Models\Medicine::query()
            ->where('expiration_date', '<=', now()->addDays($this->alertDays))
            ->orderBy('expiration_date', 'asc')
            ->get();

        return view('livewire.admin.dashboard', [
            'users' => $users,
            'expiringMedicines' => $expiringMedicines,
        ]);
    }
}
