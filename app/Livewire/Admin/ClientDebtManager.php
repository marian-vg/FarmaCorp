<?php

namespace App\Livewire\Admin;

use App\Models\Client;
use App\Models\Factura;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;

#[Layout('components.layouts.app', ['title' => 'Saldos de Cuentas Corrientes'])]
class ClientDebtManager extends Component
{
    use WithPagination;

    public $search = '';

    // RF-16: Calculamos el saldo total "en la calle" sumando todas las facturas PENDIENTES
    #[Computed]
    public function totalEnLaCalle()
    {
        return Factura::where('estado', 'PENDIENTE')->sum('total');
    }

    public function render()
    {
        // RF-08: Buscamos clientes y sumamos sus facturas pendientes [cite: 115]
        $clientes = Client::search($this->search)
            ->query(function ($query) {
                $query->withSum(['facturas as saldo_pendiente' => function ($q) {
                    $q->where('estado', 'PENDIENTE'); // RF-12 [cite: 120]
                }], 'total');
            })
            ->paginate(10);

        return view('livewire.admin.client-debt-manager', [
            'clientes' => $clientes
        ]);
    }
}