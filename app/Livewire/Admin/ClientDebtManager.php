<?php

namespace App\Livewire\Admin;

use App\Models\Client;
use App\Models\Factura;
use App\Models\Caja;
use App\Models\MovimientoCaja;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;

#[Layout('components.layouts.app', ['title' => 'Saldos de Cuentas Corrientes'])]
class ClientDebtManager extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedClientId; // Guardamos el ID del cliente seleccionado
    public $modalTab = 'pendientes';

    #[Computed]
    public function cajaActiva()
    {
        return Caja::where('user_id', Auth::id())->whereNull('fecha_cierre')->first();
    }

    // RF-16: Propiedad Computada para Deudas Pendientes
    // Se recarga sola cada vez que el componente se actualiza
    #[Computed]
    public function facturasPendientes()
    {
        if (!$this->selectedClientId) return collect();

        return Factura::where('cliente_id', $this->selectedClientId)
            ->where('estado', 'PENDIENTE')
            ->orderBy('fecha_emision', 'desc')
            ->get();
    }

    // RF-24: Propiedad Computada para Historial de Pagos
    // ¡Aquí está la solución! Siempre estará disponible si hay un cliente seleccionado
    #[Computed]
    public function facturasPagadas()
    {
        if (!$this->selectedClientId) return collect();

        return Factura::where('cliente_id', $this->selectedClientId)
            ->where('estado', 'PAGADO')
            ->orderBy('updated_at', 'desc')
            ->take(15)
            ->get();
    }

    public function verDetalleDeuda($clientId)
    {
        $this->selectedClientId = $clientId;
        $this->modalTab = 'pendientes';
        \Flux::modal('cobro-modal')->show();
    }

    public function cobrarFactura($facturaId)
    {
        if (!$this->cajaActiva) {
            $this->dispatch('notify', message: 'Error: Abre tu caja antes de cobrar.', variant: 'danger');
            return;
        }

        try {
            DB::transaction(function () use ($facturaId) {
                $factura = Factura::findOrFail($facturaId);
                $factura->update(['estado' => 'PAGADO']);

                MovimientoCaja::create([
                    'tipo_movimiento'  => 'INGRESO',
                    'monto'            => $factura->total,
                    'motivo'           => "Cobro Cta. Cte. Factura #" . str_pad($factura->id, 6, '0', STR_PAD_LEFT),
                    'fecha_movimiento' => now(),
                    'id_medio_pago'    => 1, 
                    'id_caja'          => $this->cajaActiva->id,
                    'user_id'          => Auth::id(),
                ]);
            });

            $this->dispatch('notify', message: 'Cobro exitoso.');
            
            // Si ya no quedan deudas, podemos cerrar el modal o dejar que vea el historial
            if ($this->facturasPendientes->isEmpty()) {
                $this->modalTab = 'historial';
            }

        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Error en el proceso.', variant: 'danger');
        }
    }

    #[Computed]
    public function totalEnLaCalle()
    {
        return Factura::where('estado', 'PENDIENTE')->sum('total');
    }

    public function render()
    {
        $clientes = Client::search($this->search)
            ->query(function ($query) {
                $query->withSum(['facturas as saldo_pendiente' => function ($q) {
                    $q->where('estado', 'PENDIENTE');
                }], 'total');
            })
            ->paginate(10);

        return view('livewire.admin.client-debt-manager', [
            'clientes' => $clientes
        ]);
    }
}