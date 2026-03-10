<?php

namespace App\Livewire\Admin;

use App\Models\Batch;
use App\Models\Profile;
use App\Models\Setting;
use App\Models\User;
use App\Traits\Notifies;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Lazy;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.app', ['title' => 'Admin Dashboard'])]
#[Lazy]
class Dashboard extends Component
{
    use Notifies, WithPagination;

    public int $priceMaxDays = 30;

    public string $search = '';

    public string $statusFilter = 'all'; // all, active, inactive

    public string $roleFilter = '';

    public int $alertDays = 30;

    public string $newPassword = '';

    public string $newPasswordConfirmation = '';

    public string $postSaleAction = 'preguntar';

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

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedRoleFilter()
    {
        $this->resetPage();
    }

    public function saveSaleConfig()
    {
        Setting::updateOrCreate(
            ['key' => 'post_sale_action'],
            ['value' => $this->postSaleAction]
        );
        $this->notify('Preferencia de comprobantes guardada.', 'success');
    }

    public function savePriceConfig()
    {
        $this->validate(['priceMaxDays' => 'required|integer|min:1']);
        Setting::updateOrCreate(
            ['key' => 'price_max_days'],
            ['value' => (string) $this->priceMaxDays]
        );
        $this->notify('Configuración de precios actualizada.', 'success');
    }

    public function mount()
    {
        $setting = Setting::where('key', 'alert_days')->first();
        if ($setting) {
            $this->alertDays = (int) $setting->value;
        }

        $priceSetting = Setting::where('key', 'price_max_days')->first();
        if ($priceSetting) {
            $this->priceMaxDays = (int) $priceSetting->value;
        }

        $this->postSaleAction = Setting::where('key', 'post_sale_action')->first()?->value ?? 'preguntar';
    }

    public function saveAlertDays()
    {
        $this->validate(['alertDays' => 'required|integer|min:1|max:365']);
        Setting::updateOrCreate(
            ['key' => 'alert_days'],
            ['value' => (string) $this->alertDays]
        );
        $this->notify('Configuración guardada correctamente.', 'success');
    }

    #[Computed]
    public function roles()
    {
        return Cache::remember('roles_all', 86400, fn () => Role::all());
    }

    #[Computed]
    public function permissions()
    {
        $allPermissions = Cache::remember('permissions_all', 86400, fn () => Permission::all());

        if ($this->editingUser && $this->editingUser->hasRole('empleado')) {
            return $allPermissions->filter(function ($permission) {
                return ! str($permission->name)->contains(['user', 'role', 'permission', 'profile']);
            });
        }

        return $allPermissions;
    }

    #[Computed]
    public function allProfiles()
    {
        return Cache::remember('profiles_all', 86400, fn () => Profile::all());
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
        $this->dispatch('Roles guardados exitosamente.', 'success');
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
            if ($this->editingUser->hasRole('empleado')) {
                $hasAdminPermissions = collect($this->selectedPermissions)->contains(function ($name) {
                    return str($name)->contains(['user', 'role', 'permission', 'profile']);
                });

                if ($hasAdminPermissions) {
                    throw ValidationException::withMessages([
                        'selectedPermissions' => 'Bloqueo de Seguridad: No se pueden asignar permisos administrativos a un perfil de empleado.',
                    ]);
                }
            }

            $this->editingUser->syncPermissions($this->selectedPermissions);
            Flux::modal('edit-permissions')->close();
            $this->dispatch('Permisos guardados correctamente.', 'success');
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
            $this->notify('Perfiles actualizados correctamente.', 'success');
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
        $this->notify('Usuario creado correctamente.', 'success');
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
        $this->notify('Usuario editado correctamente.', 'success');
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
        $this->notify('Usuario actualizado correctamente.', 'success');
    }

    public function updatePassword(User $user)
    {
        $this->validate([
            'newPassword' => 'required|min:8|same:newPasswordConfirmation',
        ]);

        $user->password = Hash::make($this->newPassword);
        $user->save();

        $this->reset(['newPassword', 'newPasswordConfirmation']);
        $this->notify('Contraseña actualizada correctamente.', 'success');
    }

    public function placeholder()
    {
        return view('livewire.placeholders.skeleton-table');
    }

    public function render()
    {
        $users = User::search($this->search)
            ->query(function ($query) {
                $query->with(['roles.permissions', 'permissions', 'profiles']);

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
            })
            ->paginate(12);

        // 1. Expiring Batches (Current logic)
        $expiringBatches = Batch::where('current_quantity', '>', 0)
            ->where('expiration_date', '<=', Carbon::now()->addDays($this->alertDays))
            ->orderBy('expiration_date', 'asc')
            ->with(['medicine.product']) // Eager loading the correct relation keys
            ->take(10)
            ->get();

        // 2. Minimum Stock Alerts (New Logic for RF-05)
        $lowStockBatches = Batch::where('current_quantity', '<=', DB::raw('minimum_stock'))
            ->where('current_quantity', '>', 0)
            ->orderBy('current_quantity', 'asc')
            ->with(['medicine.product'])
            ->take(10)
            ->get();

        return view('livewire.admin.dashboard', [
            'users' => $users,
            'expiringBatches' => $expiringBatches,
            'lowStockBatches' => $lowStockBatches,
        ]);
    }
}
