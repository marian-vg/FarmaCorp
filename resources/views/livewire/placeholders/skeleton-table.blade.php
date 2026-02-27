<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <flux:heading level="1" size="lg">Admin Dashboard</flux:heading>

    <div class="w-full overflow-hidden rounded-lg border border-gray-200 dark:border-zinc-700">

        <flux:skeleton.group animate="shimmer">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                
                <thead class="bg-gray-50 dark:bg-zinc-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Usuario</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Rol</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Permisos</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Perfiles</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-zinc-400">Acciones</th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200 dark:bg-zinc-900 dark:divide-zinc-700">
                    @foreach (range(1, 5) as $i)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <flux:skeleton class="size-8 rounded-full" />
                                    <div class="flex flex-col gap-1 w-full">
                                        <flux:skeleton.line class="w-5/6" />
                                        <flux:skeleton.line class="w-5/6 opacity-50" />
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <flux:skeleton.line class="w-24" />
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <flux:skeleton.line class="w-24" />
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <flux:skeleton class="w-16 h-6 rounded-md" />
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <flux:skeleton class="w-16 h-6 rounded-md" />
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <flux:skeleton class="w-16 h-6 rounded-md" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </flux:skeleton.group>
    </div>

</div>
