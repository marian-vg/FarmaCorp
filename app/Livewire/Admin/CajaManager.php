<?php

namespace App\Livewire\Admin;

use App\Models\Caja;
use App\Models\MedioPago; // Importamos User
use App\Models\MovimientoCaja;
use App\Models\User;
use App\Traits\Notifies; // Para los datos reactivos
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class CajaManager extends Component
{
    use Notifies, WithPagination;

    public $monto_inicial = '';

    public $user_id = ''; // Nueva propiedad para elegir el usuario

    // Propiedades para registro de movimientos administrativos
    public $movimiento_monto = '';

    public $movimiento_motivo = '';

    public $movimiento_medio_pago = '';

    public $movimiento_tipo = ''; // INGRESO o EGRESO

    // Propiedad para justificación del cierre (Fase 7)
    public $observaciones_cierre = '';

    // Propiedades para Filtros Paginados (RF-04)
    public string $search = '';

    public string $filtro_usuario = '';

    public string $fecha_desde = '';

    public string $fecha_hasta = '';

    public string $tabActiva = 'gestion'; // Soporte UI para Tabs

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFiltroUsuario()
    {
        $this->resetPage();
    }

    public function updatedFechaDesde()
    {
        $this->resetPage();
    }

    public function updatedFechaHasta()
    {
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->reset(['search', 'filtro_usuario', 'fecha_desde', 'fecha_hasta']);
        $this->resetPage();
    }

    // 1. OBTENER CAJAS ABIERTAS PARA LA PESTAÑA DE GESTIÓN
    #[Computed]
    public function cajas()
    {
        return Caja::with('user')
            ->whereNull('fecha_cierre') // SÓLO ABIERTAS
            ->orderBy('fecha_apertura', 'desc')
            ->get(); // Quitamos paginación aquí para más simpleza operativa
    }

    // 1.1 OBTENER CAJAS CERRADAS PARA EL HISTORIAL (Paginado y Filtrado)
    /**
     * @return LengthAwarePaginator
     */
    #[Computed]
    public function historialCajas()
    {
        return Caja::search($this->search)
            ->query(function ($query) {
                $query->join('users', 'cajas.user_id', '=', 'users.id')
                    ->select('cajas.*')
                    ->with('user')
                    ->whereNotNull('fecha_cierre') // SÓLO CERRADAS
                    ->when($this->filtro_usuario, function ($q) {
                        $q->where('cajas.user_id', $this->filtro_usuario);
                    })
                    ->when($this->fecha_desde, function ($q) {
                        $q->whereDate('cajas.fecha_apertura', '>=', $this->fecha_desde);
                    })
                    ->when($this->fecha_hasta, function ($q) {
                        $q->whereDate('cajas.fecha_apertura', '<=', $this->fecha_hasta);
                    });
            })
            ->orderBy('cajas.fecha_cierre', 'desc')
            ->paginate(10, 'historialPage');
    }

    // 2. OBTENER USUARIOS PARA EL DROPDOWN
    #[Computed]
    public function usuarios()
    {
        return User::where('is_active', true)->get();
    }

    // 2.1 ESTADÍSTICAS PARA EL GRÁFICO (Fase 7)
    #[Computed]
    public function estadisticasSieteDias()
    {
        // Últimos 7 días
        $fechas = collect();
        for ($i = 6; $i >= 0; $i--) {
            $fechas->push(now()->subDays($i)->format('Y-m-d'));
        }

        // Consultar sumatorias por fecha de cierre
        $estadisticas = Caja::whereNotNull('fecha_cierre')
            ->whereDate('fecha_cierre', '>=', now()->subDays(6)->toDateString())
            ->selectRaw('DATE(fecha_cierre) as fecha, SUM(monto_final) as total')
            ->groupBy('fecha')
            ->pluck('total', 'fecha');

        // Formatear array final asociando 0 a los días sin cierres
        $datosFinales = [];
        $labels = [];
        foreach ($fechas as $fecha) {
            $labels[] = Carbon::parse($fecha)->format('d/m');
            $datosFinales[] = isset($estadisticas[$fecha]) ? (float) $estadisticas[$fecha] : 0;
        }

        return [
            'labels' => $labels,
            'datos' => $datosFinales,
        ];
    }

    // 3. OBTENER MEDIOS DE PAGO PARA MOVIMIENTOS
    #[Computed]
    public function mediosPago()
    {
        return MedioPago::all();
    }

    public function abrirCaja()
    {
        $this->validate([
            'monto_inicial' => 'required|numeric|min:0',
            'user_id' => 'required|exists:users,id', // Validamos el usuario elegido
        ]);

        // Verificación RNF-02: Una caja abierta por turno por ese usuario
        $cajaAbierta = Caja::where('user_id', $this->user_id)
            ->whereNull('fecha_cierre')
            ->exists();

        if ($cajaAbierta) {
            $this->addError('caja_status', 'Este usuario ya tiene una caja abierta actualmente.');

            return;
        }

        Caja::create([
            'fecha_apertura' => now(),
            'monto_inicial' => $this->monto_inicial,
            'user_id' => $this->user_id, // Usamos el seleccionado, no el Auth::id()
        ]);

        Flux::modal('abrir-caja-form')->close();
        $this->reset(['monto_inicial', 'user_id']);
        $this->notify('Caja abierta correctamente.', 'success');
    }

    public function render()
    {
        return view('livewire.admin.caja-manager');
    }

    #[Computed]
    public function saldoActual()
    {
        if (! $this->cajaSeleccionada) {
            return 0;
        }

        $ingresos = $this->cajaSeleccionada->movimientos->where('tipo_movimiento', 'INGRESO')->sum('monto');
        $egresos = $this->cajaSeleccionada->movimientos->where('tipo_movimiento', 'EGRESO')->sum('monto');

        return $this->cajaSeleccionada->monto_inicial + $ingresos - $egresos;
    }

    public $cajaSeleccionada = null;

    public function verDetalle($id)
    {
        $this->cajaSeleccionada = Caja::with('movimientos.medioPago')->find($id);

        Flux::modal('detalle-caja-panel')->show();
    }

    public function prepararMovimiento($tipo)
    {
        $this->reset(['movimiento_monto', 'movimiento_motivo', 'movimiento_medio_pago', 'observaciones_cierre']);
        $this->movimiento_tipo = $tipo;
        Flux::modal('registro-movimiento-form')->show();
    }

    public function registrarMovimiento()
    {
        $this->validate([
            'movimiento_monto' => 'required|numeric|min:0.01',
            'movimiento_motivo' => 'required|string|max:255',
            'movimiento_medio_pago' => 'required|exists:medio_pagos,id',
        ]);

        MovimientoCaja::create([
            'tipo_movimiento' => $this->movimiento_tipo,
            'monto' => $this->movimiento_monto,
            'motivo' => $this->movimiento_motivo,
            'fecha_movimiento' => now(),
            'id_medio_pago' => $this->movimiento_medio_pago,
            'id_caja' => $this->cajaSeleccionada->id,
            'user_id' => Auth::id(), // Empleado que registra la operación
        ]);

        Flux::modal('registro-movimiento-form')->close();
        $this->notify("{$this->movimiento_tipo} registrado correctamente.", 'success');

        // Refrescar caja seleccionada
        $this->verDetalle($this->cajaSeleccionada->id);
    }

    public function cerrarCaja()
    {
        $this->validate([
            'observaciones_cierre' => 'nullable|string|max:1000',
        ]);

        if (! $this->cajaSeleccionada || $this->cajaSeleccionada->fecha_cierre) {
            return;
        }

        // Recalculamos usando BD sum para mayor precisión
        $ingresos = MovimientoCaja::where('id_caja', $this->cajaSeleccionada->id)
            ->where('tipo_movimiento', 'INGRESO')
            ->sum('monto');

        $egresos = MovimientoCaja::where('id_caja', $this->cajaSeleccionada->id)
            ->where('tipo_movimiento', 'EGRESO')
            ->sum('monto');

        $monto_final = $this->cajaSeleccionada->monto_inicial + $ingresos - $egresos;

        $this->cajaSeleccionada->update([
            'fecha_cierre' => now(),
            'monto_final' => $monto_final,
            'observaciones' => $this->observaciones_cierre,
        ]);

        Flux::modal('confirm-admin-close-caja')->close();
        Flux::modal('detalle-caja-panel')->close();
        $this->notify('Caja cerrada con éxito.', 'success');
        $this->reset('observaciones_cierre');
        $this->cajaSeleccionada = null; // Reset selection
    }

    #[Computed]
    public function totalesPorMedio()
    {
        if (! $this->cajaSeleccionada) {
            return collect();
        }

        // Agrupamos los movimientos por el nombre del medio de pago y sumamos/restamos
        return $this->cajaSeleccionada->movimientos()
            ->with('medioPago')
            ->get()
            ->groupBy('medioPago.nombre')
            ->map(function ($movimientos) {
                $ingresos = $movimientos->where('tipo_movimiento', 'INGRESO')->sum('monto');
                $egresos = $movimientos->where('tipo_movimiento', 'EGRESO')->sum('monto');

                return $ingresos - $egresos;
            });
    }

    // Fase 7: Reporte en PDF (RF-07)
    public function descargarReporte($id)
    {
        $caja = Caja::with(['user', 'movimientos.medioPago'])->findOrFail($id);

        if (! Auth::user()->hasRole('admin') && $caja->user_id !== Auth::id()) {
            $this->notify('No tienes permisos para descargar este reporte.', 'danger');

            return;
        }

        if (! $caja->fecha_cierre) {
            $this->notify('Solo puedes emitir reportes de cajas cerradas.', 'warning');

            return;
        }

        $totalesMp = $caja->movimientos->groupBy(function ($mov) {
            return $mov->medioPago->nombre ?? 'Sin especificar';
        })->map(function ($movs) {
            $ingresos = $movs->where('tipo_movimiento', 'INGRESO')->sum('monto');
            $egresos = $movs->where('tipo_movimiento', 'EGRESO')->sum('monto');

            return [
                'ingresos' => $ingresos,
                'egresos' => $egresos,
                'neto' => $ingresos - $egresos,
            ];
        });

        $pdf = Pdf::loadView('pdf.reporte-caja', [
            'caja' => $caja,
            'totales' => $totalesMp,
        ]);

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            "Reporte-Cierre-Caja-{$caja->id}.pdf"
        );
    }

    #[Computed]
    public function analiticaResumen()
    {
        $stats = $this->estadisticasSieteDias;
        $datos = collect($stats['datos']);

        if ($datos->isEmpty() || $datos->sum() == 0) {
            return [
                'mejor_monto' => 0,
                'mejor_dia' => 'N/A',
                'promedio' => 0,
            ];
        }

        // Buscamos el índice del valor más alto
        $maxIndice = $datos->search($datos->max());

        return [
            'mejor_monto' => $datos->max(),
            'mejor_dia' => $stats['labels'][$maxIndice],
            'promedio' => $datos->avg(),
        ];
    }
}
