<?php

namespace App\Livewire\Admin;

use App\Traits\Notifies;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;

#[Layout('components.layouts.app', ['title' => 'Gestión de Permisos'])]
class PermissionManager extends Component
{
    use Notifies, WithPagination;

    public string $search = '';

    public ?Permission $editingPermission = null;

    public array $permissionContext = [
        'name' => '',
        'description' => '',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

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
            'description' => $permission->description ?? '',
        ];
        Flux::modal('permission-form')->show();
    }

    public function savePermission()
    {
        $this->validate([
            'permissionContext.name' => 'required|string|max:255|unique:permissions,name'.($this->editingPermission ? ','.$this->editingPermission->id : ''),
            'permissionContext.description' => 'nullable|string|max:500',
        ]);

        if ($this->editingPermission) {
            $this->editingPermission->update([
                'name' => $this->permissionContext['name'],
                'description' => $this->permissionContext['description'],
            ]);
        } else {
            Permission::create([
                'name' => $this->permissionContext['name'],
                'description' => $this->permissionContext['description'],
                'guard_name' => 'web',
            ]);
        }

        Flux::modal('permission-form')->close();
        $this->reset(['permissionContext', 'editingPermission']);
        $this->notify('Permiso guardado exitosamente.', 'success');
    }

    public function confirmDelete(Permission $permission)
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
            $this->notify('Permiso eliminado.', 'success');
        }
    }

    public function render()
    {
        $query = Permission::query();

        if ($this->search) {
            $query->where('name', 'ilike', '%'.$this->search.'%')
                ->orWhere('description', 'ilike', '%'.$this->search.'%');
        }

        return view('livewire.admin.permission-manager', [
            'permissions' => $query->paginate(15),
        ]);
    }
}
