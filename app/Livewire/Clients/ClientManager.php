<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app', ['title' => 'Gestión de Clientes'])]
class ClientManager extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'all';

    public ?Client $editingClient = null;

    public array $clientContext = [
        'first_name' => '',
        'last_name' => '',
        'email' => '',
        'phone' => '',
        'address' => '',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function createClient()
    {
        abort_unless(auth()->user()->hasAnyRole(['admin', 'empleado']), 403);
        $this->reset(['clientContext', 'editingClient']);
        Flux::modal('client-form')->show();
    }

    public function viewClient(Client $client)
    {
        $this->editingClient = $client;
        $this->clientContext = [
            'first_name' => $client->first_name,
            'last_name' => $client->last_name,
            'email' => $client->email,
            'phone' => $client->phone,
            'address' => $client->address,
        ];
        Flux::modal('view-client-modal')->show();
    }

    public function editClient(Client $client)
    {
        abort_unless(auth()->user()->hasAnyRole(['admin', 'empleado']), 403);
        $this->editingClient = $client;
        $this->clientContext = [
            'first_name' => $client->first_name,
            'last_name' => $client->last_name,
            'email' => $client->email,
            'phone' => $client->phone,
            'address' => $client->address,
        ];
        Flux::modal('client-form')->show();
    }

    public function saveClient()
    {
        abort_unless(auth()->user()->hasAnyRole(['admin', 'empleado']), 403);

        $rules = [
            'clientContext.first_name' => 'required|string|max:255',
            'clientContext.last_name' => 'required|string|max:255',
            'clientContext.email' => 'nullable|email|max:255|unique:clients,email'.($this->editingClient ? ','.$this->editingClient->id : ''),
            'clientContext.phone' => 'required|string|max:255',
            'clientContext.address' => 'required|string|max:255',
        ];

        $this->validate($rules);

        if ($this->editingClient) {
            $this->editingClient->update($this->clientContext);
        } else {
            Client::create($this->clientContext);
        }

        Flux::modal('client-form')->close();
        $this->reset(['clientContext', 'editingClient']);
        $this->dispatch('notify', message: 'Cliente guardado exitosamente.', type: 'success');
    }

    public function confirmDeactivate(Client $client)
    {
        abort_unless(auth()->user()->hasAnyRole(['admin', 'empleado']), 403);
        $this->editingClient = $client;
        Flux::modal('confirm-deactivation-client')->show();
    }

    public function deactivateClient()
    {
        abort_unless(auth()->user()->hasAnyRole(['admin', 'empleado']), 403);
        if ($this->editingClient) {
            $this->editingClient->update(['is_active' => false]);
            Flux::modal('confirm-deactivation-client')->close();
            $this->reset(['editingClient']);
            $this->dispatch('notify', message: 'Cliente desactivado con éxito.', type: 'success');
        }
    }

    public function reactivateClient(Client $client)
    {
        abort_unless(auth()->user()->hasAnyRole(['admin', 'empleado']), 403);
        $client->update(['is_active' => true]);
        $this->dispatch('notify', message: 'Cliente reactivado exitosamente.', type: 'success');
    }

    public function render()
    {
        $clients = Client::search($this->search)
            ->query(function ($query) {
                if ($this->statusFilter === 'active') {
                    $query->where('is_active', true);
                } elseif ($this->statusFilter === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->paginate(12);

        return view('livewire.clients.client-manager', [
            'clients' => $clients,
        ]);
    }
}
