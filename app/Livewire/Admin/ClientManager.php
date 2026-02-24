<?php

namespace App\Livewire\Admin;

use App\Models\Client;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Flux\Flux;

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
        $this->reset(['clientContext', 'editingClient']);
        Flux::modal('client-form')->show();
    }

    public function editClient(Client $client)
    {
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
        $rules = [
            'clientContext.first_name' => 'required|string|max:255',
            'clientContext.last_name' => 'required|string|max:255',
            'clientContext.email' => 'nullable|email|max:255|unique:clients,email' . ($this->editingClient ? ',' . $this->editingClient->id : ''),
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
    }

    public function confirmDeactivate(Client $client)
    {
        $this->editingClient = $client;
        Flux::modal('confirm-deactivation-client')->show();
    }

    public function deactivateClient()
    {
        if ($this->editingClient) {
            $this->editingClient->update(['is_active' => false]);
            Flux::modal('confirm-deactivation-client')->close();
            $this->reset(['editingClient']);
        }
    }

    public function reactivateClient(Client $client)
    {
        $client->update(['is_active' => true]);
    }

    public function render()
    {
        $query = Client::query();

        if ($this->search) {
            $searchTerm = '%' . mb_strtolower($this->search) . '%';
            $query->where(function ($q) use ($searchTerm) {
                // Compatible con testing en SQLite también si es el caso
                $q->whereRaw('LOWER(first_name) LIKE ?', [$searchTerm])
                  ->orWhereRaw('LOWER(last_name) LIKE ?', [$searchTerm])
                  ->orWhereRaw('LOWER(phone) LIKE ?', [$searchTerm]);
            });
        }

        if ($this->statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        $clients = $query->paginate(15);

        return view('livewire.admin.client-manager', [
            'clients' => $clients,
        ]);
    }
}
