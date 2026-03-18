# Plan de Implementación: Refinamiento de Permisos, Prevención de Auto-eliminación y Refactorización de Rutas

## Objetivo
1. Impedir que el Super Administrador elimine (desactive) su propia cuenta y ocultar el botón en la UI.
2. Depurar y estabilizar la reactividad del componente `ErrorFallbackActions` (Pantalla 403) que no está reaccionando a los eventos de WebSockets como se esperaba.
3. Refactorizar el mapeo de rutas para que el usuario empleado sea redirigido directamente al módulo de "Cajas" en lugar del antiguo "User Dashboard".

---

## 1. Prevención de Auto-eliminación (Super Admin)

### Análisis
Actualmente, cualquier usuario con el permiso de `usuarios.desactivar` (como el Super Admin) puede hacer clic en "Desactivar" sobre su propia fila en la tabla de usuarios, lo que podría bloquearlo del sistema inmediatamente.

### Acción
*   **En la UI (`resources/views/livewire/admin/dashboard.blade.php`):**
    *   Modificar la condición que muestra el botón de "Desactivar". Si el `$user->id` iterado en la tabla es igual al `auth()->id()`, se omitirá el botón o se mostrará deshabilitado.
*   **En el Backend (`app/Livewire/Admin/Dashboard.php`):**
    *   En el método `deactivateUser(User $user)`, agregar una validación que aborte la acción si `$user->id === auth()->id()`, devolviendo una notificación de error ("No puedes desactivar tu propia cuenta").

---

## 2. Depuración del Fallback Reactivo (Vista 403)

### Análisis de la Falla Reportada
Si al otorgar permisos el botón en la vista 403 no se actualizó automáticamente, las posibles causas son:
1.  **Falta del script de Laravel Echo:** Al usar una vista de error genérica (403), es muy probable que no se esté importando `@vite(['resources/js/app.js'])` que contiene la inicialización de WebSockets (Reverb), a diferencia de `layouts/app.blade.php` donde sí existe.
2.  **Problemas de Livewire Assets:** La vista 403 puede no tener la directiva `@livewireScripts` o `@fluxScripts` inyectando correctamente los listeners.
3.  **Refresco de Permisos (Backend):** Si el componente recibe el evento pero no actualiza, puede que `auth()->user()->forgetCachedPermissions()` no esté aplicando a la sesión actual a tiempo.

### Acción
*   **Archivo:** `resources/views/errors/403.blade.php`.
*   **Corrección:** Añadir en el `<head>` la directiva de Vite: `@vite(['resources/css/app.css', 'resources/js/app.js'])` y asegurarse de que los scripts de Livewire se carguen correctamente.
*   **Verificación en Componente:** Revisar `app/Livewire/Actions/ErrorFallbackActions.php` para inyectar logs (`Log::info`) si fuera necesario, para confirmar si el evento WebSocket está entrando a nivel cliente.

---

## 3. Refactorización de la Ruta de Redirección (Dashboard de Usuario)

### Análisis
El sistema todavía asume que existe una ruta `user.dashboard` que servía como punto de partida para el empleado. Como ese dashboard ya no existe y el empleado debe ir directo a "Mi Caja Operativa", debemos actualizar todas las referencias que apunten a ese antiguo "dashboard".

### Acción
1.  **En `routes/web.php`:**
    *   Cambiar la lógica principal de la ruta genérica `/dashboard` para que la redirección del empleado apunte directamente a las funcionalidades de caja o punto de venta, eliminando el nombre/concepto de `user.dashboard`.
    *   Ejemplo de redirección para empleados: `return redirect()->route('admin.cajas');` (O a la ruta respectiva de la terminal operativa de cajas si difiere).
    *   Eliminar o renombrar cualquier definición antigua de `Route::get('user/dashboard', ...)->name('user.dashboard')`.
2.  **En el Componente 403 (`ErrorFallbackActions.php`):**
    *   Actualizar el mapeo de permisos para que la entrada de `caja.acceder` o el punto de venta apunten al nombre de ruta correcto actualizado.
3.  **En el Sidebar (`sidebar.blade.php`):**
    *   Actualizar el enlace del empleado para que no llame a `route('user.dashboard')`, sino a la nueva ruta directa operativa.

---

## 4. Pruebas y Validación (Testing)

1.  **Tests de Interfaz de Usuario:**
    *   Crear un test que compruebe que al montar el componente `Dashboard`, el botón de "Desactivar" falla silenciosamente o devuelve un error si el Admin intenta enviarse a sí mismo como objetivo (`deactivateUser`).
2.  **Verificación del 403:**
    *   A nivel local, forzar la vista 403, usar las herramientas de red del navegador y confirmar que la conexión `ws://` hacia Reverb está establecida dentro de esa pantalla.
3.  **Ejecución General:** `php artisan test` para asegurar que las redirecciones de las rutas base (`/dashboard`) funcionan tras eliminar `user.dashboard`.

---

**Esperando confirmación...**
Responde indicando tu aprobación para ejecutar el plan y realizar las correcciones.