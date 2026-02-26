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
        abort_unless(auth()->user()->hasRole('admin'), 403);
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
        abort_unless(auth()->user()->hasRole('admin'), 403);
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
        abort_unless(auth()->user()->hasRole('admin'), 403);

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
    }

    public function confirmDeactivate(Client $client)
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);
        $this->editingClient = $client;
        Flux::modal('confirm-deactivation-client')->show();
    }

    public function deactivateClient()
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);
        if ($this->editingClient) {
            $this->editingClient->update(['is_active' => false]);
            Flux::modal('confirm-deactivation-client')->close();
            $this->reset(['editingClient']);
        }
    }

    public function reactivateClient(Client $client)
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);
        $client->update(['is_active' => true]);
    }

    public function render()
    {
        $query = Client::query();

        if ($this->search) {
            $searchTerm = '%'.mb_strtolower($this->search).'%';
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

        return view('livewire.clients.client-manager', [
            'clients' => $clients,
        ]);
    }
}
