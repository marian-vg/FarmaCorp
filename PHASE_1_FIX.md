# Resumen de Corrección: Nombre de Medicamento Ausente en Dashboard (PHASE_1_FIX.md)

## 1. Descripción del Error (El "Micro-Error")
En el panel de administración (`Dashboard`), la tabla de "Alertas de Vencimiento de Lotes" se renderizaba correctamente con todos los datos del lote (número de lote, cantidad remanente, fecha de expiración), pero la columna **"Medicamento"** se mostraba vacía o con el texto por defecto (`N/D`), en lugar de mostrar el nombre real del producto.

## 2. Análisis y Causa Raíz
A pesar de que la consulta original usaba *Eager Loading* (`->with('medicine.product')`), la propiedad `$batch->medicine` estaba devolviendo `NULL` en cada iteración del bucle. Al analizar la estructura de los modelos con la consola y realizar pruebas de aislamiento, se detectó el siguiente origen:

1. **Herencia y Claves Primarias Personalizadas**: El modelo `Medicine` hereda internamente de `Product`, pero redefine su clave primaria como `protected $primaryKey = 'product_id';`.
2. **Inferencia Automática de Eloquent (`BelongsTo`)**: Cuando el modelo `Batch` declara genéricamente `$this->belongsTo(Medicine::class);`, Eloquent autocompleta la _Foreign Key_ dinámicamente (`medicine_id`), pero puede confundirse con el_Owner Key_ (clave primaria destino) al tratarse de una clase heredada, buscando por defecto en la columna `id` en lugar del sobreescrito `product_id`.
3. **Falla Silenciosa**: Al buscar cruzando `batches.medicine_id` contra `medicines.id` (inexistente), la relación no hidrataba el objeto, arrojando `null` silenciosamente y forzando a la vista de Blade a caer en la regla Null-safe de PHP: `$medicine->product?->name ?? 'N/D'`.

## 3. Solución (Implementada)
La solución consistió en **romper la dependencia de inferencia automática de Eloquent** y explicitar fácticamente ambas llaves relacionales en la definición del modelo `Batch.php`.

**Código Original (Fallaba):**
```php
public function medicine(): BelongsTo
{
    return $this->belongsTo(Medicine::class);
}
```

**Código Corregido (Exitoso):**
```php
public function medicine(): BelongsTo
{
    // Se fuerza la lectura cruzando: (Llave foránea, Llave dueña)
    return $this->belongsTo(Medicine::class, 'medicine_id', 'product_id');
}
```

Con este ajuste vinculante, el *Eager Loading* resuelve perfectamente el anidamiento dinámico. El Dashboard es ahora capaz de acceder a `$batch->medicine->product->name` sin romper la cadena del ORM, logrando el 100% de la visibilidad en HTML.

## 4. Validación (Playwright / Pest)
Se ensayó exitosamente la visualización inyectando en base de datos Lotes reales inter-relacionados. Las pruebas transaccionales (como la de *StockIngresoTest*) afirmaron la lectura pos-inserción correctamente, y la renderización en el servidor responde ahora mostrando el Nombre Comercial de las medicinas.
