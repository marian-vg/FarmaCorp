<?php
namespace App\Livewire\Admin;

use App\Models\Prescription;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;

class PrescriptionManager extends Component
{
    use WithPagination;

    public $search = '';

    public function download($id)
    {
        try {
            $prescription = Prescription::findOrFail($id);

            // Sinceridad de Analista: Nunca confíes en que el archivo está ahí solo porque lo dice la DB
            if (!Storage::disk('supabase')->exists($prescription->file_path)) {
                $this->notify('El archivo físico no se encuentra en la nube. Contacte a soporte.', 'error');
                return;
            }

            return Storage::disk('supabase')->download($prescription->file_path, "Receta-Venta-{$prescription->factura_id}.pdf");
        
        } catch (\Exception $e) {
            $this->notify('Error al intentar descargar: ' . $e->getMessage(), 'error');
        }
    }

    public function render()
    {
        $prescriptions = Prescription::with(['cliente', 'factura'])
            ->whereHas('cliente', function($q) {
                $q->where('first_name', 'ilike', "%{$this->search}%")
                  ->orWhere('last_name', 'ilike', "%{$this->search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.admin.prescription-manager', compact('prescriptions'))->layout('layouts.app');
    }
}