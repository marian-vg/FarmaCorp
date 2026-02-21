<div class="flex flex-col gap-6 p-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-zinc-200 dark:border-zinc-700 pb-6">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Gestión de Perfiles</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Define los roles operativos de FarmaCorp (Caja, Depósito, etc.)</p>
        </div>
        <button wire:click="openCreateModal" class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md hover:bg-indigo-700 transition-all active:scale-95">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Crear Nuevo Perfil
        </button>
    </div>

    @if (session()->has('message'))
        <div class="p-4 bg-green-100 border-l-4 border-green-500 text-green-700 dark:bg-green-900/30 dark:text-green-300 rounded-r-lg">
            {{ session('message') }}
        </div>
    @endif

    <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2 relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-zinc-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </span>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar perfil..." class="w-full pl-10 pr-4 py-2 border rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
            <select wire:model.live="statusFilter" class="px-4 py-2 border rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Todos los estados</option>
                <option value="active">Activos</option>
                <option value="inactive">Inactivos</option>
            </select>
        </div>
        <div class="flex items-center justify-between pt-2">
        <button wire:click="toggleTrash" class="relative text-sm font-medium px-4 py-2 rounded-lg border transition-colors {{ $showTrash ? 'bg-red-50 text-red-600 border-red-200' : 'text-zinc-600 border-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800' }}">
    {{ $showTrash ? '📂 Ver Activos' : '🗑️ Ver Papelera' }}

    @if($this->trashCount > 0 && !$showTrash)
        <span class="absolute -top-2 -right-2 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white shadow-sm ring-2 ring-white dark:ring-zinc-900">
            {{ $this->trashCount }}
        </span>
    @endif
</button>
            <span class="text-xs text-zinc-400">Resultados: {{ $profiles->total() }}</span>
        </div>
    </div>

    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-sm">
        <table class="w-full text-left">
            <thead class="bg-zinc-50 dark:bg-zinc-800/50 text-zinc-500 text-xs uppercase font-bold">
                <tr>
                    <th class="px-6 py-4">Perfil</th>
                    <th class="px-6 py-4">Descripción</th>
                    <th class="px-6 py-4">Estado</th>
                    <th class="px-6 py-4">Creado por</th>
                    <th class="px-6 py-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                @forelse($profiles as $profile)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/30 transition-colors">
                        <td class="px-6 py-4 font-semibold text-zinc-900 dark:text-zinc-100">{{ $profile->name }}</td>
                        <td class="px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">{{ Str::limit($profile->description, 50) }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-[10px] font-bold uppercase rounded-full {{ $profile->is_active ? 'bg-green-100 text-green-700' : 'bg-zinc-100 text-zinc-500' }}">
                                {{ $profile->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-xs text-zinc-500">{{ $profile->creator->name ?? 'Sistema' }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-3">
                                @if($showTrash)
                                    <button wire:click="restore({{ $profile->id }})" class="text-indigo-600 hover:underline text-xs">Restaurar</button>
                                @else
                                    <button wire:click="openEditModal({{ $profile->id }})" class="text-zinc-400 hover:text-indigo-600 transition-colors">✏️</button>
                                    <button wire:click="delete({{ $profile->id }})" class="text-zinc-400 hover:text-red-600 transition-colors">🗑️</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-12 text-center text-zinc-400 italic">No hay perfiles registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-zinc-100 dark:border-zinc-800">
            {{ $profiles->links() }}
        </div>
    </div>

    @if($showModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-zinc-900/60 backdrop-blur-sm" wire:click="closeModal"></div>
            
            <div class="relative bg-white dark:bg-zinc-900 w-full max-w-lg rounded-2xl shadow-2xl border border-zinc-200 dark:border-zinc-700 overflow-hidden transform transition-all">
                <div class="p-6 border-b border-zinc-100 dark:border-zinc-800 flex justify-between items-center">
                    <h2 class="text-xl font-bold dark:text-white">{{ $editingId ? 'Editar Perfil' : 'Nuevo Perfil' }}</h2>
                    <button wire:click="closeModal" class="text-zinc-400 hover:text-zinc-600">✕</button>
                </div>

                <form wire:submit.prevent="save" class="p-6 space-y-5">
                    <div>
                        <label class="block text-sm font-medium mb-1 dark:text-zinc-300">Nombre del Perfil</label>
                        <input type="text" wire:model="name" class="w-full px-4 py-2 border rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1 dark:text-zinc-300">Descripción</label>
                        <textarea wire:model="description" rows="3" class="w-full px-4 py-2 border rounded-lg dark:bg-zinc-800 dark:border-zinc-700 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" wire:model="isActive" id="is_active_check" class="w-4 h-4 rounded text-indigo-600 border-zinc-300 focus:ring-indigo-500">
                        <label for="is_active_check" class="text-sm dark:text-zinc-300">Perfil habilitado</label>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                        <button type="button" wire:click="closeModal" class="px-4 py-2 text-sm font-medium text-zinc-500 hover:text-zinc-700 transition-colors">Cancelar</button>
                        <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow-lg shadow-indigo-200 dark:shadow-none transition-all">
                            {{ $editingId ? 'Actualizar Cambios' : 'Guardar Perfil' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>