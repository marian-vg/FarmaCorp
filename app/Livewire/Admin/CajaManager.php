<?php

namespace App\Livewire\Admin;

use App\Models\Caja;
use App\Models\User; // Importamos User
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed; // Para los datos reactivos
use Livewire\Component;
use Livewire\WithPagination;
use Flux\Flux;

#[Layout('components.layouts.app')]
class CajaManager extends Component
{
    use WithPagination;

    public $monto_inicial = '';
    public $user_id = ''; // Nueva propiedad para elegir el usuario

    // Propiedades para registro de movimientos administrativos
    public $movimiento_monto = '';
    public $movimiento_motivo = '';
    public $movimiento_medio_pago = '';
    public $movimiento_tipo = ''; // INGRESO o EGRESO

    // Propiedades para Filtros Paginados (RF-04)
    public string $search = '';
    public string $filtro_usuario = '';
    public string $fecha_desde = '';
    public string $fecha_hasta = '';

    public function updatedSearch() { $this->resetPage(); }
    public function updatedFiltroUsuario() { $this->resetPage(); }
    public function updatedFechaDesde() { $this->resetPage(); }
    public function updatedFechaHasta() { $this->resetPage(); }

    public function limpiarFiltros()
    {
        $this->reset(['search', 'filtro_usuario', 'fecha_desde', 'fecha_hasta']);
        $this->resetPage();
    }

    // 1. OBTENER TODAS LAS CAJAS PARA LA TABLA (Paginado y Filtrado)
    #[Computed]
    public function cajas()
    {
        return Caja::with('user')
            ->when($this->search, function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filtro_usuario, function ($query) {
                $query->where('user_id', $this->filtro_usuario);
            })
            ->when($this->fecha_desde, function ($query) {
                $query->whereDate('fecha_apertura', '>=', $this->fecha_desde);
            })
            ->when($this->fecha_hasta, function ($query) {
                $query->whereDate('fecha_apertura', '<=', $this->fecha_hasta);
            })
            ->orderBy('fecha_apertura', 'desc')
            ->paginate(10);
    }

    // 2. OBTENER USUARIOS PARA EL DROPDOWN
    #[Computed]
    public function usuarios()
    {
        return User::where('is_active', true)->get();
    }

    // 3. OBTENER MEDIOS DE PAGO PARA MOVIMIENTOS
    #[Computed]
    public function mediosPago()
    {
        return \App\Models\MedioPago::all();
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
        Flux::toast('Caja abierta correctamente.', variant: 'success');
    }

    public function render()
    {
        return view('livewire.admin.caja-manager');
    }

    #[Computed]
    public function saldoActual()
    {
        if (!$this->cajaSeleccionada) return 0;

        $ingresos = $this->cajaSeleccionada->movimientos->where('tipo_movimiento', 'INGRESO')->sum('monto');
        $egresos = $this->cajaSeleccionada->movimientos->where('tipo_movimiento', 'EGRESO')->sum('monto');

        return $this->cajaSeleccionada->monto_inicial + $ingresos - $egresos;
    }

    public $cajaSeleccionada = null;

    public function verDetalle($id)
    {
        // Buscamos la caja con sus movimientos y el medio de pago de cada uno
        $this->cajaSeleccionada = Caja::with('movimientos.medioPago')->find($id);
        
        // Disparamos el panel lateral de Flux
        Flux::modal('detalle-caja-panel')->show();
    }

    public function prepararMovimiento($tipo)
    {
        $this->reset(['movimiento_monto', 'movimiento_motivo', 'movimiento_medio_pago']);
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

        \App\Models\MovimientoCaja::create([
            'tipo_movimiento' => $this->movimiento_tipo,
            'monto' => $this->movimiento_monto,
            'motivo' => $this->movimiento_motivo,
            'fecha_movimiento' => now(),
            'id_medio_pago' => $this->movimiento_medio_pago,
            'id_caja' => $this->cajaSeleccionada->id,
            'user_id' => Auth::id(), // Empleado que registra la operación
        ]);

        Flux::modal('registro-movimiento-form')->close();
        Flux::toast("{$this->movimiento_tipo} registrado correctamente.", variant: 'success');

        // Refrescar caja seleccionada
        $this->verDetalle($this->cajaSeleccionada->id);
    }

    public function cerrarCaja()
    {
        if (!$this->cajaSeleccionada || $this->cajaSeleccionada->fecha_cierre) {
            return;
        }

        // Recalculamos usando BD sum para mayor precisión
        $ingresos = \App\Models\MovimientoCaja::where('id_caja', $this->cajaSeleccionada->id)
            ->where('tipo_movimiento', 'INGRESO')
            ->sum('monto');
            
        $egresos = \App\Models\MovimientoCaja::where('id_caja', $this->cajaSeleccionada->id)
            ->where('tipo_movimiento', 'EGRESO')
            ->sum('monto');

        $monto_final = $this->cajaSeleccionada->monto_inicial + $ingresos - $egresos;

        $this->cajaSeleccionada->update([
            'fecha_cierre' => now(),
            'monto_final' => $monto_final,
        ]);

        Flux::modal('detalle-caja-panel')->close();
        Flux::toast('Caja cerrada con éxito.', variant: 'success');
        $this->cajaSeleccionada = null; // Reset selection
    }
}