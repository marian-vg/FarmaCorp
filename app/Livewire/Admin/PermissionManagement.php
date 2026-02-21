<?php

namespace App\Livewire\Admin;

use App\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app', ['title' => 'Gestión de Permisos'])]
class PermissionManagement extends Component
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
    public $guardName = 'web';
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

    public function openEditModal($permissionId): void
    {
        $permission = Permission::withTrashed()->findOrFail($permissionId);
        
        $this->editingId = $permission->id;
        $this->name = $permission->name;
        $this->description = $permission->description ?? '';
        $this->guardName = $permission->guard_name;
        $this->isActive = $permission->is_active;
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
        $this->guardName = 'web';
        $this->isActive = true;
    }

    public function save(): void
    {
        $tableNames = config('permission.table_names');
        
        $validated = $this->validate([
            'name' => [
                'required', 
                'string', 
                'max:255',
                Rule::unique($tableNames['permissions'], 'name')
                    ->where('guard_name', $this->guardName)
                    ->ignore($this->editingId)
            ],
            'guardName' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'isActive' => ['boolean'],
        ]);

        if ($this->editingId === null) {
            // Crear nuevo permiso
            $permission = Permission::create([
                'name' => $validated['name'],
                'guard_name' => $validated['guardName'],
                'description' => $validated['description'] ?? null,
                'is_active' => $this->isActive,
                'created_by' => Auth::id(),
            ]);

            session()->flash('message', 'Permiso creado exitosamente.');
        } else {
            // Actualizar permiso existente
            $permission = Permission::findOrFail($this->editingId);

            $permission->name = $validated['name'];
            $permission->guard_name = $validated['guardName'];
            $permission->description = $validated['description'] ?? null;
            $permission->is_active = $this->isActive;
            $permission->save();

            session()->flash('message', 'Permiso actualizado exitosamente.');
        }

        $this->closeModal();
        $this->resetPage();
    }

    public function toggleActive($permissionId): void
    {
        $permission = Permission::findOrFail($permissionId);
        $permission->is_active = !$permission->is_active;
        $permission->save();

        session()->flash('message', $permission->is_active 
            ? 'Permiso activado exitosamente.' 
            : 'Permiso desactivado exitosamente.');
    }

    public function delete($permissionId): void
    {
        $permission = Permission::findOrFail($permissionId);
        $permission->delete();

        session()->flash('message', 'Permiso enviado a la papelera exitosamente.');
        $this->resetPage();
    }

    public function restore($permissionId): void
    {
        $permission = Permission::onlyTrashed()->findOrFail($permissionId);
        $permission->restore();

        session()->flash('message', 'Permiso restaurado exitosamente.');
        $this->resetPage();
    }

    public function forceDelete($permissionId): void
    {
        $permission = Permission::onlyTrashed()->findOrFail($permissionId);
        $permission->forceDelete();

        session()->flash('message', 'Permiso eliminado permanentemente.');
        $this->resetPage();
    }

    public function getPermissionsProperty()
    {
        $query = $this->showTrash 
            ? Permission::onlyTrashed() 
            : Permission::query();

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

    public function getTrashCountProperty()
    {
        return Permission::onlyTrashed()->count();
    }

    public function render()
    {
        return view('livewire.admin.permission-management', [
            'permissions' => $this->permissions,
            'trashCount' => $this->trashCount,
        ]);
    }
}
