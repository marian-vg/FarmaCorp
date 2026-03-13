# Plan de Implementación: Refactorización de Backups y Eventos de Permisos

## Objetivo Principal
Mejorar la estabilidad, seguridad y dinamismo del sistema abordando tres aspectos clave:
1. Análisis y mejora del sistema de backups, incluyendo el bug de la pérdida de visualización tras una restauración (posible fallo de caché de permisos).
2. Seguridad granular para el gestor de copias de seguridad.
3. Actualización de permisos y roles en tiempo real utilizando Laravel Reverb (WebSockets).

---

### Fase A: Análisis y Mejora del Sistema de Backups

**Análisis del Problema (`flux:separator` oculto tras restaurar):**
El problema de que componentes condicionados por permisos dejen de visualizarse correctamente tras restaurar un backup radica en la **caché**. Spatie Permission almacena los permisos y roles cacheados. Al sobreescribir la base de datos con un backup antiguo, los IDs o las asignaciones de base de datos cambian, pero la caché de Spatie se mantiene intacta, causando que las directivas `@can` o `hasPermissionTo` fallen silenciosamente o devuelvan falso de forma inesperada.
**Solución:** Se añadirá una limpieza de la caché de permisos (`app()[PermissionRegistrar::class]->forgetCachedPermissions();`) y opcionalmente de otras cachés del sistema luego de restaurar el dump.

**Mejora del Motor de Backups:**
Actualmente, `BackupManager` genera el volcado SQL iterando sobre las tablas a mano con `DB::select` e iteraciones de PHP. Esto es ineficiente y problemático para bases de datos medianas/grandes.
Dado que la base de datos es **PostgreSQL** y que recientemente se instaló el paquete `spatie/db-dumper`, se refactorizará el método de creación de backup para que utilice la robustez y velocidad nativa de `pg_dump` proporcionada por el paquete de Spatie.

---

### Fase B: Permisos Granulares para el Gestor de Backups

**Acción:** 
Desglosar el permiso general de copias de seguridad para aplicar el principio de menor privilegio en el componente `BackupManager.php`.

1. **Modificar Seeder:** 
   Se agregarán los siguientes permisos al grupo **"Sistema"** en `RoleAndPermissionSeeder`:
   - `admin-backup.acceder` (Para entrar al módulo, ya existente conceptualmente)
   - `admin-backup.crear` (Para generar nuevas copias locales y a la nube/correo)
   - `admin-backup.restaurar` (Permiso crítico para sobreescribir la BD)
   - `admin-backup.eliminar` (Para borrar copias antiguas)

2. **Proteger el Componente `BackupManager.php`:**
   Al igual que en tareas previas, se aplicará el patrón de intercepción:
   ```php
   public function createInternalBackup() {
       if (!auth()->user()->can('admin-backup.crear')) {
           $this->notify('No tienes permisos para crear copias.', 'danger'); return;
       }
       // ...
   }
   ```
   Lo mismo aplicará para `restoreFromDisk` y `deleteBackup`.
3. En la interfaz (`backup-manager.blade.php`), se ocultarán o deshabilitarán los botones según el permiso.

---

### Fase C: Actualización de Interfaz en Tiempo Real (Reverb)

**Acción:** 
Crear un evento de broadcast en Laravel (ej. `UserPermissionsUpdated`) que se conecte con el frontend para reaccionar a los cambios de roles/permisos.

1. **Creación del Evento:** 
   Se generará un evento (ej. `app/Events/UserPermissionsUpdated.php`) que implemente `ShouldBroadcast` apuntando a un canal privado o público de usuario (ej. `channel('user.' . $userId)` o similar si el sidebar reacciona). Como los permisos afectan globalmente, podemos notificar al usuario específico cuyo permiso cambió.
2. **Despacho (Dispatch):** 
   En el método `savePermissions`, `saveRoles` y `updateUser` de `Admin\Dashboard`, cuando se sincronicen los permisos, se despachará el evento pasando el ID del usuario modificado.
3. **Escucha en Livewire (Frontend):** 
   El componente del sidebar (`layouts/app/sidebar.blade.php` si es livewire, o envolver el sidebar en un componente de Livewire si no lo está) escuchará este evento mediante los atributos `#[On('echo:user.{id},UserPermissionsUpdated')]`. 
   Dado que modificar permisos de acceso requiere recargar la UI fuertemente (para aplicar los nuevos directivos `@can`), cuando el frontend reciba el evento, puede emitir una notificación toast al usuario modificado y forzar un refresco de página `window.location.reload()`, o redirigirlo al dashboard si ha perdido permisos de la ruta actual.

---

### Fase D: Testing

1. Ejecutar y actualizar los tests correspondientes (`php artisan test`).
2. Comprobar que los rechazos del componente de Backup generen el aviso `danger` simulando a un usuario no autorizado.
3. Formatear los archivos modificados con Laravel Pint.

---
**Esperando confirmación...**
Escriba "Aprobado" o "Procede" para comenzar el desarrollo por fases e ir informando los resultados en `walkthrough.md`.