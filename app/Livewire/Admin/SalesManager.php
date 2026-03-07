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

    #[Computed]
    public function ventas()
    {
        return Factura::with(['user', 'cliente', 'pagos.medioPago'])
            ->when($this->search, function($q) {
                $q->whereHas('user', fn($u) => $u->where('name', 'like', "%{$this->search}%"));
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