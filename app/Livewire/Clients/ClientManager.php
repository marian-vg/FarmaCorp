<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Models\Factura;
use App\Models\ObraSocial;
use App\Traits\Notifies;
use Barryvdh\DomPDF\Facade\Pdf;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app', ['title' => 'Gestión de Clientes'])]
class ClientManager extends Component
{
    use Notifies, WithPagination;

    public string $search = '';

    public string $statusFilter = 'all';

    public ?Client $editingClient = null;

    public string $modalTab = 'info';

    public $selectedClientId = null;

    public $facturaSeleccionada = null;

    public array $clientContext = [
        'first_name' => '',
        'last_name' => '',
        'email' => '',
        'phone' => '',
        'address' => '',
    ];

    public $selected_os_id = null;
    public $affiliate_number = '';

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
        $this->authorize('clientes.crear_editar');
        $this->reset(['clientContext', 'editingClient', 'selectedClientId']);
        Flux::modal('client-form')->show();
    }

    public function viewClient(Client $client)
    {
        $this->selectedClientId = $client->id;
        $this->editingClient = $client;
        $this->clientContext = [
            'first_name' => $client->first_name,
            'last_name' => $client->last_name,
            'email' => $client->email,
            'phone' => $client->phone,
            'address' => $client->address,
        ];
        $this->modalTab = 'info'; // Siempre empezar en info
        Flux::modal('view-client-modal')->show();
    }

    #[Computed]
    public function historialCompras()
    {
        if (! $this->selectedClientId) {
            return collect();
        }

        return Factura::where('cliente_id', $this->selectedClientId)
            ->with(['user', 'pagos.medioPago'])
            ->orderBy('fecha_emision', 'desc')
            ->get();
    }

    public function verDetalleFactura($id)
    {
        $this->facturaSeleccionada = Factura::with(['details.product', 'pagos.medioPago', 'cliente', 'user'])->find($id);
        Flux::modal('detalle-auditoria-modal')->show();
    }

    public function descargarFactura($id)
    {
        $factura = Factura::with(['user', 'cliente', 'details.product', 'pagos.medioPago'])->findOrFail($id);

        $pdf = Pdf::loadView('pdf.factura', [
            'factura' => $factura,
        ]);

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            "Factura-{$factura->id}.pdf"
        );
    }

    public function editClient(Client $client)
    {
        $this->authorize('clientes.crear_editar');
        $this->editingClient = $client;
        $this->clientContext = [
            'first_name' => $client->first_name,
            'last_name' => $client->last_name,
            'email' => $client->email,
            'phone' => $client->phone,
            'address' => $client->address,
        ];

        $osVinculada = $client->obrasSociales()->first();
        if ($osVinculada) {
            $this->selected_os_id = $osVinculada->id;
            $this->affiliate_number = $osVinculada->pivot->affiliate_number;
        } else {
            $this->reset(['selected_os_id', 'affiliate_number']);
        }

        Flux::modal('client-form')->show();
    }

    public function saveClient()
    {
        $this->authorize('clientes.crear_editar');

        $rules = [
            'clientContext.first_name' => 'required|string|max:255',
            'clientContext.last_name' => 'required|string|max:255',
            'clientContext.email' => 'nullable|email|max:255|unique:clients,email'.($this->editingClient ? ','.$this->editingClient->id : ''),
            'clientContext.phone' => 'required|string|max:255',
            'clientContext.address' => 'required|string|max:255',
            'selected_os_id' => 'nullable|exists:obras_sociales,id',
            'affiliate_number' => 'nullable|required_with:selected_os_id|string|max:50',
        ];

        $this->validate($rules);

        if ($this->editingClient) {
            $this->editingClient->update($this->clientContext);
            $cliente = $this->editingClient;
        } else {
            $cliente = Client::create($this->clientContext);
        }

        // --- NUEVA LÓGICA DE VINCULACIÓN ---
        if ($this->selected_os_id) {
            // Sincronizamos: si ya tenía una, la reemplaza por esta con su número
            $cliente->obrasSociales()->sync([
                $this->selected_os_id => ['affiliate_number' => $this->affiliate_number]
            ]);
        } else {
            // Si el select está vacío, quitamos cualquier vinculación
            $cliente->obrasSociales()->detach();
        }

        Flux::modal('client-form')->close();
        $this->reset(['clientContext', 'editingClient', 'selected_os_id', 'affiliate_number']);
        $this->notify('Cliente guardado exitosamente.', 'success');
    }

    public function confirmDeactivate(Client $client)
    {
        $this->authorize('clientes.desactivar');
        $this->editingClient = $client;
        Flux::modal('confirm-deactivation-client')->show();
    }

    public function deactivateClient()
    {
        $this->authorize('clientes.desactivar');
        if ($this->editingClient) {
            $this->editingClient->update(['is_active' => false]);
            Flux::modal('confirm-deactivation-client')->close();
            $this->reset(['editingClient']);
            $this->notify('Cliente desactivado con éxito.', 'success');
        }
    }

    public function reactivateClient(Client $client)
    {
        $this->authorize('clientes.desactivar');
        $client->update(['is_active' => true]);
        $this->notify('Cliente reactivado exitosamente.', 'success');
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
