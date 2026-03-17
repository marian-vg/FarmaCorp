<?php

namespace App\Livewire\Admin;

use App\Events\UserPermissionsUpdated;
use App\Models\Profile;
use App\Traits\Notifies;
use Flux\Flux;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

#[Layout('components.layouts.app', ['title' => 'Profile Manager'])]
class ProfileManager extends Component
{
    use Notifies, WithPagination;

    public string $search = '';

    public ?Profile $editingProfile = null;

    public ?Permission $editingPermission = null;

    public array $profileContext = [
        'name' => '',
        'description' => '',
    ];

    public array $permissionContext = [
        'name' => '',
        'display_name' => '',
        'description' => '',
    ];

    public array $selectedPermissions = [];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    #[Computed]
    public function profiles()
    {
        return Profile::search($this->search)
            ->query(fn ($query) => $query->with('permissions'))
            ->paginate(12);
    }

    #[Computed]
    public function permissions()
    {
        return Cache::remember('permissions_all', 86400, fn () => Permission::all());
    }

    public function createProfile()
    {
        $this->reset(['profileContext', 'selectedPermissions', 'editingProfile']);
        Flux::modal('profile-form')->show();
    }

    public function editProfile(Profile $profile)
    {
        $this->editingProfile = $profile;
        $this->profileContext = [
            'name' => $profile->name,
            'description' => $profile->description,
        ];
        $this->selectedPermissions = $profile->permissions->pluck('name')->toArray();

        Flux::modal('profile-form')->show();
    }

    public function confirmDelete(Profile $profile)
    {
        $this->editingProfile = $profile;
        Flux::modal('confirm-delete-profile')->show();
    }

    public function saveProfile()
    {
        $this->authorize('roles.crear_editar');
        $rules = [
            'profileContext.name' => 'required|string|max:255|unique:profiles,name'.($this->editingProfile ? ','.$this->editingProfile->id : ''),
            'profileContext.description' => 'nullable|string',
        ];

        $this->validate($rules);

        if ($this->editingProfile) {
            $this->editingProfile->update($this->profileContext);
            $profile = $this->editingProfile;
        } else {
            $profile = Profile::create($this->profileContext);
        }

        $profile->syncPermissions($this->selectedPermissions);

        foreach ($profile->users()->get() as $user) {
            UserPermissionsUpdated::dispatch($user);
        }

        Cache::forget('profiles_all');
        Flux::modal('profile-form')->close();
        $this->reset(['profileContext', 'selectedPermissions', 'editingProfile']);
        $this->notify('Perfil guardado exitosamente.', 'success');
    }

    public function deleteProfile()
    {
        $this->authorize('roles.eliminar');
        if ($this->editingProfile) {
            $this->editingProfile->delete();
            Cache::forget('profiles_all');
            Flux::modal('confirm-delete-profile')->close();
            $this->reset(['editingProfile']);
            $this->notify('Perfil eliminado.', 'success');
        }
    }

    // Permission CRUD Logic
    public function createPermission()
    {
        $this->reset(['permissionContext', 'editingPermission']);
        Flux::modal('permission-form')->show();
    }

    public function editPermission(Permission $permission)
    {
        $this->editingPermission = $permission;
        $this->permissionContext = [
            'name' => $permission->name,
            'display_name' => $permission->display_name ?? $permission->name,
            'description' => $permission->description,
        ];
        Flux::modal('permission-form')->show();
    }

    public function savePermission()
    {
        $this->authorize('roles.crear_editar');
        $rules = [
            'permissionContext.display_name' => 'required|string|max:255',
            'permissionContext.description' => 'required|string|max:255',
        ];

        $this->validate($rules);

        if ($this->editingPermission) {
            $this->editingPermission->update([
                'display_name' => $this->permissionContext['display_name'],
                'description' => $this->permissionContext['description'],
            ]);
        } else {
            // Generar el slug para el nombre interno
            $slugName = Str::slug($this->permissionContext['display_name']);

            // Si el slug ya existe, lanzar error de validación manual
            if (Permission::where('name', $slugName)->exists()) {
                $this->addError('permissionContext.display_name', 'Ya existe un permiso con este nombre base.');

                return;
            }

            Permission::create([
                'name' => $slugName,
                'display_name' => $this->permissionContext['display_name'],
                'description' => $this->permissionContext['description'],
            ]);
        }

        // Limpiar la caché de Spatie según la documentación oficial
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        Cache::forget('permissions_all');

        Flux::modal('permission-form')->close();
        $this->reset(['permissionContext', 'editingPermission']);
        $this->notify('Permiso guardado exitosamente.', 'success');
    }

    public function confirmDeletePermission(Permission $permission)
    {
        $this->editingPermission = $permission;
        Flux::modal('confirm-delete-permission')->show();
    }

    public function deletePermission()
    {
        $this->authorize('roles.eliminar');
        if ($this->editingPermission) {
            $this->editingPermission->delete();
            app()[PermissionRegistrar::class]->forgetCachedPermissions();
            Cache::forget('permissions_all');
            Flux::modal('confirm-delete-permission')->close();
            $this->reset(['editingPermission']);
            $this->notify('Permiso eliminado.', 'success');
        }
    }

    public function showPermissionsList()
    {
        Flux::modal('permissions-list')->show();
    }

    public function render()
    {
        return view('livewire.admin.profile-manager');
    }
}
