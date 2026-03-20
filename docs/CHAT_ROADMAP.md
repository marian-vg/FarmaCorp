# Roadmap de Implementación: Módulo de Comunicación en Tiempo Real (FarmaCorp)

**Descripción:** Este documento detalla la arquitectura y las fases de desarrollo para el módulo de chat interno de FarmaCorp, utilizando Laravel 12, Livewire 4, Alpine.js, Flux UI y Laravel Reverb (WebSockets).

**Metodología Estricta (Instrucción para el Agente):**
El desarrollo es estrictamente iterativo. **PROHIBIDO** avanzar a la siguiente fase si la fase actual no ha sido implementada, testeada (Pest) y aprobada explícitamente por el usuario. Si ocurre un error, el *rollback* y la depuración se limitan al alcance de la fase en curso.

---

## Fase 1: Cimientos Relacionales (Estructura de Datos)
**Objetivo:** Crear el esquema de base de datos para soportar mensajes uno a uno y futuros chats grupales. Sin interfaz ni tiempo real.

- [ ] **Migraciones y Modelos:**
  - `Conversation`: `id`, `is_group` (boolean), `name` (nullable), timestamps.
  - `conversation_user` (Pivot): `conversation_id`, `user_id`, `last_read_at` (timestamp).
  - `Message`: `id`, `conversation_id`, `sender_id`, `body` (text), timestamps.
- [ ] **Relaciones Eloquent:** Configurar relaciones `belongsToMany` (usuarios <-> conversaciones) y `hasMany` (conversaciones -> mensajes).
- [ ] **Factories:** Crear fábricas para `Conversation` y `Message` para poblar la base de datos de prueba.
- [ ] **Criterios de Aceptación (Tests Pest):**
  - Test: Crear una conversación entre Usuario A y Usuario B.
  - Test: Validar que Usuario C no tiene acceso a esa conversación.
  - Test: Guardar un mensaje y verificar su asociación correcta.

---

## Fase 2: El Motor Síncrono (Lógica Livewire Base)
**Objetivo:** Implementar la lógica de negocio para enviar y recibir mensajes de forma tradicional (síncrona).

- [ ] **Componente Livewire (`ChatWidget`):**
  - Método `loadConversations()`: Obtener chats del usuario autenticado.
  - Método `loadMessages(Conversation $conversation)`: Obtener historial paginado (últimos 20 mensajes).
  - Método `sendMessage(Conversation $conversation, $body)`: Validar y guardar en BD.
- [ ] **Criterios de Aceptación (Tests Pest):**
  - Test Livewire: Simular envío de mensaje (`set('body', 'Test')->call('sendMessage')`) y verificar que se guarda en la base de datos y se limpia el input.

---

## Fase 3: El Latido del Sistema (Reverb y Eventos)
**Objetivo:** Configurar WebSockets para transmisión asíncrona segura.

- [ ] **Laravel Reverb:** Instalación y configuración del servidor de WebSockets.
- [ ] **Eventos:** Crear evento `MessageSent` implementando `ShouldBroadcast`.
- [ ] **Seguridad (Canales):** Configurar `routes/channels.php` usando `Broadcast::channel('chat.{conversationId}')` para autorizar solo a los participantes.
- [ ] **Integración:** Actualizar `sendMessage` (Fase 2) para disparar `MessageSent` tras guardar en BD.
- [ ] **Criterios de Aceptación (Tests Pest):**
  - Test: Verificar que `MessageSent` es despachado (`Event::assertDispatched`).
  - Test: Verificar que un usuario no participante recibe `403 Forbidden` al intentar autorizarse en el canal privado.

---

## Fase 4: La Interfaz Reactiva (Flux UI y Optimistic UI)
**Objetivo:** Construir la ventana flotante y conectar la escucha de eventos en el frontend.

- [ ] **UI Base (Flux UI):** Crear el contenedor fijo (`bottom-right`) usando componentes de Flux.
- [ ] **Estado Local (Alpine.js):** Usar `x-data="{ open: false }"` para abrir/cerrar el chat sin peticiones al servidor.
- [ ] **Escucha Reactiva (Livewire):** Utilizar atributos `#[On('echo-private:chat.{conversation_id},MessageSent')]` para atrapar mensajes entrantes y actualizar la colección local.
- [ ] **Optimistic UI:** Dibujar el mensaje instantáneamente en pantalla vía Alpine.js al presionar "Enviar", antes de la confirmación del servidor.
- [ ] **Criterios de Aceptación:**
  - Prueba Manual: Transmisión instantánea entre dos navegadores autenticados con distintos usuarios sin recargar la página.

---

## Fase 5: El Toque Corporativo (Seguridad y Presencia)
**Objetivo:** Añadir características empresariales de seguridad y UX.

- [ ] **Canales de Presencia:** Migrar/Añadir `PresenceChannel` para rastrear quién está online en FarmaCorp.
- [ ] **Indicadores UI:** Mostrar punto verde (Online) en el avatar de los usuarios conectados.
- [ ] **Confirmación de Lectura:** Actualizar `last_read_at` en la tabla pivote al maximizar el chat. Mostrar indicador de mensajes no leídos.
- [ ] **Cifrado (Opcional/Recomendado):** Implementar `$casts = ['body' => 'encrypted']` en el modelo `Message` para cifrado en reposo.
- [ ] **Criterios de Aceptación:**
  - Prueba Manual: El indicador "Online" debe aparecer/desaparecer en tiempo real al abrir/cerrar sesiones en otros navegadores.