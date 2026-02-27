<?php

namespace App\Livewire\User;

use App\Models\Caja;
use App\Models\MedioPago;
use App\Models\MovimientoCaja;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Flux\Flux;

#[Layout('components.layouts.app', ['title' => 'User Dashboard'])]
class Dashboard extends Component
{
    public $monto_inicial = '';
    
    // Propiedades para registro de movimientos manuales
    public $movimiento_monto = '';
    public $movimiento_motivo = '';
    public $movimiento_medio_pago = '';
    public $movimiento_tipo = 'INGRESO'; // valor por defecto

    #[Computed]
    public function cajaAbierta()
    {
        return Caja::where('user_id', Auth::id())
            ->whereNull('fecha_cierre')
            ->first();
    }

    #[Computed]
    public function saldoActual()
    {
        $caja = $this->cajaAbierta;
        if (!$caja) return 0;

        $ingresos = $caja->movimientos()->where('tipo_movimiento', 'INGRESO')->sum('monto');
        $egresos = $caja->movimientos()->where('tipo_movimiento', 'EGRESO')->sum('monto');

        return $caja->monto_inicial + $ingresos - $egresos;
    }

    #[Computed]
    public function mediosPago()
    {
        return MedioPago::all();
    }

    public function abrirCaja()
    {
        $this->validate([
            'monto_inicial' => 'required|numeric|min:0',
        ]);

        if ($this->cajaAbierta) {
            $this->addError('caja_status', 'Ya tienes una caja abierta.');
            return;
        }

        Caja::create([
            'fecha_apertura' => now(),
            'monto_inicial' => $this->monto_inicial,
            'user_id' => Auth::id(),
        ]);

        $this->reset('monto_inicial');
        Flux::modal('abrir-caja-form')->close();
    }

    public function registrarMovimiento()
    {
        $this->validate([
            'movimiento_monto' => 'required|numeric|min:0.01',
            'movimiento_motivo' => 'required|string|max:255',
            'movimiento_medio_pago' => 'required|exists:medio_pagos,id',
            'movimiento_tipo' => 'required|in:INGRESO,EGRESO',
        ]);

        $caja = $this->cajaAbierta;

        if (!$caja) {
            $this->addError('movimiento_status', 'No tienes una caja abierta para registrar movimientos.');
            return;
        }

        MovimientoCaja::create([
            'tipo_movimiento' => $this->movimiento_tipo,
            'monto' => $this->movimiento_monto,
            'fecha_movimiento' => now(),
            'motivo' => $this->movimiento_motivo,
            'id_medio_pago' => $this->movimiento_medio_pago,
            'id_caja' => $caja->id,
            'user_id' => Auth::id(),
        ]);

        $this->reset(['movimiento_monto', 'movimiento_motivo', 'movimiento_medio_pago', 'movimiento_tipo']);
        Flux::modal('registro-movimiento-form')->close();
    }

    public function cerrarMiTurno()
    {
        $caja = $this->cajaAbierta;

        if ($caja) {
            $caja->update([
                'fecha_cierre' => now(),
                'monto_final' => $this->saldoActual,
            ]);

            Flux::modal('confirm-close-caja')->close();
        }
    }

    public function render()
    {
        return view('livewire.user.dashboard', [
            'user' => Auth::user(),
        ]);
    }
}
