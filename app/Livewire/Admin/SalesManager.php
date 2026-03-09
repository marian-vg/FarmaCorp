<?php

namespace App\Livewire\Admin;

use App\Models\Factura;
use App\Models\FacturaDetalle;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Flux\Flux;

#[Layout('components.layouts.app', ['title' => 'Historial de Ventas'])]
class SalesManager extends Component
{
    use WithPagination;

    public string $search = '';
    public $ventaSeleccionada = null;
    public $filtroCliente = '';
    public $filtroTipo = '';
    public $fechaInicio = '';
    public $fechaFin = '';

    public function updatedFiltroCliente() { $this->resetPage(); }
    public function updatedFiltroTipo() { $this->resetPage(); }
    public function updatedFechaInicio() { $this->resetPage(); }
    public function updatedFechaFin() { $this->resetPage(); }

    public function limpiarFiltros()
    {
        $this->reset(['filtroCliente', 'filtroTipo', 'fechaInicio', 'fechaFin', 'search']);
    }

    /**
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    #[Computed]
    public function ventas()
    {
        return Factura::with(['user', 'cliente', 'pagos.medioPago'])
        // Filtro por responsable (Buscador actual)
        ->when($this->search, function($q) {
            $q->whereHas('user', fn($u) => $u->where('name', 'like', "%{$this->search}%"));
        })
        // RF-25: Filtro por Cliente
        ->when($this->filtroCliente, function($q) {
            $q->where('cliente_id', $this->filtroCliente);
        })
        // RF-25: Filtro por Tipo de Comprobante
        ->when($this->filtroTipo, function($q) {
            $q->where('tipo_comprobante', $this->filtroTipo);
        })
        // RF-25: Filtro por Rango de Fechas
        ->when($this->fechaInicio, function($q) {
            $q->whereDate('fecha_emision', '>=', $this->fechaInicio);
        })
        ->when($this->fechaFin, function($q) {
            $q->whereDate('fecha_emision', '<=', $this->fechaFin);
        })
        ->orderBy('fecha_emision', 'desc')
        ->paginate(15);
    }

    public function descargarFactura($id)
    {
        $factura = Factura::with(['user', 'cliente', 'details.product', 'pagos.medioPago'])->findOrFail($id);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.factura', [
            'factura' => $factura,
        ]);

        return response()->streamDownload(
            fn () => print($pdf->output()),
            "Factura-Admin-{$factura->id}.pdf"
        );
    }

    #[Computed]
    public function clientes()
    {
        return \App\Models\Client::orderBy('first_name')->get();
    }

    public function verDetalle($id)
    {
        $this->ventaSeleccionada = Factura::with('details.product')->find($id);
        Flux::modal('detalle-venta-modal')->show();
    }

    public function render()
    {
        return view('livewire.admin.sales-manager');
    }
}