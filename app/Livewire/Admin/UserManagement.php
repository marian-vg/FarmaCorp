<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.app', ['title' => 'Gestión de Usuarios'])]
class UserManagement extends Component
{
    use WithPagination;

    // Estados del componente
    public $showTrash = false;
    public $search = '';
    public $roleFilter = '';
    public $statusFilter = '';
    
    // Estados del formulario
    public $editingId = null;
    public $showModal = false;
    public $name = '';
    public $email = '';
    public $password = '';
    public $selectedRole = '';
    public $isActive = true;

    protected $paginationTheme = 'tailwind';

    public function mount(): void
    {
        $this->resetFilters();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->roleFilter = '';
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

    public function openEditModal($userId): void
    {
        $user = User::withTrashed()->findOrFail($userId);
        
        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->selectedRole = $user->roles->first()?->name ?? '';
        $this->isActive = $user->is_active;
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
        $this->email = '';
        $this->password = '';
        $this->selectedRole = '';
        $this->isActive = true;
    }

    public function save(): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($this->editingId),
            ],
            'selectedRole' => ['required', 'string', 'exists:roles,name'],
        ];

        if ($this->editingId === null) {
            $rules['password'] = ['required', 'string', 'min:8'];
        } else {
            $rules['password'] = ['nullable', 'string', 'min:8'];
        }

        $validated = $this->validate($rules);

        if ($this->editingId === null) {
            // Crear nuevo usuario
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'is_active' => $this->isActive,
                'created_by' => Auth::id(),
            ]);

            // Asignar rol usando Spatie
            if ($validated['selectedRole']) {
                $role = Role::findByName($validated['selectedRole'], 'web');
                $user->syncRoles([$role]);
            }

            session()->flash('message', 'Usuario creado exitosamente.');
        } else {
            // Actualizar usuario existente
            $user = User::findOrFail($this->editingId);

        // Protección: No permitir modificar el propio usuario autenticado
        if ($user->id === Auth::id()) {
                session()->flash('error', 'No puedes modificar tu propia cuenta desde aquí.');
                return;
            }

            $user->name = $validated['name'];
            $user->email = $validated['email'];
            
            if (!empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }

            // Solo permitir cambiar is_active si no es el usuario autenticado
            if ($user->id !== Auth::id()) {
                $user->is_active = $this->isActive;
            }

            $user->save();

            // Sincronizar roles usando Spatie
            if ($validated['selectedRole']) {
                $role = Role::findByName($validated['selectedRole'], 'web');
                $user->syncRoles([$role]);
            }

            session()->flash('message', 'Usuario actualizado exitosamente.');
        }

        $this->closeModal();
        $this->resetPage();
    }

    public function toggleActive($userId): void
    {
        $user = User::findOrFail($userId);

        // Protección: No permitir desactivar el propio usuario
        if ($user->id === Auth::id()) {
            session()->flash('error', 'No puedes desactivar tu propia cuenta.');
            return;
        }

        $user->is_active = !$user->is_active;
        $user->save();

        session()->flash('message', $user->is_active 
            ? 'Usuario activado exitosamente.' 
            : 'Usuario desactivado exitosamente.');
    }

    public function delete($userId): void
    {
        $user = User::findOrFail($userId);

        // Protección: No permitir eliminar el propio usuario
        if ($user->id === Auth::id()) {
            session()->flash('error', 'No puedes eliminarte a ti mismo.');
            return;
        }

        $user->delete();

        session()->flash('message', 'Usuario enviado a la papelera exitosamente.');
        $this->resetPage();
    }

    public function restore($userId): void
    {
        $user = User::onlyTrashed()->findOrFail($userId);
        $user->restore();

        session()->flash('message', 'Usuario restaurado exitosamente.');
        $this->resetPage();
    }

    public function forceDelete($userId): void
    {
        $user = User::onlyTrashed()->findOrFail($userId);

        // Protección: No permitir eliminar permanentemente el propio usuario
        if ($user->id === Auth::id()) {
            session()->flash('error', 'No puedes eliminarte a ti mismo permanentemente.');
            return;
        }

        $user->forceDelete();

        session()->flash('message', 'Usuario eliminado permanentemente.');
        $this->resetPage();
    }

    public function getUsersProperty()
    {
        $query = $this->showTrash 
            ? User::onlyTrashed() 
            : User::query();

        // Búsqueda por nombre o email
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        // Filtro por rol (usando Spatie)
        if ($this->roleFilter) {
            $query->role($this->roleFilter);
        }

        // Filtro por estado activo/inactivo
        if ($this->statusFilter !== '') {
            $query->where('is_active', $this->statusFilter === 'active');
        }

        return $query->with(['creator', 'roles'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function getRolesProperty()
    {
        return Role::where('guard_name', 'web')->get();
    }

    public function render()
    {
        return view('livewire.admin.user-management', [
            'users' => $this->users,
            'roles' => $this->roles,
        ]);
    }

    public function getTrashCountProperty()
    {
    return User::onlyTrashed()->count();
    }
}
