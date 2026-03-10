<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    @include('partials.head')
</head>
<body class="bg-white text-zinc-900 antialiased dark:bg-zinc-900 dark:text-zinc-100" x-data="{ activeSection: 'introduccion' }">

    <div class="flex min-h-screen">
        <aside class="fixed inset-y-0 left-0 z-50 w-72 bg-zinc-50 border-r border-zinc-200 dark:bg-zinc-950 dark:border-zinc-800 hidden lg:block overflow-y-auto">
            <div class="p-8">
                <div class="flex items-center gap-3 mb-10">
                    <div class="bg-indigo-600 p-2 rounded-lg text-white shadow-lg shadow-indigo-500/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold tracking-tight">FarmaCorp <span class="text-indigo-600">Docs</span></span>
                </div>

                <nav class="space-y-6">
                    <div x-data="{ open: true }">
                        <button @click="open = !open" class="flex items-center justify-between w-full px-2 mb-3 text-xs font-bold uppercase tracking-widest text-zinc-400 hover:text-indigo-600 transition-colors text-left font-sans">
                            <span>1. Introducción</span>
                            <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <ul x-show="open" x-transition class="space-y-1 border-l border-zinc-200 dark:border-zinc-800 ml-2">
                            <li><a href="#introduccion" class="block pl-4 py-2 text-sm font-medium hover:text-indigo-600">Inicio</a></li>
                            <li><a href="#filosofia" class="block pl-8 py-1.5 text-sm text-zinc-500 hover:text-indigo-600 transition-colors">Filosofía del Sistema</a></li>
                            <li><a href="#rbac" class="block pl-8 py-1.5 text-sm text-zinc-500 hover:text-indigo-600 transition-colors">Roles y Permisos (RBAC)</a></li>
                            <li><a href="#requisitos" class="block pl-8 py-1.5 text-sm text-zinc-500 hover:text-indigo-600 transition-colors">Requisitos Previos para el Uso</a></li>
                        </ul>
                    </div>

                    <div x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center justify-between w-full px-2 mb-3 text-xs font-bold uppercase tracking-widest text-zinc-400 hover:text-indigo-600 transition-colors text-left font-sans">
                            <span>2. Operaciones Iniciales</span>
                            <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <ul x-show="open" x-transition class="space-y-1 border-l border-zinc-200 dark:border-zinc-800 ml-2">
                            <li><a href="#operaciones-iniciales" class="block pl-4 py-2 text-sm font-medium hover:text-indigo-600">Inicio</a></li>
                            <li><a href="#dashboard-alertas" class="block pl-8 py-1.5 text-sm text-zinc-500 hover:text-indigo-600 transition-colors">2.1 El Dashboard de Alertas</a></li>
                            <li><a href="#gestion-cajas" class="block pl-8 py-1.5 text-sm text-zinc-500 hover:text-indigo-600 transition-colors">2.2 Gestión de Cajas (RF-01)</a></li>
                        </ul>
                    </div>

                    <div x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center justify-between w-full px-2 mb-3 text-xs font-bold uppercase tracking-widest text-zinc-400 hover:text-indigo-600 transition-colors text-left font-sans">
                            <span>3. Administración de Padrones</span>
                            <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <ul x-show="open" x-transition class="space-y-1 border-l border-zinc-200 dark:border-zinc-800 ml-2">
                            <li><a href="#padrones" class="block pl-4 py-2 text-sm font-medium hover:text-indigo-600">Inicio</a></li>
                            <li><a href="#gestion-clientes" class="block pl-8 py-1.5 text-sm text-zinc-500 hover:text-indigo-600 transition-colors">3.1 Gestión de Clientes</a></li>
                            <li><a href="#catalogo-productos" class="block pl-8 py-1.5 text-sm text-zinc-500 hover:text-indigo-600 transition-colors">3.2 Catálogo de Productos y Medicamentos</a></li>
                            <li><a href="#control-precios" class="block pl-8 py-1.5 text-sm text-zinc-500 hover:text-indigo-600 transition-colors">3.3 Control de Precios e Inflación</a></li>
                        </ul>
                    </div>

                    <div x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center justify-between w-full px-2 mb-3 text-xs font-bold uppercase tracking-widest text-zinc-400 hover:text-indigo-600 transition-colors text-left font-sans">
                            <span>4. Punto de Venta (POS)</span>
                            <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <ul x-show="open" x-transition class="space-y-1 border-l border-zinc-200 dark:border-zinc-800 ml-2">
                            <li><a href="#pos" class="block pl-4 py-2 text-sm font-medium hover:text-indigo-600">Inicio</a></li>
                            <li><a href="#ciclo-venta" class="block pl-8 py-1.5 text-sm text-zinc-500 hover:text-indigo-600 transition-colors">4.1 Ciclo de Venta y Carrito</a></li>
                            <li><a href="#ayudas-atencion" class="block pl-8 py-1.5 text-sm text-zinc-500 hover:text-indigo-600 transition-colors">4.2 Ayudas de Atención al Paciente</a></li>
                            <li><a href="#escudo-financiero" class="block pl-8 py-1.5 text-sm text-zinc-500 hover:text-indigo-600 transition-colors">4.3 Escudo de Seguridad Financiera</a></li>
                            <li><a href="#pagos-multimedio" class="block pl-8 py-1.5 text-sm text-zinc-500 hover:text-indigo-600 transition-colors">4.4 Gestión de Pagos Multimedio</a></li>
                        </ul>
                    </div>

                    <div x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center justify-between w-full px-2 mb-3 text-xs font-bold uppercase tracking-widest text-zinc-400 hover:text-indigo-600 transition-colors text-left font-sans">
                            <span>5. Finanzas y Cuentas Corrientes</span>
                            <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <ul x-show="open" x-transition class="space-y-1 border-l border-zinc-200 dark:border-zinc-800 ml-2">
                            <li><a href="#finanzas" class="block pl-4 py-2 text-sm font-medium hover:text-indigo-600">Inicio</a></li>
                            <li><a href="#monitoreo-saldos" class="block pl-8 py-1.5 text-sm text-zinc-500 hover:text-indigo-600 transition-colors">5.1 Monitoreo de Saldos (RF-16)</a></li>
                            <li><a href="#proceso-cobranza" class="block pl-8 py-1.5 text-sm text-zinc-500 hover:text-indigo-600 transition-colors">5.2 Proceso de Cobranza Multimedio</a></li>
                            <li><a href="#historial-auditoria" class="block pl-8 py-1.5 text-sm text-zinc-500 hover:text-indigo-600 transition-colors">5.3 Historial de Compras y Auditoría</a></li>
                            <li><a href="#integracion-caja" class="block pl-8 py-1.5 text-sm text-zinc-500 hover:text-indigo-600 transition-colors">5.4 Integración con Caja (RF-01)</a></li>
                        </ul>
                    </div>

                    <div x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center justify-between w-full px-2 mb-3 text-xs font-bold uppercase tracking-widest text-zinc-400 hover:text-indigo-600 transition-colors text-left font-sans">
                            <span>6. Gestión de Stock y Trazabilidad</span>
                            <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <ul x-show="open" x-transition class="space-y-1 border-l border-zinc-200 dark:border-zinc-800 ml-2">
                            <li><a href="#stock" class="block pl-4 py-2 text-sm font-medium hover:text-indigo-600">Inicio</a></li>
                            <li><a href="#ingreso-lotes" class="block pl-8 py-1.5 text-sm text-zinc-500 hover:text-indigo-600 transition-colors">6.1 Ingreso de Mercadería (Lotes)</a></li>
                            <li><a href="#egresos-especiales" class="block pl-8 py-1.5 text-sm text-zinc-500 hover:text-indigo-600 transition-colors">6.2 Egresos Especiales (Mermas y Ajustes)</a></li>
                            <li><a href="#historial-stock" class="block pl-8 py-1.5 text-sm text-zinc-500 hover:text-indigo-600 transition-colors">6.3 Historial de Movimientos</a></li>
                        </ul>
                    </div>

                    <div x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center justify-between w-full px-2 mb-3 text-xs font-bold uppercase tracking-widest text-zinc-400 hover:text-indigo-600 transition-colors text-left font-sans">
                            <span>7. Auditoría Global y Reportes</span>
                            <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <ul x-show="open" x-transition class="space-y-1 border-l border-zinc-200 dark:border-zinc-800 ml-2">
                            <li><a href="#auditoria" class="block pl-4 py-2 text-sm font-medium hover:text-indigo-600">Inicio</a></li>
                            <li><a href="#historial-ventas" class="block pl-8 py-1.5 text-sm text-zinc-500 hover:text-indigo-600 transition-colors">7.1 Historial Global de Ventas</a></li>
                            <li><a href="#auditoria-detalle" class="block pl-8 py-1.5 text-sm text-zinc-500 hover:text-indigo-600 transition-colors">7.2 Auditoría Detallada de Operaciones</a></li>
                            <li><a href="#reportes-pdf" class="block pl-8 py-1.5 text-sm text-zinc-500 hover:text-indigo-600 transition-colors">7.3 Reportes y Exportación PDF</a></li>
                            <li><a href="#analytics" class="block pl-8 py-1.5 text-sm text-zinc-500 hover:text-indigo-600 transition-colors">7.4 Indicadores de Rendimiento (Analytics)</a></li>
                        </ul>
                    </div>
                </nav>
            </div>
        </aside>

        <main class="lg:ml-72 flex-1 min-h-screen">
            <div class="max-w-4xl mx-auto px-6 py-12 lg:px-16">
                
                <article id="introduccion" class="mb-24 scroll-mt-24">
                    <header class="mb-10 border-b border-zinc-100 pb-10 dark:border-zinc-800">
                        <div class="text-indigo-600 font-semibold mb-2 text-sm uppercase tracking-widest">Sección 1</div>
                        <h1 class="text-4xl font-extrabold tracking-tight text-zinc-900 dark:text-white sm:text-5xl">Introducción</h1>
                        <p class="mt-4 text-xl text-zinc-500 leading-relaxed">
                            Bienvenido a la documentación oficial de **FarmaCorp**, una solución integral de nivel empresarial para la gestión farmacéutica.
                        </p>
                    </header>

                    <section id="filosofia" class="mb-20 scroll-mt-24">
                        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-4">Filosofía del Sistema</h2>
                        <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed text-lg">
                            FarmaCorp opera bajo el principio de **Responsabilidad Operativa Directa**. Esto significa que cada acción (una venta, un ajuste de stock o un cobro) queda vinculada de forma inmutable a un usuario y a un estado de caja específico. El sistema no permite operaciones "huérfanas", asegurando que la auditoría sea total y transparente.
                        </p>
                    </section>

                    <section id="rbac" class="mb-20 scroll-mt-24">
                        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-4">Roles y Permisos (RBAC)</h2>
                        <p class="text-zinc-600 dark:text-zinc-400 mb-8 text-lg">
                            La plataforma utiliza un sistema de Control de Acceso Basado en Roles (RBAC) para restringir el acceso a módulos sensibles mediante validaciones en tiempo real.
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
                            <div class="p-8 bg-zinc-50 dark:bg-zinc-800/30 rounded-3xl border border-zinc-200/50 dark:border-zinc-800">
                                <h3 class="font-bold text-indigo-600 text-lg mb-4 flex items-center gap-2">
                                    <flux:icon.shield-check variant="micro" /> Administrador
                                </h3>
                                <ul class="text-sm space-y-3 text-zinc-600 dark:text-zinc-400 leading-relaxed">
                                    <li class="flex gap-2"><span>•</span> Gestión total de usuarios, roles y permisos directos.</li>
                                    <li class="flex gap-2"><span>•</span> Control maestro de precios, márgenes e inflación.</li>
                                    <li class="flex gap-2"><span>•</span> Auditoría global de cierres de caja y saldos de deuda.</li>
                                    <li class="flex gap-2"><span>•</span> Autorización de egresos especiales (mermas/robos).</li>
                                </ul>
                            </div>
                            <div class="p-8 bg-zinc-50 dark:bg-zinc-800/30 rounded-3xl border border-zinc-200/50 dark:border-zinc-800">
                                <h3 class="font-bold text-zinc-700 dark:text-zinc-200 text-lg mb-4 flex items-center gap-2">
                                    <flux:icon.user variant="micro" /> Empleado
                                </h3>
                                <ul class="text-sm space-y-3 text-zinc-600 dark:text-zinc-400 leading-relaxed">
                                    <li class="flex gap-2"><span>•</span> Operación reactiva del POS vinculado a turnos.</li>
                                    <li class="flex gap-2"><span>•</span> Consultas clínicas al Vademécum digital.</li>
                                    <li class="flex gap-2"><span>•</span> Registro de ingresos de stock por número de lote.</li>
                                    <li class="flex gap-2"><span>•</span> Gestión y rendición de su propia caja abierta.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="bg-red-50 border-l-4 border-red-500 p-6 my-8 rounded-r-2xl dark:bg-red-900/10">
                            <div class="flex items-center gap-2 mb-3 text-red-800 dark:text-red-400 font-bold uppercase text-xs tracking-widest">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                                Seguridad por Diseño
                            </div>
                            <p class="text-red-900 dark:text-red-300 text-sm leading-relaxed">
                                El sistema utiliza la directiva <code>@@hasanyrole</code> en la interfaz y cláusulas <code>abort_unless</code> en el controlador. Si un usuario intenta forzar una URL para la que no tiene permiso, FarmaCorp interrumpirá la ejecución devolviendo un error **403 Forbidden**.
                            </p>
                        </div>
                    </section>

                    <section id="requisitos" class="mb-20 scroll-mt-24">
                        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-6">Requisitos Previos para el Uso</h2>
                        <ul class="space-y-6 text-zinc-600 dark:text-zinc-400">
                            <li class="flex gap-4">
                                <span class="bg-indigo-600 text-white h-7 w-7 rounded-full flex items-center justify-center shrink-0 text-xs font-black shadow-md shadow-indigo-200 dark:shadow-none">1</span>
                                <div class="text-lg">
                                    <strong class="text-zinc-900 dark:text-zinc-100 block font-bold mb-1">Caja Habilitada</strong>
                                    Ningún usuario puede procesar transacciones de dinero sin una apertura de caja previa vinculada a su sesión de trabajo.
                                </div>
                            </li>
                            <li class="flex gap-4">
                                <span class="bg-indigo-600 text-white h-7 w-7 rounded-full flex items-center justify-center shrink-0 text-xs font-black shadow-md shadow-indigo-200 dark:shadow-none">2</span>
                                <div class="text-lg">
                                    <strong class="text-zinc-900 dark:text-zinc-100 block font-bold mb-1">Validación de Identidad</strong>
                                    El sistema requiere una sesión activa; los tokens caducan automáticamente tras periodos de inactividad para proteger la terminal física.
                                </div>
                            </li>
                        </ul>

                        <div class="bg-indigo-50 border-l-4 border-indigo-500 p-8 my-12 rounded-r-2xl dark:bg-indigo-900/10 shadow-sm">
                            <div class="flex items-center gap-2 mb-3 text-indigo-800 dark:text-indigo-400 font-bold uppercase text-xs tracking-widest">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM5.884 6.68a1 1 0 10-1.415-1.414l.707-.707a1 1 0 001.415 1.415l-.707.707zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zM12.364 14.778a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM11 18a1 1 0 10-2 0v-1a1 1 0 102 0v1zM4.99 11a1 1 0 100-2H4a1 1 0 100 2h.99zM6.344 13.182a1 1 0 00-1.414 1.415l.707.707a1 1 0 001.414-1.414l-.707-.707zM14.828 5.263a1 1 0 00-1.415-1.414l-.707.707a1 1 0 001.415 1.414l.707-.707z"></path></svg>
                                Integridad Histórica
                            </div>
                            <p class="text-indigo-900 dark:text-indigo-300 text-sm leading-relaxed italic">
                                FarmaCorp implementa el concepto de **Desactivación Lógica**. Los productos o clientes que ya no se utilicen no se eliminan físicamente de la base de datos, sino que se "archivan" para no romper la integridad de los reportes financieros de ejercicios anteriores.
                            </p>
                        </div>
                    </section>
                </article>

                <article id="operaciones-iniciales" class="mb-24 scroll-mt-24 border-t border-zinc-100 pt-16 dark:border-zinc-800">
                    <header class="mb-10 pb-6">
                        <div class="text-indigo-600 font-semibold mb-2 text-sm uppercase tracking-widest">Sección 2</div>
                        <h1 class="text-4xl font-extrabold tracking-tight text-zinc-900 dark:text-white sm:text-5xl">Operaciones Iniciales</h1>
                        <p class="mt-4 text-xl text-zinc-500 leading-relaxed">
                            Antes de comenzar la operativa comercial, FarmaCorp requiere la validación del estado del inventario crítico y la habilitación del flujo de fondos.
                        </p>
                    </header>

                    <section id="dashboard-alertas" class="mb-20 scroll-mt-24">
                        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-6 flex items-center gap-3">
                            <flux:icon.presentation-chart-line variant="micro" class="text-indigo-500" />
                            2.1 El Dashboard de Alertas
                        </h2>
                        <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed mb-6">
                            Al iniciar sesión, el sistema redirige automáticamente al Dashboard. Este panel es un motor preventivo que utiliza lógica de comparación en tiempo real con la base de datos para generar alertas sanitarias y comerciales.
                        </p>

                        <div class="space-y-6">
                            <div class="p-6 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl shadow-sm">
                                <h3 class="font-bold text-zinc-900 dark:text-zinc-100 mb-3 flex items-center gap-2">Configuración de Alertas (RF-05)</h3>
                                <p class="text-sm text-zinc-500 mb-4">El Administrador puede parametrizar la sensibilidad del sistema desde la cabecera del Dashboard:</p>
                                <ul class="text-sm space-y-2 text-zinc-600 dark:text-zinc-400">
                                    <li class="flex items-start gap-2">
                                        <span class="text-indigo-500 mt-1">•</span> 
                                        <span>Ingrese los días en <strong>"Período de anticipación"</strong> para definir qué tan temprano desea ver los vencimientos.</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="text-indigo-500 mt-1">•</span> 
                                        <span>Presione <strong>"Guardar"</strong> para recalcular instantáneamente el riesgo de todos los lotes.</span>
                                    </li>
                                </ul>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="p-5 border border-red-200 dark:border-red-900/30 bg-red-50/50 dark:bg-red-900/5 rounded-2xl">
                                    <h4 class="font-bold text-red-700 dark:text-red-400 text-sm uppercase mb-2">Vencimientos Próximos</h4>
                                    <p class="text-xs text-red-600 dark:text-red-300 leading-relaxed">
                                        FarmaCorp resalta en <strong>Rojo</strong> los lotes con menos de 15 días de vigencia y en <strong>Amarillo</strong> los que vencen en menos de 30 días.
                                    </p>
                                </div>
                                <div class="p-5 border border-orange-200 dark:border-orange-900/30 bg-orange-50/50 dark:bg-orange-900/5 rounded-2xl">
                                    <h4 class="font-bold text-orange-700 dark:text-orange-400 text-sm uppercase mb-2">Quiebre de Stock</h4>
                                    <p class="text-xs text-orange-600 dark:text-orange-300 leading-relaxed">
                                        Visualización automática de productos que alcanzaron su umbral mínimo de seguridad para reposición inmediata.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-indigo-50 border-l-4 border-indigo-500 p-6 my-8 rounded-r-xl dark:bg-indigo-900/10">
                            <div class="flex items-center gap-2 mb-2 text-indigo-800 dark:text-indigo-400 font-bold uppercase text-xs tracking-widest text-left">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zm1 11H9v-2h2v2zm0-4H9V7h2v2z"></path></svg>
                                Nota sobre Dinámicas
                            </div>
                            <p class="text-indigo-900 dark:text-indigo-300 text-sm">
                                Las alertas de stock mínimo son dinámicas. Tan pronto como se registre un nuevo ingreso de lote para ese producto, la alerta desaparecerá automáticamente del Dashboard.
                            </p>
                        </div>
                    </section>

                    <section id="gestion-cajas" class="mb-16 scroll-mt-24">
                        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-6 flex items-center gap-3">
                            <flux:icon.banknotes variant="micro" class="text-indigo-500" />
                            2.2 Gestión de Cajas (RF-01)
                        </h2>
                        <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed mb-8">
                            El control de caja en FarmaCorp es <strong>estricto y nominal</strong>. Ninguna transacción que implique movimiento de dinero puede realizarse sin un turno de caja abierto vinculado al usuario.
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                            <div class="space-y-4">
                                <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100 border-b border-zinc-100 pb-2">Apertura de Turno</h3>
                                <ol class="text-sm space-y-3 text-zinc-600 dark:text-zinc-400">
                                    <li class="flex gap-2"><strong>1.</strong> Diríjase al módulo de <strong>Cajas</strong>.</li>
                                    <li class="flex gap-2"><strong>2.</strong> Presione el botón <strong>"Nueva Apertura"</strong>.</li>
                                    <li class="flex gap-2"><strong>3.</strong> Defina el <strong>Monto Inicial</strong> de efectivo físico.</li>
                                    <li class="flex gap-2"><strong>4.</strong> El sistema estampa fecha, hora y responsable de forma inmutable.</li>
                                </ol>
                            </div>
                            <div class="space-y-4">
                                <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100 border-b border-zinc-100 pb-2">Auditoría y Cierre</h3>
                                <p class="text-sm text-zinc-500 mb-2">Al finalizar la jornada, presione el botón <strong>"Cerrar Turno"</strong> para realizar el arqueo:</p>
                                <ul class="text-sm space-y-2 text-zinc-600 dark:text-zinc-400">
                                    <li class="flex gap-2">• Verifique totales desglosados por Efectivo, Tarjeta y Transferencia.</li>
                                    <li class="flex gap-2">• Justifique diferencias en el campo <strong>Observaciones</strong>.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="bg-red-50 border-l-4 border-red-500 p-8 my-10 rounded-r-2xl dark:bg-red-900/10">
                            <div class="flex items-center gap-2 mb-3 text-red-800 dark:text-red-400 font-bold uppercase text-xs tracking-widest">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                                Bloqueo Preventivo
                            </div>
                            <p class="text-red-900 dark:text-red-300 text-sm leading-relaxed font-medium">
                                Una vez confirmada la rendición, el estado de la caja pasa a <strong>Cerrada</strong>. El Punto de Venta bloqueará inmediatamente cualquier intento de añadir productos para este usuario hasta que se realice una nueva apertura.
                            </p>
                        </div>
                    </section>
                </article>

                <article id="padrones" class="mb-24 scroll-mt-24 border-t border-zinc-100 pt-16 dark:border-zinc-800">
                    <header class="mb-10 pb-6">
                        <div class="text-indigo-600 font-semibold mb-2 text-sm uppercase tracking-widest">Sección 3</div>
                        <h1 class="text-4xl font-extrabold tracking-tight text-zinc-900 dark:text-white sm:text-5xl">Administración de Padrones</h1>
                        <p class="mt-4 text-xl text-zinc-500 leading-relaxed">
                            Los padrones representan los datos maestros del sistema. FarmaCorp utiliza un modelo de **Integridad Referencial**, protegiendo la información contra eliminaciones accidentales.
                        </p>
                    </header>

                    <section id="gestion-clientes" class="mb-20 scroll-mt-24">
                        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-6 flex items-center gap-3">
                            <flux:icon.users variant="micro" class="text-indigo-500" />
                            3.1 Gestión de Clientes
                        </h2>
                        <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed mb-8">
                            Este módulo centraliza la información de pacientes y compradores habituales, permitiendo habilitar funciones de **Cuenta Corriente** y seguimiento de consumos.
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                            <div class="p-6 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl shadow-sm">
                                <h3 class="font-bold text-zinc-900 dark:text-zinc-100 mb-3">Registro y Edición</h3>
                                <ul class="text-sm space-y-3 text-zinc-600 dark:text-zinc-400">
                                    <li class="flex gap-2"><span>1.</span> Acceda a <strong>Directorio de Clientes</strong>.</li>
                                    <li class="flex gap-2"><span>2.</span> Complete Nombres, Apellidos, Teléfono y Dirección.</li>
                                    <li class="flex gap-2"><span>3.</span> El sistema validará que el <strong>Email</strong> sea único para evitar registros duplicados.</li>
                                </ul>
                            </div>
                            <div class="p-6 bg-zinc-50 dark:bg-zinc-800/50 rounded-2xl border border-zinc-200 dark:border-zinc-700">
                                <h3 class="font-bold text-zinc-900 dark:text-zinc-100 mb-3">Desactivación Lógica (RF-10)</h3>
                                <p class="text-sm text-zinc-500 leading-relaxed">
                                    Al presionar el icono de la <strong>Papelera</strong>, el cliente pasa a estado Inactivo. Esto lo oculta de las búsquedas en el POS pero mantiene sus deudas y compras históricas intactas para auditoría.
                                </p>
                            </div>
                        </div>

                        <div class="bg-indigo-50 border-l-4 border-indigo-500 p-6 my-8 rounded-r-xl dark:bg-indigo-900/10">
                            <div class="flex items-center gap-2 mb-2 text-indigo-800 dark:text-indigo-400 font-bold uppercase text-xs tracking-widest text-left text-sm">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zm1 11H9v-2h2v2zm0-4H9V7h2v2z"></path></svg>
                                Nota para Administradores
                            </div>
                            <p class="text-indigo-900 dark:text-indigo-300 text-sm">
                                Un Administrador puede reactivar a un cliente en cualquier momento mediante el botón <strong>"Flecha de Retorno"</strong> en el listado principal, restaurando su acceso al POS.
                            </p>
                        </div>
                    </section>

                    <section id="catalogo-productos" class="mb-20 scroll-mt-24">
                        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-6 flex items-center gap-3">
                            <flux:icon.beaker variant="micro" class="text-indigo-500" />
                            3.2 Catálogo de Productos y Medicamentos
                        </h2>
                        <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed mb-6">
                            FarmaCorp unifica la gestión de insumos comerciales y fármacos regulados mediante una arquitectura de extensión de datos técnicos.
                        </p>
                        
                        

                        <div class="space-y-6 mb-10">
                            <div class="p-6 border border-zinc-200 dark:border-zinc-700 rounded-2xl">
                                <h3 class="font-bold text-indigo-600 mb-2">Configuración Avanzada de Medicamentos</h3>
                                <p class="text-sm text-zinc-500 mb-4">Activar el switch <strong>"Es un Medicamento"</strong> desbloquea el panel clínico:</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                                    <p>• <strong>Grupo Farmacológico:</strong> Clasificación según Vademécum.</p>
                                    <p>• <strong>Dosificación:</strong> Especificación (ej. 500mg / Jarabe).</p>
                                    <p>• <strong>Prospecto:</strong> Información clínica para consulta en venta.</p>
                                    <p>• <strong>Psicotrópico:</strong> Activa el badge de alerta sanitaria.</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section id="control-precios" class="mb-16 scroll-mt-24">
                        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-6 flex items-center gap-3">
                            <flux:icon.currency-dollar variant="micro" class="text-indigo-500" />
                            3.3 Control de Precios e Inflación (RF-17 / RF-18)
                        </h2>
                        <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed mb-8">
                            FarmaCorp implementa un sistema de <strong>Protección de Ganancia</strong> que audita la antigüedad de los precios de forma automática.
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <div class="p-6 bg-zinc-50 dark:bg-zinc-800/30 rounded-2xl border border-zinc-100 dark:border-zinc-800">
                                <h4 class="font-bold text-zinc-900 dark:text-zinc-100 text-sm mb-2">Antigüedad (price_updated_at)</h4>
                                <p class="text-xs text-zinc-500 leading-relaxed">
                                    Registra el momento exacto del último cambio. Si supera el límite del Admin, el producto se bloquea en el POS por desactualización.
                                </p>
                            </div>
                            <div class="p-6 bg-zinc-50 dark:bg-zinc-800/30 rounded-3xl border border-zinc-100 dark:border-zinc-800">
                                <h4 class="font-bold text-zinc-900 dark:text-zinc-100 text-sm mb-2">Vencimiento (price_expires_at)</h4>
                                <p class="text-xs text-zinc-500 leading-relaxed">
                                    Define una fecha límite para listas de precios u ofertas. Al cumplirse, impide la facturación del producto.
                                </p>
                            </div>
                        </div>

                        <div class="bg-red-50 border-l-4 border-red-500 p-8 my-10 rounded-r-2xl dark:bg-red-900/10 shadow-lg shadow-red-500/5">
                            <div class="flex items-center gap-2 mb-3 text-red-800 dark:text-red-400 font-bold uppercase text-xs tracking-widest">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                                Bloqueo Automático Preventivo
                            </div>
                            <p class="text-red-900 dark:text-red-300 text-sm leading-relaxed font-medium">
                                Si un producto detecta desactualización o vencimiento comercial, su tarjeta en el Punto de Venta se mostrará en <strong>Escala de Grises</strong> y el sistema impedirá su adición al carrito de compras, forzando la actualización manual desde este módulo.
                            </p>
                        </div>
                    </section>
                </article>

                <article id="pos" class="mb-24 scroll-mt-24 border-t border-zinc-100 pt-16 dark:border-zinc-800">
                    <header class="mb-10 pb-6">
                        <div class="text-indigo-600 font-semibold mb-2 text-sm uppercase tracking-widest">Sección 4</div>
                        <h1 class="text-4xl font-extrabold tracking-tight text-zinc-900 dark:text-white sm:text-5xl">Punto de Venta (POS)</h1>
                        <p class="mt-4 text-xl text-zinc-500 leading-relaxed">
                            La interfaz POS de FarmaCorp es un entorno reactivo de alto rendimiento diseñado para minimizar errores humanos y garantizar el cumplimiento normativo en cada ticket.
                        </p>
                    </header>

                    <section id="ciclo-venta" class="mb-20 scroll-mt-24">
                        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-6 flex items-center gap-3">
                            <flux:icon.shopping-cart variant="micro" class="text-indigo-500" />
                            4.1 Ciclo de Venta y Carrito
                        </h2>
                        <p class="text-zinc-600 dark:text-zinc-400 mb-8">
                            La pantalla se divide en dos áreas críticas: el <strong>Catálogo (Izquierda)</strong> para selección rápida y el <strong>Resumen de Cobro (Derecha)</strong> para la liquidación.
                        </p>

                        <div class="space-y-4 mb-8">
                            <div class="flex items-start gap-4 p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl">
                                <div class="font-bold text-indigo-600">Búsqueda:</div>
                                <p class="text-sm text-zinc-500 italic leading-relaxed">Implementa tecnología Debounce. Al escribir en la barra central, el sistema espera milisegundos antes de consultar, optimizando la velocidad del servidor.</p>
                            </div>
                            <div class="flex items-start gap-4 p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl">
                                <div class="font-bold text-indigo-600 text-nowrap">Comprobante:</div>
                                <p class="text-sm text-zinc-500 leading-relaxed">Es obligatorio elegir el tipo (Ticket, Factura A/B) antes de cargar productos para pre-configurar los impuestos correspondientes.</p>
                            </div>
                        </div>

                        <div class="bg-red-50 border-l-4 border-red-500 p-6 my-8 rounded-r-xl dark:bg-red-900/10">
                            <div class="flex items-center gap-2 mb-2 text-red-800 dark:text-red-400 font-bold uppercase text-xs tracking-widest">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
                                Caja Cerrada
                            </div>
                            <p class="text-red-900 dark:text-red-300 text-sm font-medium">
                                Si no hay un turno activo, el panel derecho mostrará un bloqueo total. FarmaCorp no permite facturar "en el aire" sin un responsable de caja vinculado.
                            </p>
                        </div>
                    </section>

                    <section id="ayudas-atencion" class="mb-20 scroll-mt-24">
                        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-6 flex items-center gap-3">
                            <flux:icon.heart variant="micro" class="text-indigo-500" />
                            4.2 Ayudas de Atención al Paciente
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
                            <div class="p-6 border border-zinc-200 dark:border-zinc-700 rounded-3xl">
                                <div class="bg-indigo-100 dark:bg-indigo-900/30 w-10 h-10 rounded-full flex items-center justify-center mb-4 text-indigo-600">
                                    <flux:icon.information-circle variant="mini" />
                                </div>
                                <h3 class="font-bold text-zinc-900 dark:text-zinc-100 mb-2">Prospectos (RF-15)</h3>
                                <p class="text-xs text-zinc-500 leading-relaxed">Pulse el icono "i" para abrir el modal clínico. Permite leer indicaciones y advertencias (ej. Psicotrópicos) sin salir de la venta actual.</p>
                            </div>
                            <div class="p-6 border border-zinc-200 dark:border-zinc-700 rounded-3xl">
                                <div class="bg-indigo-100 dark:bg-indigo-900/30 w-10 h-10 rounded-full flex items-center justify-center mb-4 text-indigo-600">
                                    <flux:icon.user-plus variant="mini" />
                                </div>
                                <h3 class="font-bold text-zinc-900 dark:text-zinc-100 mb-2">Cliente (RF-22)</h3>
                                <p class="text-xs text-zinc-500 leading-relaxed">Vincule la venta a un cliente del padrón para habilitar el pago a <strong>Cuenta Corriente</strong>. Por defecto, se asigna "Consumidor Final".</p>
                            </div>
                        </div>
                    </section>

                    <section id="escudo-financiero" class="mb-20 scroll-mt-24">
                        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-6 flex items-center gap-3">
                            <flux:icon.shield-check variant="micro" class="text-indigo-500" />
                            4.3 Escudo de Seguridad Financiera
                        </h2>
                        
                        <div class="overflow-hidden border border-zinc-200 dark:border-zinc-800 rounded-2xl mb-10">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-zinc-50 dark:bg-zinc-950 text-zinc-500 uppercase text-xs font-bold">
                                    <tr>
                                        <th class="px-6 py-4 italic">Estado Visual</th>
                                        <th class="px-6 py-4">Motivo del Bloqueo</th>
                                        <th class="px-6 py-4">Acción Requerida</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                    <tr>
                                        <td class="px-6 py-4 font-medium text-zinc-400">Escala de Grises</td>
                                        <td class="px-6 py-4 text-zinc-500">Sin Stock disponible en lotes.</td>
                                        <td class="px-6 py-4 text-indigo-600 font-semibold underline underline-offset-4">Ingreso de Lote</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 font-medium text-orange-500">Borde Naranja</td>
                                        <td class="px-6 py-4 text-zinc-500">Precio Antiguo (Inflación).</td>
                                        <td class="px-6 py-4 text-indigo-600 font-semibold underline underline-offset-4">Actualizar Padrón</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 font-medium text-red-600">Borde Rojo</td>
                                        <td class="px-6 py-4 text-zinc-500">Precio Vencido (Oferta Caducada).</td>
                                        <td class="px-6 py-4 text-indigo-600 font-semibold underline underline-offset-4">Nueva Fecha/Precio</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section id="pagos-multimedio" class="mb-16 scroll-mt-24">
                        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-6 flex items-center gap-3">
                            <flux:icon.bolt variant="micro" class="text-indigo-500" />
                            4.4 Gestión de Pagos Multimedio
                        </h2>
                        <p class="text-zinc-600 dark:text-zinc-400 mb-8 leading-relaxed">
                            FarmaCorp permite la liquidación flexible de la venta, admitiendo múltiples métodos de pago en una sola transacción.
                        </p>

                        <div class="bg-zinc-900 text-zinc-300 p-8 rounded-3xl shadow-2xl mb-10">
                            <div class="flex items-center gap-4 mb-6">
                                <div class="p-3 bg-yellow-500 text-black rounded-full animate-pulse">
                                    <flux:icon.bolt variant="mini" />
                                </div>
                                <div>
                                    <h4 class="text-white font-bold text-lg">El Botón del Rayo</h4>
                                    <p class="text-sm text-zinc-400">Acelere el cierre de venta. Un solo clic y el sistema calcula el saldo restante exacto.</p>
                                </div>
                            </div>
                            <ul class="space-y-4 text-sm">
                                <li class="flex items-center gap-3"><flux:icon.check class="text-green-400" /> <strong>Ajuste Global:</strong> Aplique descuentos manuales finales.</li>
                                <li class="flex items-center gap-3"><flux:icon.check class="text-green-400" /> <strong>Monto Parcial:</strong> Registre pagos parciales con el botón "+".</li>
                            </ul>
                        </div>

                        <div class="bg-indigo-50 border-l-4 border-indigo-500 p-6 my-10 rounded-r-xl dark:bg-indigo-900/10">
                            <div class="flex items-center gap-2 mb-2 text-indigo-800 dark:text-indigo-400 font-bold uppercase text-xs tracking-widest text-left text-sm">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"></path><path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"></path></svg>
                                Venta a Cuenta Corriente
                            </div>
                            <p class="text-indigo-900 dark:text-indigo-300 text-sm leading-relaxed">
                                Si el pago total no cubre la venta, el sistema habilitará el botón <strong>"Vender con Saldo Deudor"</strong> automáticamente, vinculando el remanente a la ficha del cliente.
                            </p>
                        </div>
                    </section>
                </article>

                <article id="finanzas" class="mb-24 scroll-mt-24 border-t border-zinc-100 pt-16 dark:border-zinc-800">
                    <header class="mb-10 pb-6">
                        <div class="text-indigo-600 font-semibold mb-2 text-sm uppercase tracking-widest">Sección 5</div>
                        <h1 class="text-4xl font-extrabold tracking-tight text-zinc-900 dark:text-white sm:text-5xl">Finanzas y Cuentas Corrientes</h1>
                        <p class="mt-4 text-xl text-zinc-500 leading-relaxed">
                            FarmaCorp incluye un módulo de gestión de deudas que permite administrar ventas a crédito de forma segura, garantizando que cada ingreso posterior sea auditado y vinculado a su origen.
                        </p>
                    </header>

                    <section id="monitoreo-saldos" class="mb-20 scroll-mt-24">
                        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-6 flex items-center gap-3">
                            <flux:icon.presentation-chart-bar variant="micro" class="text-indigo-500" />
                            5.1 Monitoreo de Saldos (RF-16)
                        </h2>
                        <p class="text-zinc-600 dark:text-zinc-400 mb-8 leading-relaxed">
                            El panel de Saldos ofrece una visión global de la salud financiera del negocio respecto a sus clientes deudores en tiempo real.
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                            <div class="p-6 bg-indigo-600 rounded-2xl shadow-lg shadow-indigo-500/20 text-white">
                                <div class="text-xs uppercase font-bold opacity-80 mb-1">Total a Cobrar</div>
                                <div class="text-2xl font-black">$ Suma Dinámica</div>
                                <p class="text-[10px] mt-2 opacity-70 italic">Calculado sobre saldos pendientes reales.</p>
                            </div>
                            <div class="p-6 bg-zinc-50 dark:bg-zinc-800/50 rounded-2xl border border-zinc-100 dark:border-zinc-800">
                                <h4 class="font-bold text-zinc-900 dark:text-zinc-100 text-sm mb-2 flex items-center gap-2">
                                    <span class="w-3 h-3 bg-red-500 rounded-full"></span> Badge Rojo
                                </h4>
                                <p class="text-xs text-zinc-500 leading-relaxed">Cliente con deuda activa. El saldo se actualiza con cada pago parcial.</p>
                            </div>
                            <div class="p-6 bg-zinc-50 dark:bg-zinc-800/50 rounded-2xl border border-zinc-100 dark:border-zinc-800">
                                <h4 class="font-bold text-zinc-900 dark:text-zinc-100 text-sm mb-2 flex items-center gap-2">
                                    <span class="w-3 h-3 bg-green-500 rounded-full"></span> Badge Verde
                                </h4>
                                <p class="text-xs text-zinc-500 leading-relaxed">Cliente "Al Día". El historial de compras permanece accesible para consulta.</p>
                            </div>
                        </div>

                        <div class="bg-indigo-50 border-l-4 border-indigo-500 p-6 my-8 rounded-r-xl dark:bg-indigo-900/10">
                            <div class="flex items-center gap-2 mb-2 text-indigo-800 dark:text-indigo-400 font-bold uppercase text-xs tracking-widest text-sm text-left">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"></path></svg>
                                Identificación de Deudores
                            </div>
                            <p class="text-indigo-900 dark:text-indigo-300 text-sm italic">
                                El buscador superior permite filtrar clientes por nombre o teléfono, facilitando la identificación inmediata durante la atención en mostrador.
                            </p>
                        </div>
                    </section>

                    <section id="proceso-cobranza" class="mb-20 scroll-mt-24">
                        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-6 flex items-center gap-3">
                            <flux:icon.arrow-path-rounded-square variant="micro" class="text-indigo-500" />
                            5.2 Proceso de Cobranza Multimedio
                        </h2>
                        <p class="text-zinc-600 dark:text-zinc-400 mb-8 leading-relaxed">
                            Para registrar un pago, acceda al modal de gestión mediante el icono de **"Ojo"**. El sistema utiliza una interfaz idéntica al POS para eliminar la curva de aprendizaje.
                        </p>

                        

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                            <div class="p-8 border border-zinc-200 dark:border-zinc-700 rounded-3xl">
                                <h3 class="font-bold text-indigo-600 text-lg mb-4">Selección de Comprobantes</h3>
                                <ul class="text-sm space-y-4 text-zinc-600 dark:text-zinc-400">
                                    <li class="flex gap-2"><strong>1.</strong> Localice la factura en la pestaña "Deudas Pendientes".</li>
                                    <li class="flex gap-2"><strong>2.</strong> Presione el icono <strong>"+" (Cobrar)</strong> para cargar los datos financieros de ese comprobante.</li>
                                    <li class="flex gap-2 text-indigo-500 italic">★ El sistema carga automáticamente el saldo remanente, no el total original.</li>
                                </ul>
                            </div>
                            <div class="p-8 bg-zinc-900 text-zinc-300 rounded-3xl shadow-xl">
                                <h3 class="font-bold text-white text-lg mb-4">Registro del Pago</h3>
                                <ul class="text-xs space-y-4">
                                    <li><strong class="text-indigo-400">Pagos Parciales:</strong> Se aceptan montos menores al total de la deuda. El sistema recalcula la deuda al instante.</li>
                                    <li><strong class="text-indigo-400">Pagos Combinados:</strong> Al igual que en el POS, puede cobrar parte en Efectivo y el resto por Transferencia.</li>
                                    <li><strong class="text-indigo-400">Botón Rayo:</strong> Autocompleta el monto con el total exacto de la factura seleccionada.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="bg-indigo-50 border-l-4 border-indigo-500 p-6 my-8 rounded-r-xl dark:bg-indigo-900/10">
                            <div class="flex items-center gap-2 mb-2 text-indigo-800 dark:text-indigo-400 font-bold uppercase text-xs tracking-widest text-sm text-left">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                Cierre Automático de Factura
                            </div>
                            <p class="text-indigo-900 dark:text-indigo-300 text-sm">
                                Cuando el saldo llega a **$0.00**, FarmaCorp cambia el estado de <code>PENDIENTE</code> a <code>PAGADO</code> automáticamente, moviendo la transacción al historial histórico.
                            </p>
                        </div>
                    </section>

                    <section id="historial-auditoria" class="mb-20 scroll-mt-24">
                        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-6 flex items-center gap-3">
                            <flux:icon.clock variant="micro" class="text-indigo-500" />
                            5.3 Historial de Compras y Auditoría (RF-24)
                        </h2>
                        <div class="space-y-6 mb-10">
                            <div class="p-6 bg-zinc-50 dark:bg-zinc-800/30 rounded-2xl border border-zinc-100 dark:border-zinc-800">
                                <div class="flex flex-col md:flex-row gap-6">
                                    <div class="flex-1">
                                        <h4 class="font-bold text-zinc-900 dark:text-zinc-100 mb-2">Centro de Información</h4>
                                        <p class="text-xs text-zinc-500 leading-relaxed mb-4">La pestaña <strong>"Historial"</strong> ofrece una línea de tiempo inmutable de todas las operaciones del cliente.</p>
                                        <ul class="text-xs space-y-2 text-zinc-600 dark:text-zinc-400">
                                            <li>• <strong>Detalle de Productos:</strong> Vea qué llevó y quién lo atendió.</li>
                                            <li>• <strong>Ruta de Pago:</strong> Desglose de cómo se pagó originalmente.</li>
                                            <li>• <strong>Descarga PDF:</strong> Botón para re-emitir el comprobante.</li>
                                        </ul>
                                    </div>
                                    <div class="w-full md:w-48 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-4 flex items-center justify-center text-center italic text-[10px] text-zinc-400">
                                        Representación visual de la línea de tiempo de ventas.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section id="integracion-caja" class="mb-16 scroll-mt-24">
                        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-6 flex items-center gap-3">
                            <flux:icon.banknotes variant="micro" class="text-indigo-500" />
                            5.4 Integración con Caja (RF-01)
                        </h2>
                        <p class="text-zinc-600 dark:text-zinc-400 mb-8 leading-relaxed">
                            Cada cobro de deuda genera un registro automático de **INGRESO** en la caja del usuario activo, vinculando el dinero a la factura saldada.
                        </p>

                        <div class="bg-red-50 border-l-4 border-red-500 p-8 my-10 rounded-r-2xl dark:bg-red-900/10">
                            <div class="flex items-center gap-2 mb-3 text-red-800 dark:text-red-400 font-bold uppercase text-xs tracking-widest">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.367zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"></path></svg>
                                Validación de Responsabilidad
                            </div>
                            <p class="text-red-900 dark:text-red-300 text-sm font-bold">
                                Sin una caja abierta, el botón "Confirmar Cobro" permanecerá bloqueado. FarmaCorp no permite ingresos de efectivo "fantasma" sin un responsable contable.
                            </p>
                        </div>
                    </section>
                </article>

                <article id="stock" class="mb-24 scroll-mt-24 border-t border-zinc-100 pt-16 dark:border-zinc-800">
                    <header class="mb-10 pb-6">
                        <div class="text-indigo-600 font-semibold mb-2 text-sm uppercase tracking-widest text-left">Sección 6</div>
                        <h1 class="text-4xl font-extrabold tracking-tight text-zinc-900 dark:text-white sm:text-5xl">Gestión de Stock y Trazabilidad</h1>
                        <p class="mt-4 text-xl text-zinc-500 leading-relaxed">
                            A diferencia de un comercio convencional, el stock en FarmaCorp utiliza una arquitectura de **Inventario por Lotes**, permitiendo la coexistencia de productos con distintas fechas de vencimiento y procedencias.
                        </p>
                    </header>

                    <section id="ingreso-lotes" class="mb-20 scroll-mt-24">
                        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-6 flex items-center gap-3">
                            <flux:icon.archive-box-arrow-down variant="micro" class="text-indigo-500" />
                            6.1 Ingreso de Mercadería (Lotes)
                        </h2>
                        <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed mb-8">
                            Cada ingreso crea una entidad de **Lote** única. Este es el punto de partida para el rastreo sanitario y comercial hasta el consumidor final.
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
                            <div class="p-6 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-3xl">
                                <h3 class="font-bold text-zinc-900 dark:text-zinc-100 mb-4 flex items-center gap-2">
                                    <span class="p-1.5 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg text-indigo-600"><flux:icon.magnifying-glass variant="micro"/></span>
                                    Selección e Identidad
                                </h3>
                                <p class="text-xs text-zinc-500 leading-relaxed">
                                    Identifique el producto mediante el buscador. Es obligatorio ingresar el <strong>Número de Lote</strong> y la <strong>Fecha de Vencimiento</strong> sanitaria para habilitar la trazabilidad.
                                </p>
                            </div>
                            <div class="p-6 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-3xl">
                                <h3 class="font-bold text-zinc-900 dark:text-zinc-100 mb-4 flex items-center gap-2">
                                    <span class="p-1.5 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg text-indigo-600"><flux:icon.bell-alert variant="micro"/></span>
                                    Alertas de Seguridad
                                </h3>
                                <p class="text-xs text-zinc-500 leading-relaxed">
                                    Defina el <strong>Stock Mínimo</strong> por lote. El Dashboard disparará una alerta visual cuando las unidades caigan por debajo de este umbral específico.
                                </p>
                            </div>
                        </div>

                        <div class="bg-indigo-50 border-l-4 border-indigo-500 p-8 my-10 rounded-r-2xl dark:bg-indigo-900/10">
                            <div class="flex items-center gap-2 mb-3 text-indigo-800 dark:text-indigo-400 font-bold uppercase text-xs tracking-widest text-left">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"></path></svg>
                                Lógica FEFO (First Expired, First Out)
                            </div>
                            <p class="text-indigo-900 dark:text-indigo-300 text-sm leading-relaxed italic text-left">
                                FarmaCorp está programado para sugerir y descontar automáticamente los productos del lote con el <strong>vencimiento más cercano</strong> durante la venta, optimizando la rotación y reduciendo pérdidas por caducidad sanitaria.
                            </p>
                        </div>
                    </section>

                    <section id="egresos-especiales" class="mb-20 scroll-mt-24">
                        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-6 flex items-center gap-3 text-left">
                            <flux:icon.minus-circle variant="micro" class="text-indigo-500" />
                            6.2 Egresos Especiales (Mermas y Ajustes)
                        </h2>
                        <p class="text-zinc-600 dark:text-zinc-400 mb-8 leading-relaxed text-left">
                            Este módulo permite retirar unidades que no pasaron por el Punto de Venta, manteniendo la fidelidad absoluta entre el stock del sistema y el stock físico del depósito.
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
                            <div class="p-4 border border-zinc-100 dark:border-zinc-800 rounded-xl text-center">
                                <span class="text-xs font-bold text-indigo-500 uppercase">Mermas</span>
                                <p class="text-[10px] text-zinc-500 mt-1 italic">Daños físicos</p>
                            </div>
                            <div class="p-4 border border-zinc-100 dark:border-zinc-800 rounded-xl text-center">
                                <span class="text-xs font-bold text-indigo-500 uppercase">Vencimiento</span>
                                <p class="text-[10px] text-zinc-500 mt-1 italic">Caducidad sanitaria</p>
                            </div>
                            <div class="p-4 border border-zinc-100 dark:border-zinc-800 rounded-xl text-center">
                                <span class="text-xs font-bold text-indigo-500 uppercase">Robo/Pérdida</span>
                                <p class="text-[10px] text-zinc-500 mt-1 italic">Ajuste de inventario</p>
                            </div>
                            <div class="p-4 border border-zinc-100 dark:border-zinc-800 rounded-xl text-center">
                                <span class="text-xs font-bold text-indigo-500 uppercase">Devolución</span>
                                <p class="text-[10px] text-zinc-500 mt-1 italic">Retorno a proveedor</p>
                            </div>
                        </div>

                        <div class="bg-zinc-900 text-zinc-300 p-8 rounded-3xl shadow-2xl mb-10">
                            <h3 class="text-white font-bold text-lg mb-4 flex items-center gap-2">
                                <flux:icon.qr-code variant="mini" class="text-indigo-400" />
                                Selección por Trazabilidad
                            </h3>
                            <p class="text-sm leading-relaxed mb-4">
                                A diferencia del ingreso, el egreso requiere que el usuario seleccione el <strong>Lote Específico</strong>. 
                            </p>
                            <p class="text-xs text-zinc-400 italic">
                                Esto asegura que si se rompe un frasco de un lote X, el descuento afecte solo a ese sub-grupo, manteniendo la integridad de los reportes sanitarios.
                            </p>
                        </div>
                    </section>

                    <section id="historial-stock" class="mb-16 scroll-mt-24">
                        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-6 flex items-center gap-3">
                            <flux:icon.bars-3-bottom-left variant="micro" class="text-indigo-500" />
                            6.3 Historial de Movimientos
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                            <div class="space-y-4">
                                <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100 border-b border-zinc-100 pb-2">Auditoría de Movimientos</h3>
                                <p class="text-sm text-zinc-500 leading-relaxed">
                                    Bitácora inmutable: registre quién (usuario), cuándo (fecha/hora), por qué (motivo) y de qué lote se movió mercadería.
                                </p>
                            </div>
                            <div class="space-y-4">
                                <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100 border-b border-zinc-100 pb-2">Valorización de Stock</h3>
                                <p class="text-sm text-zinc-500 leading-relaxed">
                                    Permite observar el total de capital inmovilizado desglosado por las cantidades actuales de cada lote activo y su precio de costo asociado.
                                </p>
                            </div>
                        </div>

                        <div class="bg-red-50 border-l-4 border-red-500 p-8 my-10 rounded-r-2xl dark:bg-red-900/10 shadow-lg shadow-red-500/5 text-left">
                            <div class="flex items-center gap-2 mb-3 text-red-800 dark:text-red-400 font-bold uppercase text-xs tracking-widest text-left">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                                Integridad de Datos e Inmutabilidad
                            </div>
                            <p class="text-red-900 dark:text-red-300 text-sm leading-relaxed font-bold italic">
                                Los movimientos de stock registrados NO pueden ser eliminados. En caso de error en la carga, se debe realizar un movimiento de compensación (ej: un egreso por "Ajuste de Carga"). Esto garantiza que el historial de auditoría sea 100% auditable legalmente.
                            </p>
                        </div>
                    </section>
                </article>

                <article id="auditoria" class="mb-24 scroll-mt-24 border-t border-zinc-100 pt-16 dark:border-zinc-800">
                    <header class="mb-10 pb-6">
                        <div class="text-indigo-600 font-semibold mb-2 text-sm uppercase tracking-widest text-left">Sección 7</div>
                        <h1 class="text-4xl font-extrabold tracking-tight text-zinc-900 dark:text-white sm:text-5xl">Auditoría Global y Reportes</h1>
                        <p class="mt-4 text-xl text-zinc-500 leading-relaxed">
                            La transparencia informativa es el pilar de FarmaCorp. El sistema consolida cada transacción, permitiendo un seguimiento exhaustivo de flujos de fondos y comportamiento comercial.
                        </p>
                    </header>

                    <section id="historial-ventas" class="mb-20 scroll-mt-24">
                        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-6 flex items-center gap-3">
                            <flux:icon.funnel variant="micro" class="text-indigo-500" />
                            7.1 Historial Global de Ventas (RF-25)
                        </h2>
                        <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed mb-8">
                            El módulo de <strong>Gestión de Ventas</strong> centraliza la actividad de todas las terminales. Para optimizar la búsqueda, FarmaCorp implementa filtros de alta precisión:
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                            <div class="p-5 bg-white dark:bg-zinc-800 border border-zinc-100 dark:border-zinc-700 rounded-2xl">
                                <h4 class="font-bold text-zinc-900 dark:text-zinc-100 text-sm mb-2">Filtros Dinámicos</h4>
                                <ul class="text-xs text-zinc-500 space-y-2">
                                    <li>• <strong>Cliente:</strong> Aísle facturas por paciente para historial crediticio.</li>
                                    <li>• <strong>Comprobante:</strong> Separe Tickets de Facturas A/B rápidamente.</li>
                                    <li>• <strong>Fechas:</strong> Cruce rangos (Desde/Hasta) para auditorías de turno.</li>
                                    <li>• <strong>Responsable:</strong> Filtre ventas por empleado específico.</li>
                                </ul>
                            </div>
                            <div class="p-5 bg-indigo-50 dark:bg-indigo-900/10 border border-indigo-100 dark:border-indigo-900/30 rounded-2xl flex items-center">
                                <p class="text-xs text-indigo-700 dark:text-indigo-300 italic">
                                    <strong>Tip de Eficiencia:</strong> El sistema incluye un botón de "Limpiar Filtros" que restaura la vista global instantáneamente, eliminando todas las restricciones de búsqueda.
                                </p>
                            </div>
                        </div>

                        <div class="bg-indigo-50 border-l-4 border-indigo-500 p-6 my-8 rounded-r-xl dark:bg-indigo-900/10">
                            <div class="flex items-center gap-2 mb-2 text-indigo-800 dark:text-indigo-400 font-bold uppercase text-xs tracking-widest text-left text-sm">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM5.884 6.68a1 1 0 10-1.415-1.414l.707-.707a1 1 0 001.415 1.415l-.707.707zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zM12.364 14.778a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM11 18a1 1 0 10-2 0v-1a1 1 0 102 0v1zM4.99 11a1 1 0 100-2H4a1 1 0 100 2h.99zM6.344 13.182a1 1 0 00-1.414 1.415l.707.707a1 1 0 001.414-1.414l-.707-.707zM14.828 5.263a1 1 0 00-1.415-1.414l-.707.707a1 1 0 001.415 1.414l.707-.707z"></path></svg>
                                Reactividad Total (Livewire)
                            </div>
                            <p class="text-indigo-900 dark:text-indigo-300 text-sm">
                                Los filtros no requieren recargar la página. Al modificar cualquier parámetro, la tabla se actualiza automáticamente, ahorrando tiempo operativo crítico.
                            </p>
                        </div>
                    </section>

                    <section id="auditoria-detalle" class="mb-20 scroll-mt-24">
                        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-6 flex items-center gap-3">
                            <flux:icon.eye variant="micro" class="text-indigo-500" />
                            7.2 Auditoría Detallada de Operaciones
                        </h2>
                        <p class="text-zinc-600 dark:text-zinc-400 mb-8 leading-relaxed">
                            Al presionar el icono del <strong>"Ojo"</strong> en cualquier registro, se accede a la anatomía completa de la transacción:
                        </p>

                        <div class="space-y-4 mb-10">
                            <div class="p-6 border border-zinc-200 dark:border-zinc-800 rounded-3xl">
                                <ul class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm text-zinc-600 dark:text-zinc-400">
                                    <li><strong class="text-zinc-900 dark:text-zinc-100 block">Cabecera de Control</strong> ID único, tipo de factura y responsable del turno.</li>
                                    <li><strong class="text-zinc-900 dark:text-zinc-100 block">Desglose de Ítems</strong> Productos, precios unitarios y cantidades históricas.</li>
                                    <li><strong class="text-zinc-900 dark:text-zinc-100 block">Flujo de Fondos</strong> Detalle exacto de cuánto se cobró en Efectivo, Tarjeta o Transferencia.</li>
                                    <li><strong class="text-zinc-900 dark:text-zinc-100 block">Observaciones</strong> Comentarios cargados durante el cierre de caja o cobro de deuda.</li>
                                </ul>
                            </div>
                        </div>

                        
                    </section>

                    <section id="reportes-pdf" class="mb-20 scroll-mt-24">
                        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-6 flex items-center gap-3">
                            <flux:icon.document-arrow-down variant="micro" class="text-indigo-500" />
                            7.3 Reportes y Exportación PDF (RF-19)
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                            <div class="p-8 bg-zinc-900 text-zinc-300 rounded-3xl shadow-xl">
                                <h3 class="text-white font-bold mb-4">Descarga Bajo Demanda</h3>
                                <p class="text-xs leading-relaxed opacity-80">
                                    El sistema genera documentos en formato PDF optimizados para impresión. Incluyen el logo de <strong>FarmaCorp</strong>, datos del cliente, trazabilidad de lotes y estado de deuda (Pagado/Pendiente).
                                </p>
                            </div>
                            <div class="flex items-center justify-center p-8 bg-zinc-50 dark:bg-zinc-800/30 rounded-3xl border-2 border-dashed border-zinc-200 dark:border-zinc-700">
                                <p class="text-[10px] text-zinc-400 text-center italic">Representación visual del comprobante generado por el sistema.</p>
                            </div>
                        </div>

                        <div class="bg-red-50 border-l-4 border-red-500 p-8 my-10 rounded-r-2xl dark:bg-red-900/10 shadow-lg shadow-red-500/5">
                            <div class="flex items-center gap-2 mb-3 text-red-800 dark:text-red-400 font-bold uppercase text-xs tracking-widest text-left">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001z" clip-rule="evenodd"></path></svg>
                                Integridad Documental e Inmutabilidad
                            </div>
                            <p class="text-red-900 dark:text-red-300 text-sm leading-relaxed font-bold italic">
                                Los PDFs generados son una "fotografía" de la base de datos al momento de la venta. Cualquier cambio posterior en los precios del padrón no alterará las facturas ya emitidas, blindando la seguridad contable histórica del negocio.
                            </p>
                        </div>
                    </section>

                    <section id="analytics" class="mb-16 scroll-mt-24">
                        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-6 flex items-center gap-3">
                            <flux:icon.chart-bar variant="micro" class="text-indigo-500" />
                            7.4 Indicadores de Rendimiento (Analytics)
                        </h2>
                        <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed mb-10 text-left">
                            La vista de administración procesa la información transaccional para ofrecer métricas clave sobre la salud del negocio:
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                            <div class="p-6 bg-white dark:bg-zinc-800 border border-zinc-100 dark:border-zinc-700 rounded-3xl text-center">
                                <div class="text-indigo-600 font-bold text-lg mb-1 italic">Mejor Día</div>
                                <p class="text-[10px] text-zinc-500 uppercase tracking-tighter">Picos de Facturación</p>
                            </div>
                            <div class="p-6 bg-white dark:bg-zinc-800 border border-zinc-100 dark:border-zinc-700 rounded-3xl text-center">
                                <div class="text-indigo-600 font-bold text-lg mb-1 italic">Promedio</div>
                                <p class="text-[10px] text-zinc-500 uppercase tracking-tighter">Media Semanal</p>
                            </div>
                            <div class="p-6 bg-white dark:bg-zinc-800 border border-zinc-100 dark:border-zinc-700 rounded-3xl text-center">
                                <div class="text-indigo-600 font-bold text-lg mb-1 italic">Gráfico</div>
                                <p class="text-[10px] text-zinc-500 uppercase tracking-tighter">Últimos 7 días</p>
                            </div>
                        </div>

                        <div class="p-8 border border-zinc-200 dark:border-zinc-800 rounded-3xl bg-zinc-50 dark:bg-zinc-900/50 italic text-center text-sm text-zinc-500">
                            "Los datos de Analytics permiten a la gerencia anticipar tendencias de consumo y optimizar la rotación de inventario."
                        </div>
                    </section>
                </article>

            </div>
        </main>
    </div>
</body>
</html>