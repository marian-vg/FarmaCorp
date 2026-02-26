<?php

namespace App\Livewire\Admin;

use App\Models\Profile;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Permission;

#[Layout('components.layouts.app', ['title' => 'Profile Manager'])]
class ProfileManager extends Component
{
    public ?Profile $editingProfile = null;

    public ?Permission $editingPermission = null;

    public array $profileContext = [
        'name' => '',
        'description' => '',
    ];

    public array $permissionContext = [
        'name' => '',
        'description' => '',
    ];

    public array $selectedPermissions = [];

    #[Computed]
    public function profiles()
    {
        return Profile::with('permissions')->get();
    }

    #[Computed]
    public function permissions()
    {
        return Permission::all();
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

        Flux::modal('profile-form')->close();
        $this->reset(['profileContext', 'selectedPermissions', 'editingProfile']);
    }

    public function deleteProfile()
    {
        if ($this->editingProfile) {
            $this->editingProfile->delete();
            Flux::modal('confirm-delete-profile')->close();
            $this->reset(['editingProfile']);
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
            'description' => $permission->description,
        ];
        Flux::modal('permission-form')->show();
    }

    public function savePermission()
    {
        $rules = [
            'permissionContext.name' => 'required|string|max:255|unique:permissions,name'.($this->editingPermission ? ','.$this->editingPermission->id : ''),
            'permissionContext.description' => 'required|string|max:255',
        ];

        $this->validate($rules);

        if ($this->editingPermission) {
            $this->editingPermission->update($this->permissionContext);
        } else {
            Permission::create($this->permissionContext);
        }

        Flux::modal('permission-form')->close();
        $this->reset(['permissionContext', 'editingPermission']);
    }

    public function confirmDeletePermission(Permission $permission)
    {
        $this->editingPermission = $permission;
        Flux::modal('confirm-delete-permission')->show();
    }

    public function deletePermission()
    {
        if ($this->editingPermission) {
            $this->editingPermission->delete();
            Flux::modal('confirm-delete-permission')->close();
            $this->reset(['editingPermission']);
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
