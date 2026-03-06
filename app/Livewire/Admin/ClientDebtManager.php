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
    public $selectedClientId;
    public $modalTab = 'pendientes';
    public $medio_pago_id = '';
    public $facturaEnCobro = null;
    public $pagos_acumulados = [];
    public $monto_pago_actual = 0;

    public function seleccionarFacturaParaCobro($facturaId)
    {
        $this->facturaEnCobro = Factura::findOrFail($facturaId);
        $this->pagos_acumulados = [];
        $this->reset(['medio_pago_id', 'monto_pago_actual']);
    }

    public function cancelarCobro()
    {
        $this->facturaEnCobro = null;
        $this->pagos_acumulados = [];
    }

    #[Computed]
    public function montoRestanteFactura()
    {
        if (!$this->facturaEnCobro) return 0;
        $totalPagado = collect($this->pagos_acumulados)->sum('monto');
        return round((float)$this->facturaEnCobro->total - $totalPagado, 2);
    }

    public function autocompletarMonto()
    {
        $this->monto_pago_actual = round($this->montoRestanteFactura, 2);
    }

    public function agregarPago()
    {
        $this->validate([
            'medio_pago_id' => 'required|exists:medio_pagos,id',
            'monto_pago_actual' => 'required|numeric|min:0.01|max:' . ($this->montoRestanteFactura + 0.01),
        ]);

        $medio = \App\Models\MedioPago::find($this->medio_pago_id);

        $this->pagos_acumulados[] = [
            'medio_id' => $this->medio_pago_id,
            'monto' => (float) $this->monto_pago_actual,
            'nombre' => $medio->nombre,
        ];

        $this->reset(['medio_pago_id', 'monto_pago_actual']);
        unset($this->montoRestanteFactura);
    }

    public function quitarPago($index)
    {
        unset($this->pagos_acumulados[$index]);
        $this->pagos_acumulados = array_values($this->pagos_acumulados);
    }

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

    public function cobrarFactura()
    {
        if (!$this->cajaActiva) {
            $this->dispatch('notify', message: 'Error: Abre tu caja antes.', variant: 'danger');
            return;
        }

        if ($this->montoRestanteFactura > 0.01) {
            $this->dispatch('notify', message: 'Debe cubrir el total de la factura.', variant: 'warning');
            return;
        }

        try {
            DB::transaction(function () {
                $this->facturaEnCobro->update(['estado' => 'PAGADO']);

                foreach ($this->pagos_acumulados as $pago) {
                    MovimientoCaja::create([
                        'tipo_movimiento'  => 'INGRESO',
                        'monto'            => $pago['monto'],
                        'motivo'           => "Cobro Cta. Cte. #{$this->facturaEnCobro->id} - {$pago['nombre']}",
                        'fecha_movimiento' => now(),
                        'id_medio_pago'    => $pago['medio_id'],
                        'id_caja'          => $this->cajaActiva->id,
                        'user_id'          => Auth::id(),
                        'factura_id'       => $this->facturaEnCobro->id,
                    ]);
                }
            });

            $this->dispatch('notify', message: 'Cobro multimedio exitoso.');
            $this->cancelarCobro();
            
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
            'clientes' => $clientes,
            'mediosPago' => \App\Models\MedioPago::all()
        ]);
    }
}