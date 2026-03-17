<?php

namespace App\Livewire\Admin;

use App\Models\ObraSocial;
use App\Models\Medicine;
use App\Models\Group;
use App\Traits\Notifies;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

#[Layout('components.layouts.app', ['title' => 'Gestión de Obras Sociales'])]
class ObraSocialManager extends Component
{
    use Notifies, WithPagination;

    // Filtros y Búsqueda
    public string $search = '';
    public string $statusFilter = '';

    // Gestión de Obra Social (CRUD)
    public ?ObraSocial $editingOS = null;
    public array $osContext = ['name' => '', 'is_active' => true];

    // Gestión de Vademécum (Modal)
    public ?ObraSocial $selectedOS = null;
    public string $searchProduct = '';
    public string $filterGroup = '';
    public array $selectedMedicines = []; // Checkboxes
    public $bulkDiscount = 0; // Input de descuento masivo

    public function updatedSearch() { $this->resetPage(); }

    // --- CRUD BÁSICO ---
    public function createOS()
    {
        $this->reset(['osContext', 'editingOS']);
        Flux::modal('os-form')->show();
    }

    public function editOS(ObraSocial $os)
    {
        $this->editingOS = $os;
        $this->osContext = ['name' => $os->name, 'is_active' => $os->is_active];
        Flux::modal('os-form')->show();
    }

    public function saveOS()
    {
        $this->validate([
            'osContext.name' => 'required|string|max:255|unique:obras_sociales,name,' . ($this->editingOS->id ?? 'NULL'),
        ]);

        if ($this->editingOS) {
            $this->editingOS->update($this->osContext);
        } else {
            ObraSocial::create($this->osContext);
        }

        Flux::modal('os-form')->close();
        $this->notify('Obra Social guardada correctamente.', 'success');
    }

    // --- LÓGICA DEL VADEMÉCUM (MODAL DEL OJO) ---
    public function manageVademecum(ObraSocial $os)
    {
        $this->selectedOS = $os;
        $this->reset(['selectedMedicines', 'bulkDiscount', 'searchProduct', 'filterGroup']);
        Flux::modal('vademecum-modal')->show();
    }

    public function applyBulkDiscount()
    {
        if (empty($this->selectedMedicines)) {
            $this->notify('Seleccione al menos un medicamento.', 'warning');
            return;
        }

        $this->validate(['bulkDiscount' => 'required|numeric|min:0|max:100']);

        // Sinceridad de Analista: Usamos una transacción para asegurar que todo se guarde o nada
        DB::transaction(function () {
            foreach ($this->selectedMedicines as $medicineId) {
                // updateOrInsert para el pivot
                DB::table('obra_social_medicine')->updateOrInsert(
                    ['obra_social_id' => $this->selectedOS->id, 'medicine_id' => $medicineId],
                    ['discount_percentage' => $this->bulkDiscount, 'updated_at' => now()]
                );
            }
        });

        $this->notify("Descuento del {$this->bulkDiscount}% aplicado a " . count($this->selectedMedicines) . " productos.", 'success');
        $this->reset(['selectedMedicines', 'bulkDiscount']);
        // Refrescamos la relación para que se vea en el modal si fuera necesario
        $this->selectedOS->load('medicines');
    }

    public function render()
    {
        // Lista principal de Obras Sociales
        $obrasSociales = ObraSocial::where('name', 'ilike', "%{$this->search}%")
            ->when($this->statusFilter !== '', fn($q) => $q->where('is_active', $this->statusFilter))
            ->paginate(10);

        // Lista de medicamentos para el modal del Vademécum
        $medicinesList = Medicine::query()
            ->with(['product', 'group'])
            ->join('products', 'medicines.product_id', '=', 'products.id')
            ->where('products.name', 'ilike', "%{$this->searchProduct}%")
            ->when($this->filterGroup !== '', fn($q) => $q->where('medicines.group_id', $this->filterGroup))
            ->select('medicines.*')
            ->get();

        return view('livewire.admin.obra-social-manager', [
            'obrasSociales' => $obrasSociales,
            'medicinesList' => $medicinesList,
            'groups' => Group::orderBy('name')->get(),
        ]);
    }
}