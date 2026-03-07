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
use Flux\Flux;

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
    public $facturaSeleccionada = null;

    #[Computed]
    public function historialCompras()
    {
        if (!$this->selectedClientId) return collect();

        return Factura::where('cliente_id', $this->selectedClientId)
            ->with(['pagos.medioPago', 'details.product'])
            ->orderBy('fecha_emision', 'desc')
            ->paginate(10, pageName: 'history-page');
    }

    public function verDetalleFactura($id)
    {
        $this->facturaSeleccionada = Factura::with(['details.product', 'pagos.medioPago', 'cliente', 'user'])->find($id);
        Flux::modal('detalle-auditoria-modal')->show();
    }

    public function descargarFactura($id)
    {
        $factura = Factura::with(['user', 'cliente', 'details.product', 'pagos.medioPago'])->findOrFail($id);
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.factura', [
            'factura' => $factura,
        ]);

        return response()->streamDownload(
            fn () => print($pdf->output()),
            "Factura-{$factura->id}.pdf"
        );
    }

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
        
        $pagosPrevios = $this->facturaEnCobro->pagos->sum('monto');
        $pagosNuevos = collect($this->pagos_acumulados)->sum('monto');
        
        $restante = (float)$this->facturaEnCobro->total - $pagosPrevios - $pagosNuevos;
        return round($restante, 2);
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

        // Validamos que el pago nuevo no sea cero
        if (empty($this->pagos_acumulados)) {
            $this->dispatch('notify', message: 'Debe registrar al menos un pago.', variant: 'warning');
            return;
        }

        try {
            DB::transaction(function () {
                // Registramos los nuevos movimientos de caja
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

                // RE-CALCULAMOS EL SALDO TOTAL DESPUÉS DE LOS NUEVOS PAGOS
                $this->facturaEnCobro->refresh(); // Refrescamos los pagos vinculados
                $totalPagadoHistórico = $this->facturaEnCobro->pagos->sum('monto');

                // Si cubrió el 100%, la factura pasa a PAGADO
                if (round($totalPagadoHistórico, 2) >= round($this->facturaEnCobro->total, 2)) {
                    $this->facturaEnCobro->update(['estado' => 'PAGADO']);
                }
            });

            $this->dispatch('notify', message: 'Cobro registrado correctamente.');
            $this->cancelarCobro();
        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Error en el proceso.', variant: 'danger');
        }
    }

    #[Computed]
    public function totalEnLaCalle()
    {
        // Obtenemos todas las facturas pendientes con sus pagos
        $pendientes = Factura::where('estado', 'PENDIENTE')->with('pagos')->get();
        
        return $pendientes->sum(fn($f) => $f->total - $f->pagos->sum('monto'));
    }

    public function render()
    {
        $clientes = Client::search($this->search)
            ->query(function ($query) {
                $query->with(['facturas' => function($q) {
                    $q->where('estado', 'PENDIENTE')->with('pagos');
                }]);
            })
            ->paginate(10);

        // Calculamos el saldo real para cada cliente manualmente antes de enviar a la vista
        $clientes->getCollection()->transform(function ($cliente) {
            $cliente->saldo_real_pendiente = $cliente->facturas->sum(fn($f) => $f->total - $f->pagos->sum('monto'));
            return $cliente;
        });

        return view('livewire.admin.client-debt-manager', [
            'clientes' => $clientes,
            'mediosPago' => \App\Models\MedioPago::all()
        ]);
    }
}