# Plan de Implementación: Separación de Permisos de Caja (Admin vs Empleado)

## Objetivo Principal
Diferenciar el acceso a la "Caja Operativa" (terminal del empleado) del acceso a la "Administración de Cajas" (vista del administrador). Actualmente, ambas vistas dependen del mismo permiso (`caja.acceder`), lo que permite que usuarios comunes vean apartados administrativos. Se separarán los permisos y se agruparán estratégicamente para cumplir con los requerimientos de seguridad.

---

### Fase A: Modificación de Permisos (Seeder)
**Acción:** 
Actualizar el archivo `database/seeders/RoleAndPermissionSeeder.php` para desvincular la administración de cajas del permiso general de caja.

1. **Mantener** el permiso `caja.acceder` (y derivados como abrir, cerrar, ingresos_egresos) dentro del grupo **"Caja"**. Este será exclusivo para la operación diaria (Mi Caja Operativa) de los empleados.
2. **Crear** un nuevo permiso llamado `admin-cajas.acceder` (display name: "Administrar Cajas e Historial") y colocarlo dentro del grupo **"Sistema"**. De esta forma, este permiso quedará agrupado junto con `admin-panel.acceder` para facilitar su asignación a perfiles gerenciales (Administradores o Low-Admins).

---

### Fase B: Actualización de Rutas (`routes/web.php`)
**Acción:** 
Cambiar el middleware de protección para la ruta administrativa de cajas.

- **Antes:**
  ```php
  Route::get('admin/cajas', CajaManager::class)->name('admin.cajas')->middleware('permission:caja.acceder');
  ```
- **Después:**
  ```php
  Route::get('admin/cajas', CajaManager::class)->name('admin.cajas')->middleware('permission:admin-cajas.acceder');
  ```
- La ruta `user/dashboard` (Mi Caja Operativa) conservará su middleware `permission:caja.acceder`.

---

### Fase C: Modificación de la Interfaz (Sidebar)
**Acción:** 
Actualizar el componente de navegación `resources/views/components/layouts/app/sidebar.blade.php` para que renderice los enlaces dependiendo de su respectivo permiso, respetando la estructura visual de Flux UI.

- **Implementación en Blade:**
  ```blade
  @can('caja.acceder')
      <flux:sidebar.item icon="wallet" href="{{ route('user.dashboard') }}" :current="request()->routeIs('user.dashboard')">Mi Caja Operativa</flux:sidebar.item>
  @endcan

  @can('admin-cajas.acceder')
      <flux:sidebar.item icon="archive-box" href="{{ route('admin.cajas') }}" :current="request()->routeIs('admin.cajas')">Administración de Cajas</flux:sidebar.item>
  @endcan
  ```

---

### Fase D: Actualización de Tests y Componentes Relacionados
**Acción:** 
Garantizar que el sistema de testing automático siga funcionando.

1. Revisar `tests/Feature/Livewire/Admin/CajaManagerTest.php` (y cualquier otro relacionado a `admin/cajas`) para asegurar que al usuario de prueba (Admin) se le asigne explícitamente el nuevo permiso `admin-cajas.acceder` antes de montar el componente.
2. Ejecutar Laravel Pint para mantener la convención de estilo.
3. Correr la suite de Pest/PHPUnit completa para validar la ausencia de regresiones (`php artisan test`).

---
**Esperando confirmación...**
Escriba "Aprobado" o "Procede" para que ejecute este plan paso a paso y registre los resultados en `walkthrough.md`.