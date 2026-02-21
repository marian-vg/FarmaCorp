<div class="flex flex-col gap-6 p-6 bg-white dark:bg-zinc-900 rounded-xl shadow-sm">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-zinc-800 dark:text-zinc-100">Gestión de Usuarios</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Administra los usuarios del sistema, sus roles y permisos.</p>
        </div>
        <button wire:click="openCreateModal" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors font-medium">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Crear Usuario
        </button>
    </div>

    @if (session()->has('message'))
        <div class="p-4 bg-green-100 border-l-4 border-green-500 text-green-700 dark:bg-green-900 dark:text-green-100 rounded">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="p-4 bg-red-100 border-l-4 border-red-500 text-red-700 dark:bg-red-900 dark:text-red-100 rounded">
            {{ session('error') }}
        </div>
    @endif

    <div class="p-4 bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por nombre o email..." 
                    class="w-full px-4 py-2 border rounded-lg dark:bg-zinc-900 dark:border-zinc-600 dark:text-white focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <select wire:model.live="roleFilter" class="w-full px-4 py-2 border rounded-lg dark:bg-zinc-900 dark:border-zinc-600 dark:text-white">
                    <option value="">Todos los roles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select wire:model.live="statusFilter" class="w-full px-4 py-2 border rounded-lg dark:bg-zinc-900 dark:border-zinc-600 dark:text-white">
                    <option value="">Todos los estados</option>
                    <option value="active">Activos</option>
                    <option value="inactive">Inactivos</option>
                </select>
            </div>
        </div>

        <div class="flex items-center justify-between border-t border-zinc-200 dark:border-zinc-700 pt-4">
            <div class="flex gap-2">
            <button wire:click="toggleTrash" class="relative px-3 py-1.5 text-sm font-medium rounded-lg border {{ $showTrash ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300 border-zinc-300 dark:border-zinc-600' }}">
                {{ $showTrash ? '📁 Ver Activos' : '🗑️ Ver Papelera' }}
    
                @if($this->trashCount > 0 && !$showTrash)
                <span class="absolute -top-2 -right-2 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] text-white shadow-sm">
                    {{ $this->trashCount }}
                </span>
                @endif
            </button>
            </div>
            <span class="text-sm text-zinc-500 italic">Total: {{ $users->total() }} usuario(s)</span>
        </div>
    </div>

    <div class="overflow-x-auto border border-zinc-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-900">
        <table class="w-full text-left">
            <thead class="bg-zinc-50 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 text-xs uppercase font-semibold">
                <tr>
                    <th class="px-6 py-4">Usuario</th>
                    <th class="px-6 py-4">Rol</th>
                    <th class="px-6 py-4">Estado</th>
                    <th class="px-6 py-4">Creado por</th>
                    <th class="px-6 py-4">Fecha</th>
                    <th class="px-6 py-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse($users as $user)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors text-zinc-700 dark:text-zinc-300">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 text-indigo-700 font-bold">
                                    {{ $user->initials() }}
                                </div>
                                <div>
                                    <div class="font-semibold text-zinc-900 dark:text-white">{{ $user->name }}</div>
                                    <div class="text-sm text-zinc-500">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-700">
                                {{ $user->roles->first()?->name ?? 'Sin rol' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($showTrash)
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700 uppercase">Eliminado</span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-zinc-100 text-zinc-700' }}">
                                    {{ $user->is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm italic text-zinc-500">{{ $user->creator->name ?? 'Sistema' }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm">{{ $user->created_at->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                @if($showTrash)
                                    <button wire:click="restore({{ $user->id }})" class="text-indigo-600 hover:underline text-sm font-medium">Restaurar</button>
                                    <button wire:click="forceDelete({{ $user->id }})" wire:confirm="¿Borrar permanentemente?" class="text-red-600 hover:underline text-sm font-medium">Borrar Final</button>
                                @else
                                    <button wire:click="openEditModal({{ $user->id }})" class="text-zinc-600 hover:text-indigo-600">
                                        ✏️ Editar
                                    </button>
                                    @if($user->id !== auth()->id())
                                        <button wire:click="toggleActive({{ $user->id }})" class="text-zinc-600 hover:text-indigo-600">
                                            {{ $user->is_active ? '🚫 Desactivar' : '✅ Activar' }}
                                        </button>
                                        <button wire:click="delete({{ $user->id }})" wire:confirm="¿Enviar a la papelera?" class="text-red-500 hover:text-red-700">
                                            🗑️
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-zinc-500 italic">No se encontraron usuarios.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>

    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/75 backdrop-blur-sm">
            <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden border border-zinc-200 dark:border-zinc-700">
                <div class="p-6 border-b dark:border-zinc-700">
                    <h3 class="text-xl font-bold text-zinc-900 dark:text-white">{{ $editingId ? 'Editar Usuario' : 'Nuevo Usuario' }}</h3>
                </div>
                
                <form wire:submit.prevent="save" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Nombre</label>
                        <input wire:model="name" type="text" class="w-full mt-1 px-4 py-2 border rounded-lg dark:bg-zinc-800 dark:border-zinc-600 dark:text-white">
                        @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Email</label>
                        <input wire:model="email" type="email" class="w-full mt-1 px-4 py-2 border rounded-lg dark:bg-zinc-800 dark:border-zinc-600 dark:text-white">
                        @error('email') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Password</label>
                        <input wire:model="password" type="password" placeholder="{{ $editingId ? 'Dejar vacío para no cambiar' : 'Mínimo 8 caracteres' }}" 
                            class="w-full mt-1 px-4 py-2 border rounded-lg dark:bg-zinc-800 dark:border-zinc-600 dark:text-white">
                        @error('password') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Rol</label>
                        <select wire:model="selectedRole" class="w-full mt-1 px-4 py-2 border rounded-lg dark:bg-zinc-800 dark:border-zinc-600 dark:text-white">
                            <option value="">Seleccionar rol...</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                        @error('selectedRole') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    @if(!$editingId || $editingId !== auth()->id())
                        <div class="flex items-center gap-2">
                            <input wire:model="isActive" type="checkbox" id="modal_active" class="rounded text-indigo-600">
                            <label for="modal_active" class="text-sm dark:text-zinc-300">Usuario Activo</label>
                        </div>
                    @endif

                    <div class="flex justify-end gap-3 pt-4 border-t dark:border-zinc-700">
                        <button type="button" wire:click="closeModal" class="px-4 py-2 text-sm font-medium text-zinc-600 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg">Cancelar</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>