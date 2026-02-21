<?php

namespace App\Livewire\Admin;

use App\Models\Profile;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;

#[Layout('components.layouts.app', ['title' => 'Gestión de Perfiles'])]
class ProfileManagement extends Component
{
    use WithPagination;

    // Estados del componente
    public $showTrash = false;
    public $search = '';
    public $statusFilter = '';
    
    // Estados del formulario
    public $editingId = null;
    public $showModal = false;
    public $name = '';
    public $description = '';
    public $isActive = true;

    protected $paginationTheme = 'tailwind';

    public function mount(): void
    {
        $this->resetFilters();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->showTrash = false;
        $this->resetPage();
    }

    public function toggleTrash(): void
    {
        $this->showTrash = !$this->showTrash;
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal($profileId): void
    {
        $profile = Profile::withTrashed()->findOrFail($profileId);
        
        $this->editingId = $profile->id;
        $this->name = $profile->name;
        $this->description = $profile->description ?? '';
        $this->isActive = $profile->is_active;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->resetForm();
        $this->showModal = false;
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->description = '';
        $this->isActive = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => [
                'required', 
                'string', 
                'max:255', 
                // Esta es la forma "blindada"
                Rule::unique('profiles', 'name')->ignore($this->editingId)
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'isActive' => ['boolean'],
        ]);

        if ($this->editingId === null) {
            // Crear nuevo perfil
            $profile = Profile::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'is_active' => $this->isActive,
                'created_by' => Auth::id(),
            ]);

            session()->flash('message', 'Perfil creado exitosamente.');
        } else {
            // Actualizar perfil existente
            $profile = Profile::findOrFail($this->editingId);

            $profile->name = $validated['name'];
            $profile->description = $validated['description'] ?? null;
            $profile->is_active = $this->isActive;
            $profile->save();

            session()->flash('message', 'Perfil actualizado exitosamente.');
        }

        $this->closeModal();
        $this->resetPage();
    }

    public function toggleActive($profileId): void
    {
        $profile = Profile::findOrFail($profileId);
        $profile->is_active = !$profile->is_active;
        $profile->save();

        session()->flash('message', $profile->is_active 
            ? 'Perfil activado exitosamente.' 
            : 'Perfil desactivado exitosamente.');
    }

    public function delete($profileId): void
    {
        $profile = Profile::findOrFail($profileId);
        $profile->delete();

        session()->flash('message', 'Perfil enviado a la papelera exitosamente.');
        $this->resetPage();
    }

    public function restore($profileId): void
    {
        $profile = Profile::onlyTrashed()->findOrFail($profileId);
        $profile->restore();

        session()->flash('message', 'Perfil restaurado exitosamente.');
        $this->resetPage();
    }

    public function forceDelete($profileId): void
    {
        $profile = Profile::onlyTrashed()->findOrFail($profileId);
        $profile->forceDelete();

        session()->flash('message', 'Perfil eliminado permanentemente.');
        $this->resetPage();
    }

    public function getProfilesProperty()
    {
        $query = $this->showTrash 
            ? Profile::onlyTrashed() 
            : Profile::query();

        // Búsqueda por nombre
        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        // Filtro por estado activo/inactivo
        if ($this->statusFilter !== '') {
            $query->where('is_active', $this->statusFilter === 'active');
        }

        return $query->with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.admin.profile-management', [
            'profiles' => $this->profiles,
        ]);
    }

    public function getTrashCountProperty()
    {
        return Profile::onlyTrashed()->count();
    }
}
