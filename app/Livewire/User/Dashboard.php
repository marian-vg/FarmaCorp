<?php

namespace App\Livewire\User;

use App\Models\Caja;
use App\Models\MedioPago;
use App\Models\MovimientoCaja;
use Barryvdh\DomPDF\Facade\Pdf;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\Notifies;

#[Layout('components.layouts.app', ['title' => 'User Dashboard'])]
class Dashboard extends Component
{
    use WithPagination, Notifies;

    public $monto_inicial = '';

    // Propiedades para registro de movimientos manuales
    public $movimiento_monto = '';

    public $movimiento_motivo = '';

    public $movimiento_medio_pago = '';

    public $movimiento_tipo = 'INGRESO'; // valor por defecto

    public $tabActiva = 'gestion'; // Soporte para pestañas manuales

    // Propiedad para justificación del cierre (Fase 7)
    public $observaciones_cierre = '';

    #[Computed]
    public function cajaAbierta()
    {
        return Caja::where('user_id', Auth::id())
            ->whereNull('fecha_cierre')
            ->first();
    }

    // Novedad Fase 7: Listar el Historial de las cajas del propio empleado
    #[Computed]
    public function historialCajas()
    {
        return Caja::where('user_id', Auth::id())
            ->whereNotNull('fecha_cierre')
            ->orderBy('fecha_cierre', 'desc')
            ->paginate(10);
    }

    #[Computed]
    public function saldoActual()
    {
        $caja = $this->cajaAbierta;
        if (! $caja) {
            return 0;
        }

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

        if (! $caja) {
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
        $this->validate([
            'observaciones_cierre' => 'nullable|string|max:1000',
        ]);

        $caja = $this->cajaAbierta;

        if ($caja) {
            $caja->update([
                'fecha_cierre' => now(),
                'monto_final' => $this->saldoActual,
                'observaciones' => $this->observaciones_cierre,
            ]);

            // INVALIDAR LAS PROPIEDADES COMPUTED PARA FORZAR RE-RENDERIZADO DE LA VISTA
            unset($this->cajaAbierta);
            unset($this->saldoActual);
            unset($this->totalesPorMedio);
            unset($this->historialCajas);

            Flux::modal('confirm-close-caja')->close();
            $this->reset('observaciones_cierre');
            $this->notify('Has cerrado tu turno correctamente.', 'success');
        }
    }

    public function render()
    {
        return view('livewire.user.dashboard', [
            'user' => Auth::user(),
        ]);
    }

    #[Computed]
    public function totalesPorMedio()
    {
        $caja = $this->cajaAbierta;
        if (! $caja) {
            return collect();
        }

        // Agrupamos los movimientos por el nombre del medio de pago
        return $caja->movimientos()
            ->with('medioPago')
            ->get()
            ->groupBy('medioPago.nombre')
            ->map(function ($movimientos) {
                $ingresos = $movimientos->where('tipo_movimiento', 'INGRESO')->sum('monto');
                $egresos = $movimientos->where('tipo_movimiento', 'EGRESO')->sum('monto');

                return $ingresos - $egresos;
            });
    }

    // Fase 7: Reporte en PDF (RF-07) para el Empleado
    public function descargarReporte($id)
    {
        // Validamos estrictamente que la caja le pertenezca a Auth::id()
        $caja = Caja::where('user_id', Auth::id())
            ->with(['user', 'movimientos.medioPago'])
            ->findOrFail($id);

        if (! $caja->fecha_cierre) {
            $this->notify('Solo puedes generar reportes de turnos finalizados.', 'error');

            return;
        }

        $totalesMp = [];
        foreach ($caja->movimientos->groupBy('medioPago.nombre') as $nombre => $movs) {
            $ingresos = $movs->where('tipo_movimiento', 'INGRESO')->sum('monto');
            $egresos = $movs->where('tipo_movimiento', 'EGRESO')->sum('monto');
            $totalesMp[$nombre] = [
                'ingresos' => $ingresos,
                'egresos' => $egresos,
                'neto' => $ingresos - $egresos,
            ];
        }

        $pdf = Pdf::loadView('pdf.reporte-caja', [
            'caja' => $caja,
            'totales' => $totalesMp,
        ]);

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            "Mi-Cierre-Caja-{$caja->id}.pdf"
        );
    }
}
