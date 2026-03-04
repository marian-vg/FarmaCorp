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


# Plan de Desarrollo - Módulo de Stock (Plex 25 / FarmaCorp)

**Contexto Arquitectónico:** El control de inventario en una farmacia no se maneja por producto global, sino por **Lotes** (RF-06). Cada lote tiene su propia cantidad y fecha de vencimiento. Además, todo cambio en la cantidad debe dejar una huella de auditoría mediante **Movimientos de Stock** (RF-03, RF-04).

**Stack Tecnológico:** Laravel 12, Livewire 4, PostgreSQL (Supabase).
**UI/UX:** Tailwind CSS + Flux UI (Versión Gratuita. Tablas maquetadas con HTML nativo).

---

## Fase 0: Arquitectura de Lotes, Movimientos y Corrección del Dashboard
*Objetivo: Sentar las bases de datos transaccionales y corregir el bug lógico del Dashboard del Administrador (falsos positivos de stock cero).*

* **Migraciones de Lotes y Movimientos:**
    * Crear tabla `lotes`: `id`, `medicamento_id`, `numero_lote`, `fecha_vencimiento`, `cantidad_inicial`, `cantidad_actual`, `stock_minimo`.
    * Crear tabla `movimientos_stock`: `id`, `lote_id`, `usuario_id`, `tipo` (ingreso, egreso), `motivo` (compra, venta, merma, vencimiento), `cantidad`.
* **Refactorización del Dashboard (Corrección del Bug):**
    * Modificar la consulta del widget de vencimientos (desarrollado en el Módulo de Usuarios/Medicamentos).
    * La consulta debe leer ahora de la tabla `lotes`, aplicando la condición: `where('cantidad_actual', '>', 0)` y calculando la proximidad de la `fecha_vencimiento`.

---

## Fase 1: Ingresos de Stock y Gestión de Lotes (RF-01, RF-02, RF-06)
*Objetivo: Permitir al administrador registrar las compras a proveedores y dar de alta los lotes físicos en el sistema.*

* **Componente de Ingreso de Mercadería (`StockIngresoManager`):**
    * UI con formulario (`<flux:modal>` o vista dedicada) para registrar una entrada.
    * Seleccionar Medicamento (usando buscador Scout).
    * Ingresar los datos del Lote: Número de lote, Fecha de vencimiento (RF-06), Cantidad recibida (RF-01), y Stock mínimo deseado.
* **Lógica Transaccional (Livewire):**
    * Al guardar, el sistema debe crear/actualizar el registro en `lotes` y registrar automáticamente un `movimiento_stock` de tipo "ingreso" por "compra a proveedor" (RF-02).

---

## Fase 2: Egresos Manuales y Ajustes (RF-03, RNF-04)
*Objetivo: Permitir la salida de stock por razones operativas ajenas a la venta en caja (mermas, robos, destrucción por vencimiento).*

* **Componente de Ajuste de Inventario (`StockEgresoManager`):**
    * Tabla HTML/Tailwind que liste el stock actual (solo lotes con `cantidad_actual > 0`).
    * Botón de acción (ej. icono de ajuste) que abra un `<flux:modal>`.
    * El formulario debe solicitar la cantidad a retirar y obligar a seleccionar un **motivo** (devolución, merma, robo, destrucción).
* **Protección Transaccional:**
    * Validar que la cantidad a retirar no supere la `cantidad_actual` del lote.
    * Registrar el `movimiento_stock` de tipo "egreso" y descontar la cantidad del lote.

---

## Fase 3: Trazabilidad, Alertas y Bloqueos (RF-04, RF-05, RF-07)
*Objetivo: Proveer herramientas de auditoría (Kardex) y asegurar el cumplimiento de las normativas de salud restringiendo la venta de productos caducados.*

* **Historial de Movimientos / Kardex (RF-04):**
    * Vista de solo lectura con una tabla detallada que muestre el historial cronológico de un medicamento (quién movió qué lote, cuándo, por qué motivo y cuánta cantidad).
* **Alertas de Stock Mínimo (RF-05):**
    * Añadir un nuevo widget al Dashboard del Administrador o una pestaña en la vista de Stock que liste los lotes cuya `cantidad_actual` sea menor o igual a su `stock_minimo`.
* **Bloqueo Lógico de Vencidos (RF-07):**
    * Implementar un método global o un *Scope* en el modelo `Lote` que oculte o marque como "no vendibles" aquellos lotes cuya `fecha_vencimiento` sea menor a la fecha actual, preparándolo para que el Módulo de Facturación (que hará tu compañero) no pueda seleccionarlos.