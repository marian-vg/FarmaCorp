<?php

namespace App\Livewire\Admin;

use App\Models\Profile;
use Spatie\Permission\Models\Permission;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Flux\Flux;

#[Layout('components.layouts.app', ['title' => 'Profile Manager'])]
class ProfileManager extends Component
{
    public ?Profile $editingProfile = null;

    public array $profileContext = [
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
            'profileContext.name' => 'required|string|max:255|unique:profiles,name' . ($this->editingProfile ? ',' . $this->editingProfile->id : ''),
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

    public function render()
    {
        return view('livewire.admin.profile-manager');
    }
}
