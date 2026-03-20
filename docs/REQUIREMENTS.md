# Documento de Requerimientos - Sistema FarmaCorp (Plex 25)

Este documento detalla los Requerimientos Funcionales (RF) y No Funcionales (RNF) definidos para el desarrollo del sistema de gestión farmacéutica.

---

## 2.3 Requerimientos Funcionales

### 2.3.1 Módulo de Usuarios
* **RF-01:** El sistema permite crear uno o más usuarios administradores.
* **RF-02:** El sistema permite al administrador dar de alta, baja y modificar usuarios.
* **RF-03:** El sistema permite al administrador crear claves a los usuarios.
* **RF-04:** El sistema permite al administrador crear perfiles.
* **RF-05:** El sistema permite al administrador modificar permisos de los usuarios/perfiles.
* **RF-06:** El sistema permite al administrador realizar una búsqueda avanzada por filtros: usuarios inactivos, perfiles, total de la información del usuario.
* **RF-07:** El sistema permite el inicio de sesión mediante la credencial.
* **RF-08:** El sistema permite al administrador configurar un período de anticipación (en días) para el control de vencimiento de medicamentos.
* **RF-09:** El sistema genera automáticamente una lista de medicamentos que vencen dentro del período configurado y la pone a disposición del usuario para su revisión.
* **RF-10:** El sistema debe permitir gestionar la información de clientes (alta, baja, modificación y búsqueda).

### 2.3.2 Módulo de Medicamentos
* **RF-01:** El sistema permite cargar el Stock de los medicamentos.
* **RF-02:** El sistema permite consultar el padrón de Stock.
* **RF-03:** El sistema permite consultar la lista de Psicotrópicos.
* **RF-04:** El sistema permite consultar la información de un medicamento (prospecto, casos de uso).
* **RF-05:** El usuario podrá consultar el vencimiento de los medicamentos.
* **RF-06:** El usuario podrá crear grupos de medicamentos.

### 2.3.3 Módulo de Stock
* **RF-01:** El sistema permite al usuario administrador agregar cantidades, unidades y mínimos a los productos.
* **RF-02:** El sistema permite registrar ingresos de stock (compras a proveedores).
* **RF-03:** El sistema permite registrar egresos de stock (ventas, devoluciones, mermas, robos, destrucción).
* **RF-04:** El sistema permite consultar el historial de movimientos por medicamento.
* **RF-05:** El sistema genera alertas cuando un medicamento baja del stock mínimo o está próximo a vencer.
* **RF-06:** El sistema permite registrar el número y la fecha de vencimiento de cada lote de medicamentos.
* **RF-07:** El sistema permite bloquear la venta de medicamentos vencidos.

### 2.3.4 Módulo de Productos
* **RF-01:** El sistema permite registrar y modificar productos con información básica.
* **RF-02:** El sistema permite desactivar productos.
* **RF-03:** El sistema permite al usuario buscar productos por nombre parcial o completo.

### 2.3.5 Módulo de Caja
* **RF-01:** El sistema permite al usuario abrir una caja.
* **RF-02:** El sistema permite administrar las cobranzas.
* **RF-03:** El sistema permite modificar el cobro del comprobante.
* **RF-04:** El sistema permite filtrar la búsqueda de productos por fecha y/o por tipo de comprobantes y/o por turnos específicos.
* **RF-05:** El sistema permite al usuario agregar múltiples medios de pago al monto final de un producto.
* **RF-06:** El sistema permite al usuario autorizado generar un retiro de caja.
* **RF-07:** El sistema permite al usuario generar reportes luego del cierre de caja.
* **RF-08:** El sistema permite realizar ingresos y egresos en la caja.
* **RF-09:** El sistema permite al usuario especificar los datos sobre el movimiento, fecha, monto y medio de pago sobre los ingresos y egresos de caja.
* **RF-10:** El sistema permite gestionar Vales al usuario en caso de devolución de producto (emitir, registrar, cancelar).
* **RF-11:** El sistema permite ver un listado de detalles de Egresos por caja en un tiempo definido.
* **RF-12:** El sistema permite ver un listado de Ingresos por Medios de Pago por caja en un tiempo definido.

### 2.3.6 Módulo de Facturación
* **RF-01:** El sistema permite al usuario realizar diferentes tipos de dispensas de productos.
* **RF-02:** El sistema permite la modificación y eliminación de productos.
* **RF-03:** El sistema permite aplicar un recargo/descuento general a los productos en la venta.
* **RF-04:** El sistema exige seleccionar un tipo de comprobante antes de cargar productos.
* **RF-05:** El sistema permite seleccionar múltiples medios de pago en la venta (efectivo, débito, crédito, vales, etc.).
* **RF-06:** El sistema permite registrar pagos parciales con múltiples medios de pago hasta completar el monto total.
* **RF-07:** El sistema permite registrar a los clientes, incluyendo su nombre, apellido, correo, teléfono y dirección.
* **RF-08:** El sistema permite buscar clientes por nombre o teléfono.
* **RF-09:** El sistema permite modificar los datos básicos de un cliente registrado.
* **RF-10:** El sistema permite desactivar clientes en lugar de eliminarlos.
* **RF-11:** El sistema permite asociar una venta a un cliente, marcando si esta es “pagada” o a “cuenta corriente”.
* **RF-12:** Si fue a “cuenta corriente” el sistema registra el saldo pendiente para el cliente.
* **RF-13:** El sistema permite emitir una factura básica, vinculando productos y cliente.
* **RF-14:** El sistema descuenta automáticamente el stock del producto una vez realizada la venta.
* **RF-15:** El sistema permite al usuario consultar el prospecto de los medicamentos.
* **RF-16:** El sistema permite al usuario consultar el saldo de Cuentas Corrientes de los clientes.
* **RF-17:** El sistema genera una alerta al intentar facturar un producto cuyo precio no fue actualizado, restringiendo la venta y bloqueando su inclusión en el comprobante.
* **RF-18:** El sistema puede restringir la venta de productos con precios vencidos, bloqueando su inclusión en el comprobante.
* **RF-19:** El sistema permite imprimir copias de las facturas electrónicas.
* **RF-20:** El sistema permite configurar opciones al cerrar un comprobante: imprimir y/o guardar.
* **RF-21:** El sistema permite configurar la cantidad de días de antigüedad de los precios de los productos.
* **RF-22:** El sistema debe permitir registrar los datos del cliente al generar una factura. 
* **RF-23:** El sistema debe permitir asociar una factura existente a un cliente registrado. 
* **RF-24:** El sistema debe permitir consultar el historial de compras por cliente.
* **RF-25:** El sistema debe permitir filtrar facturas por cliente, fecha o tipo de comprobante.

---

## 2.4 Requerimientos No Funcionales

### 2.4.1 Usabilidad
* **RNF-01:** La interfaz del sistema debe ser intuitiva, con formularios y listados claros para minimizar la curva de aprendizaje del usuario.
* **RNF-02:** El sistema debe permitir que un usuario nuevo pueda realizar operaciones básicas (facturación, búsqueda de productos, control de stock) en menos de 30 minutos de capacitación.
* **RNF-03:** El sistema debe usar mensajes claros para corregir errores y confirmaciones, evitando tecnicismos y asegurando que el usuario pueda entender la causa y solución del problema.
* **RNF-04:** Todas las funcionalidades deben ser accesibles en máximo 3 clics desde el menú principal.

### 2.4.2 Seguridad
* **RNF-01:** El sistema debe requerir credenciales únicas para el inicio de sesión, con contraseñas encriptadas.
* **RNF-02:** La sesión de usuario debe expirar automáticamente tras 15 minutos de inactividad.
* **RNF-03:** Los permisos deben estar controlados mediante roles y perfiles, asegurando que los usuarios solo accedan a las funcionalidades autorizadas.
* **RNF-04:** El sistema debe impedir la eliminación física de datos críticos como usuarios, clientes o productos, usando desactivación lógica para mantener trazabilidad.

### 2.4.3 Mantenibilidad y Escalabilidad
* **RNF-01:** El sistema debe estar desarrollado siguiendo el patrón MVC, separando la lógica de negocio, la presentación y el acceso a datos.
* **RNF-02:** La base de datos debe diseñarse respetando 3FN (Tercera Forma Normal) para evitar redundancias y facilitar futuras modificaciones.
* **RNF-03:** El sistema debe permitir agregar nuevos módulos en el futuro sin afectar el funcionamiento de los módulos actuales.

### 2.4.4 Tecnología
* **RNF-01:** El backend debe desarrollarse en PHP (Laravel) y la base de datos en PostgreSQL como motor y por encima se utilizará Supabase para su gestión.
* **RNF-02:** El frontend debe seguir un diseño responsive para su uso en PC y tablets.

### 2.4.5 Restricciones Operativas
* **RNF-01:** El sistema solo debe operar en una única sucursal y en una red local sin requerir acceso externo.
* **RNF-02:** El sistema debe ser monousuario para caja (una caja abierta por turno), pero multiusuario para gestión administrativa.