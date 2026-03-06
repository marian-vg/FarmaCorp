# Backlog de Refinamiento Arquitectónico y UX (Plex 25)

## Seguridad y Arquitectura
- [ ] **Inmutabilidad de Permisos Críticos:** Refactorizar la gestión de permisos en el panel de administración (Spatie). Restringir la edición del identificador interno (`name`) en la interfaz para evitar la ruptura de directivas `@can()` y validaciones en el código fuente. La interfaz solo debe permitir la modificación del alias visual (`display_name`) y la descripción.

## Rendimiento y Optimización
- [x] **Estrategia de Caché Global:** Implementar `Cache::remember` en consultas de datos estáticos o de baja frecuencia de actualización (ej. listados de grupos de medicamentos, roles del sistema) para reducir los tiempos de respuesta del servidor y aliviar la carga sobre PostgreSQL.
- [x] **Resolución de N+1 (Módulo Empleado):** Auditar y corregir las excepciones de carga diferida (`LazyLoadingViolationException`) en las vistas asociadas a las rutas "Vender (POS)" y "Mi Caja Operativa". Aplicar Eager Loading (`->with()`) en los controladores Livewire correspondientes.

## Experiencia de Usuario (UX) e Interfaz (UI)
- [ ] **Estados Vacíos (Empty States) en POS y Caja:** Diseñar e implementar retroalimentación visual amigable en el Módulo de Ventas. El sistema debe bloquear la interfaz de cobro y mostrar mensajes claros mediante componentes `<flux:empty>` o tarjetas informativas cuando "La caja actual se encuentre cerrada" o "El carrito de compras esté vacío".
- [ ] **Estandarización de Feedback (Toasts):** Auditar y corregir la emisión de notificaciones tras operaciones CRUD (guardado, actualización, eliminación). Configurar correctamente los eventos de Livewire para disparar `Flux::toast()` garantizando que el usuario reciba confirmaciones visuales de éxito o error de forma consistente.

## Corrección de Bugs y Reactividad (Livewire)
- [ ] **Sincronización de Estado al Cerrar Caja (Empleado):** Solucionar el bug de reactividad en "Mi Caja Operativa". Al confirmar el cierre de caja, el componente Livewire debe actualizar correctamente su estado interno y re-renderizar la vista de forma inmediata, ocultando los botones transaccionales (ingresos, egresos, cerrar turno) y habilitando únicamente el botón de apertura.
- [ ] **Prevención de Modales Duplicados en Auditoría (Admin):** Corregir la persistencia errónea del modal de "Cierre de Turno / Auditoría Final" en el panel del Administrador. Asegurar que tras confirmar el cierre, la variable de estado que controla la visibilidad del componente `<flux:modal>` se resetee adecuadamente, evitando que la ventana emergente aparezca por segunda vez de forma ociosa.