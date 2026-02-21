<div class="flex flex-col gap-6 p-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-zinc-200 dark:border-zinc-700 pb-6">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Gestión de Permisos</h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Define los accesos atómicos del sistema FarmaCorp (Caja, Inventario, etc.)
            </p>
        </div>
        <button 
            wire:click="openCreateModal" 
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md hover:bg-blue-700 transition-all active:scale-95"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Crear Permiso
        </button>
    </div>

    @if (session()->has('message'))
        <div class="p-4 bg-green-100 border-l-4 border-green-500 text-green-700 dark:bg-green-900/30 dark:text-green-300 rounded-r-lg shadow-sm">
            {{ session('message') }}
        </div>
    @endif

    <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2 relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-zinc-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </span>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Buscar por nombre de permiso..." 
                    class="w-full pl-10 pr-4 py-2 border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all"
                />
            </div>

            <select 
                wire:model.live="statusFilter" 
                class="px-4 py-2 border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-white outline-none focus:ring-2 focus:ring-blue-500 transition-all"
            >
                <option value="">Todos los estados</option>
                <option value="active">Activos</option>
                <option value="inactive">Inactivos</option>
            </select>
        </div>

        <div class="flex items-center justify-between pt-2">
            <div class="flex items-center gap-3">
                <button 
                    wire:click="toggleTrash" 
                    class="relative inline-flex items-center gap-2 rounded-lg border px-4 py-2 text-sm font-medium transition-colors {{ $showTrash ? 'bg-blue-50 text-blue-600 border-blue-200' : 'bg-white dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300 border-zinc-300 dark:border-zinc-700 hover:bg-zinc-50' }}"
                >
                    {{ $showTrash ? '📂 Ver Activos' : '🗑️ Ver Papelera' }}
                    @if($this->trashCount > 0 && !$showTrash)
                        <span class="absolute -top-2 -right-2 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white ring-2 ring-white dark:ring-zinc-900">
                            {{ $this->trashCount }}
                        </span>
                    @endif
                </button>
                <button wire:click="resetFilters" class="text-sm font-medium text-zinc-500 hover:text-blue-600">
                    Limpiar Filtros
                </button>
            </div>
            <p class="text-xs text-zinc-400 italic">Total: {{ $permissions->total() }} permiso(s)</p>
        </div>
    </div>

    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-zinc-50 dark:bg-zinc-800/50 text-zinc-500 text-xs uppercase font-bold">
                    <tr>
                        <th class="px-6 py-4">Permiso / Guard</th>
                        <th class="px-6 py-4">Descripción</th>
                        <th class="px-6 py-4">Estado</th>
                        <th class="px-6 py-4">Creador</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    @forelse($permissions as $permission)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $permission->name }}</div>
                                <div class="text-[10px] text-zinc-400 uppercase tracking-tighter">{{ $permission->guard_name }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $permission->description ? Str::limit($permission->description, 60) : 'Sin descripción' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-[10px] font-bold uppercase rounded-full {{ $permission->is_active ? 'bg-green-100 text-green-700' : 'bg-zinc-100 text-zinc-500' }}">
                                    {{ $permission->is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($permission->creator)
                                    <div class="flex items-center gap-2">
                                        <div class="flex h-7 w-7 items-center justify-center rounded-full bg-zinc-200 text-[10px] font-bold dark:bg-zinc-700">{{ strtoupper(substr($permission->creator->name, 0, 2)) }}</div>
                                        <span class="text-xs text-zinc-500">{{ $permission->creator->name }}</span>
                                    </div>
                                @else
                                    <span class="text-xs text-zinc-400 italic">Sistema</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    @if($showTrash)
                                        <button wire:click="restore({{ $permission->id }})" class="text-blue-600 hover:underline text-xs font-bold">Restaurar</button>
                                        <button wire:click="forceDelete({{ $permission->id }})" wire:confirm="¿Borrar para siempre?" class="text-red-600 hover:underline text-xs font-bold ml-2">Final</button>
                                    @else
                                        <button wire:click="openEditModal({{ $permission->id }})" class="p-1.5 text-zinc-400 hover:text-blue-600 transition-colors">✏️</button>
                                        <button wire:click="toggleActive({{ $permission->id }})" class="p-1.5 text-zinc-400 hover:text-zinc-600">👁️</button>
                                        <button wire:click="delete({{ $permission->id }})" wire:confirm="¿A la papelera?" class="p-1.5 text-zinc-400 hover:text-red-600">🗑️</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-12 text-center text-zinc-400 italic text-sm">No se encontraron permisos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-zinc-100 dark:border-zinc-800">
            {{ $permissions->links() }}
        </div>
    </div>

    @if($showModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-zinc-900/60 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>
            
            <div class="relative bg-white dark:bg-zinc-900 w-full max-w-lg rounded-2xl shadow-2xl border border-zinc-200 dark:border-zinc-700 overflow-hidden transform transition-all animate-in fade-in zoom-in duration-200" wire:click.stop>
                <div class="p-6 border-b border-zinc-100 dark:border-zinc-800 flex justify-between items-center">
                    <h2 class="text-xl font-bold text-zinc-900 dark:text-white">
                        {{ $editingId ? 'Editar Permiso' : 'Nuevo Permiso' }}
                    </h2>
                    <button wire:click="closeModal" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 transition-colors">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form wire:submit.prevent="save" class="p-6 space-y-5">
                    <div>
                        <label class="block text-sm font-semibold mb-1 text-zinc-700 dark:text-zinc-300">Nombre del Permiso</label>
                        <input type="text" wire:model="name" placeholder="Ej: ingresar.caja" class="w-full px-4 py-2 border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                        @error('name') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1 text-zinc-700 dark:text-zinc-300">Guard</label>
                        <select wire:model="guardName" class="w-full px-4 py-2 border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="web">web (Web Browser)</option>
                            <option value="api">api (Mobile/External)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1 text-zinc-700 dark:text-zinc-300">Descripción Funcional</label>
                        <textarea wire:model="description" rows="3" placeholder="¿Qué permite hacer este permiso?" class="w-full px-4 py-2 border border-zinc-300 rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" wire:model="isActive" id="perm_active" class="w-4 h-4 rounded text-blue-600 border-zinc-300 focus:ring-blue-500 transition-all">
                        <label for="perm_active" class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Permiso habilitado para el sistema</label>
                    </div>

                    <div class="flex justify-end gap-3 pt-6 border-t border-zinc-100 dark:border-zinc-800">
                        <button type="button" wire:click="closeModal" class="px-4 py-2 text-sm font-medium text-zinc-500 hover:text-zinc-700 transition-colors">Cancelar</button>
                        <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg shadow-lg shadow-blue-200 dark:shadow-none transition-all">
                            {{ $editingId ? 'Actualizar Cambios' : 'Crear Permiso' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>