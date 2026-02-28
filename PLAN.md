## Leer OBLIGATORIAMENTE el archivo: "REQUIREMENTS.md"

# Plan de Desarrollo - Módulo de Usuarios y Dashboard Admin (Plex 25 / FarmaCorp)

**Stack Tecnológico:** Laravel 12, Livewire 4, PostgreSQL (Supabase).
**UI/UX:** Tailwind CSS + Flux UI (Exclusivamente versión Gratuita. Las tablas se maquetan con HTML nativo y clases de Tailwind).
**Metodología:** Desarrollo ágil por Sprints, priorizando optimización de consultas (Eager Loading) para mitigar latencia de red.

---

## Fase 1: Perfeccionamiento del CRUD de Usuarios
*Objetivo: Consolidar la gestión básica de empleados asegurando la trazabilidad operativa y el cumplimiento de seguridad.*

* [cite_start]**Búsqueda Avanzada y Filtros (RF-06)[cite: 63]:**
    * Implementar buscador en tiempo real (Live Search) por nombre/DNI utilizando `wire:model.live.debounce`.
    * Añadir filtros desplegables por Estado (Activo/Inactivo) y por Perfil (Rol).
* [cite_start]**Desactivación Lógica (RNF-04)[cite: 145]:**
    * Implementar un botón de "Eliminar" (icono de papelera) que dispare un modal de confirmación.
    * La acción no ejecutará un `DELETE` físico en la base de datos, sino un cambio de estado (`is_active = false`) para preservar el historial de facturación y caja.
* [cite_start]**Gestión de Claves (RF-03)[cite: 58]:**
    * Integrar campos de contraseña en el formulario de alta de usuarios.
    * Añadir funcionalidad para que el administrador pueda resetear o establecer nuevas contraseñas a los empleados desde el panel.

---

## Fase 2: Gestión Integral de Perfiles y Accesos (Spatie)
*Objetivo: Escalar la seguridad del sistema mediante roles dinámicos, eliminando la asignación estática de permisos.*

* [cite_start]**CRUD de Perfiles / Roles (RF-04)[cite: 59]:**
    * Desarrollar un componente independiente (`RoleManager`) para crear, editar y eliminar roles (ej. "Administrador", "Farmacéutico", "Cajero").
* [cite_start]**Asignación de Permisos a Perfiles (RF-05)[cite: 61, 62]:**
    * Integrar una matriz de *checkboxes* en los modales de creación/edición de Perfiles para asignar permisos masivos (ej. `abrir_caja`, `vender_psicotropicos`).
* **Modales Dinámicos de Edición en Tabla de Usuarios:**
    * Actualizar los modales `x-edit-role` y `x-edit-permission` para que sean formularios funcionales.
    * Al abrirse, deben mostrar **todos** los perfiles/permisos del sistema, dejando pre-seleccionados los que el usuario ya posee.
    * Implementar métodos `syncRoles` y `syncPermissions` para guardar los cambios.
* **Reactivación de Usuarios:**
    * Añadir un botón de acción rápida (icono de recarga/flecha) junto al botón de eliminar, visible solo en usuarios inactivos, para devolverles el estado `is_active = true`.

---

## Fase 3: Módulo de Gestión de Clientes
*Objetivo: Separar lógicamente a los clientes de los empleados, preparándolos para el módulo de facturación.*

* [cite_start]**CRUD de Clientes (RF-10, RF-07)[cite: 67, 114]:**
    * [cite_start]Crear el ABM (Alta, Baja, Modificación) para clientes, requiriendo: nombre, apellido, correo, teléfono y dirección[cite: 114].
    * [cite_start]Aplicar la misma lógica de "desactivación" (Soft Delete) utilizada en los usuarios[cite: 117].
* [cite_start]**Buscador Ágil (RF-08)[cite: 115]:**
    * Implementar búsqueda filtrada por nombre completo o teléfono para agilizar el proceso en el mostrador.

---

## Fase 4: Widgets del Dashboard Principal (Panel de Control)
*Objetivo: Transformar la pantalla de inicio del administrador en un centro de monitoreo proactivo cruzando datos con el Módulo de Medicamentos.*

* [cite_start]**Configurador de Alertas (RF-08)[cite: 65]:**
    * Desarrollar una tarjeta de configuración donde el admin defina el "período de anticipación" (en días) para el control de vencimientos.
* [cite_start]**Lista Automática de Próximos Vencimientos (RF-09)[cite: 66]:**
    * Generar una tabla de alerta rápida en el dashboard principal que cruce el valor configurado con el stock real, mostrando los medicamentos críticos que requieren atención inmediata.

# Plan de Desarrollo - Módulo de Productos y Medicamentos (Plex 25 / FarmaCorp)

**Contexto Arquitectónico:** Según el Modelo Relacional, la entidad `Medicine` extiende de la entidad base `Product`. Por lo tanto, este módulo abordará ambas entidades de forma conjunta para cumplir con los requerimientos 2.3.2 y 2.3.4.

**Stack Tecnológico:** Laravel 12, Livewire 4, PostgreSQL (Supabase).
**UI/UX:** Tailwind CSS + Flux UI (Exclusivamente versión Gratuita. Tablas maquetadas con HTML nativo).

---

## Fase 1: Catálogo Base (CRUD de Productos y Grupos)
*Objetivo: Establecer la base de datos de artículos de la farmacia, permitiendo su categorización y búsqueda, asegurando la integridad de los datos mediante desactivación lógica.*

* **Gestión de Grupos/Categorías (RF-06 Med):**
    * Crear un CRUD simple para "Grupos de Medicamentos" (ej. Analgésicos, Antibióticos, Venta Libre).
* **CRUD Central de Productos (RF-01 Prod):**
    * Implementar el ABM principal de `Productos` (Nombre, Descripción, Precio, Estado).
    * Extender el formulario para que, si el producto es un `Medicamento`, solicite los campos adicionales (Nivel, Prospecto, Grupo).
* **Desactivación Lógica de Productos (RF-02 Prod, RNF-04):**
    * Implementar el botón de "Desactivar" (Soft Delete) para evitar borrar productos que ya hayan sido facturados históricamente.
* **Buscador Universal de Catálogo (RF-03 Prod):**
    * Implementar un buscador en tiempo real (`wire:model.live`) en la tabla principal que permita encontrar productos por coincidencia parcial o completa del nombre.

---

## Fase 2: Información Especializada y Psicotrópicos
*Objetivo: Cumplir con los requerimientos legales y farmacológicos, facilitando a los empleados la consulta de información crítica en mostrador.*

* **Visor de Prospectos y Casos de Uso (RF-04 Med):**
    * Añadir un botón de acción (ej. icono de documento) en la tabla para los Medicamentos.
    * Al hacer clic, desplegar un `<flux:modal>` de solo lectura que muestre el prospecto detallado, nivel y casos de uso para que el empleado pueda asesorar al cliente.
* **Listado Exclusivo de Psicotrópicos (RF-03 Med):**
    * Crear una vista o filtro rápido (ej. un `<flux:select>` o un Tab) que aísle y muestre únicamente los medicamentos marcados como psicotrópicos, vital para los controles de auditoría y regulaciones.

---

## Fase 3: Integración de Stock Visual y Vencimientos
*Objetivo: Conectar el catálogo con el inventario físico y las alertas del sistema, sirviendo de puente hacia el Módulo de Stock (2.3.3).*

* **Padrón de Stock de Lectura (RF-01 y RF-02 Med):**
    * Modificar la tabla principal de Productos/Medicamentos para incluir columnas calculadas que muestren el stock actual disponible. *(Nota: La carga real de ingresos/egresos se hará en el Módulo de Stock, aquí solo visualizamos).*
* **Visor de Vencimientos (RF-05 Med):**
    * Permitir a los empleados hacer clic en un medicamento y ver la lista de lotes actuales con sus respectivas fechas de caducidad.
* **Conexión con el Dashboard Admin (RF-08 y RF-09 de Usuarios):**
    * Conectar la base de datos de medicamentos con el widget del "Dashboard de Administrador" creado en la fase anterior, para que la tabla de alertas lea directamente las fechas de vencimiento de este módulo y muestre los que están en zona de riesgo.