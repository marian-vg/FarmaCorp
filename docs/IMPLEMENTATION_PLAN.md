# Plan de Implementación: Refactorización y Ampliación de la Base de Datos

## Objetivo
Fortalecer la base de datos del sistema para demostraciones y pruebas, ampliando el catálogo de medicamentos (Vademécum), definiendo perfiles operativos realistas, incorporando obras sociales nacionales y trazando un historial de stock inicial.

---

## 1. Ampliación del Vademécum
**Archivo:** `database/data/vademecum_mock.json`

**Acción:** Agregar mínimo 20 medicamentos adicionales con presencia real en el mercado argentino.
- **Ejemplos a incluir:** Atorvastatina, Enalapril, Pantoprazol, Levotiroxina, Alprazolam, Diclofenac, Dipirona, etc.
- **Detalles:** Cada entrada contará con descripción, grupo farmacológico, presentación (nombre, precio, nivel) e indicación de si es psicotrópico o requiere receta.

---

## 2. Creación de Nuevos Seeders

### A. ProfileSeeder
**Propósito:** Crear perfiles de acceso (model `Profile`) con permisos granulares pre-asignados para simular roles operativos reales.
- **Perfiles a crear (5-6):**
    1.  **Caja Turno Mañana:** Permisos de `caja.acceder`, `caja.abrir`, `caja.ingresos_egresos`.
    2.  **Caja Turno Tarde:** Mismos que el anterior, permitiendo diferenciar responsables.
    3.  **Administrador de Obras Sociales:** Permisos de `obrasocial.acceder` y `obrasocial.crear_editar`.
    4.  **Gestor de Inventario y Stock:** Permisos de `inventario.acceder`, `stock.acceder`, `stock.ingreso`, `stock.egreso`.
    5.  **Auditor de Ventas y Recetas:** Permisos de `admin-ventas.acceder`, `recetas.acceder`, `recetas.crear_editar`.
    6.  **Atención al Cliente:** Permisos de `clientes.acceder` y `clientes.crear_editar`.

### B. ObraSocialSeeder
**Propósito:** Poblar el sistema con las 15 obras sociales y prepagas más reconocidas de Argentina.
- **Lista tentativa:** PAMI, OSDE, OSECAC, Swiss Medical, Galeno, IOMA, Unión Personal, Medicus, Omint, OSDEPYM, etc.
- **Acción:** Vincular aleatoriamente algunos medicamentos del Vademécum a estas obras sociales con descuentos variables (40%, 50%, 100%).

### C. StockSeeder
**Propósito:** Generar un historial de movimientos auditable y stock disponible inicial.
- **Acción:**
    1.  Para una selección de medicamentos, crear registros en la tabla `batches`.
    2.  Crear movimientos de `StockMovement` tipo "ingreso" para inicializar las cantidades.
    3.  Crear movimientos de `StockMovement` tipo "egreso" (ajustes o ventas simuladas) para demostrar la trazabilidad en el Kardex.

---

## 3. Integración en DatabaseSeeder
**Archivo:** `database/seeders/DatabaseSeeder.php`

**Acción:** 
- Incorporar las llamadas a `ProfileSeeder`, `ObraSocialSeeder` y `StockSeeder` respetando las dependencias (el stock requiere que los medicamentos existan primero).
- Asegurar que el comando `php artisan migrate:fresh --seed` sea la vía definitiva para reconstruir este entorno robusto.

---

## 4. Verificación
- **Ejecución:** Correr el seed completo y verificar visualmente en la interfaz:
    - Tabla de productos poblada.
    - Obras sociales en su catálogo.
    - Historial de stock (Kardex) con datos de ejemplo.
    - Perfiles disponibles en la sección de administración de usuarios.
- **Limpieza:** Ejecutar `vendor/bin/pint` sobre los nuevos archivos.

---
**Esperando confirmación...**
Responde indicando tu aprobación para comenzar con la refactorización de la base de datos.