<?php

namespace App\Livewire\Admin;

use App\Models\Group;
use App\Traits\Notifies;
use Flux\Flux;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app', ['title' => 'Gestión de Grupos'])]
class GroupManager extends Component
{
    use Notifies, WithPagination;

    public string $search = '';

    public ?Group $editingGroup = null;

    public array $groupContext = [
        'name' => '',
        'description' => '',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function createGroup()
    {
        $this->reset(['groupContext', 'editingGroup']);
        Flux::modal('group-form')->show();
    }

    public function editGroup(Group $group)
    {
        $this->editingGroup = $group;
        $this->groupContext = [
            'name' => $group->name,
            'description' => $group->description,
        ];
        Flux::modal('group-form')->show();
    }

    public function saveGroup()
    {
        $rules = [
            'groupContext.name' => 'required|string|max:255',
            'groupContext.description' => 'nullable|string',
        ];

        if ($this->editingGroup) {
            $rules['groupContext.name'] .= '|unique:groups,name,'.$this->editingGroup->id;
        } else {
            $rules['groupContext.name'] .= '|unique:groups,name';
        }

        $this->validate($rules);

        if ($this->editingGroup) {
            $this->editingGroup->update($this->groupContext);
        } else {
            Group::create($this->groupContext);
        }

        Cache::forget('groups_all');
        Flux::modal('group-form')->close();
        $this->reset(['groupContext', 'editingGroup']);
        $this->notify('Grupo guardado exitosamente.', 'success');
    }

    public function confirmDeactivate(Group $group)
    {
        $this->editingGroup = $group;
        Flux::modal('confirm-deactivation-group')->show();
    }

    public function deactivateGroup()
    {
        if ($this->editingGroup) {
            $this->editingGroup->delete(); // Soft delete
            Cache::forget('groups_all');
            Flux::modal('confirm-deactivation-group')->close();
            $this->reset(['editingGroup']);
            $this->notify('Grupo desactivado con éxito.', 'success');
        }
    }

    public function render()
    {
        $groups = Group::search($this->search)
            ->paginate(12);

        return view('livewire.admin.group-manager', [
            'groups' => $groups,
        ]);
    }
}
