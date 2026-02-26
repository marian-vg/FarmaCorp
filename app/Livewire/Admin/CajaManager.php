<?php

namespace App\Livewire\Admin;

use App\Models\Caja;
use App\Models\User; // Importamos User
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed; // Para los datos reactivos
use Livewire\Component;
use Flux\Flux;

#[Layout('components.layouts.app')]
class CajaManager extends Component
{
    public $monto_inicial = '';
    public $user_id = ''; // Nueva propiedad para elegir el usuario

    // 1. OBTENER TODAS LAS CAJAS PARA LA TABLA
    #[Computed]
    public function cajas()
    {
        // Traemos las cajas con sus usuarios (relación Credencial del PDF)
        return Caja::with('user')->orderBy('fecha_apertura', 'desc')->get();
    }

    // 2. OBTENER USUARIOS PARA EL DROPDOWN
    #[Computed]
    public function usuarios()
    {
        return User::where('is_active', true)->get();
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

    public $cajaSeleccionada = null;

    public function verDetalle($id)
    {
        // Buscamos la caja con sus movimientos y el medio de pago de cada uno
        $this->cajaSeleccionada = Caja::with('movimientos.medioPago')->find($id);
        
        // Disparamos el panel lateral de Flux
        Flux::modal('detalle-caja-panel')->show();
    }
}