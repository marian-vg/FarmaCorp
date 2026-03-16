# Registro de Tareas - Módulo de Chat Interno (FarmaCorp)

## Fase 1: Cimientos Relacionales (Completada)
- [x] Crear migraciones para `conversations`, `conversation_user` y `messages`.
- [x] Definir esquemas de base de datos con llaves foráneas y borrado en cascada.
- [x] Crear modelo `Conversation` con relaciones Eloquent.
- [x] Crear modelo `Message` con relaciones Eloquent.
- [x] Actualizar modelo `User` con relaciones hacia conversaciones y mensajes.
- [x] Crear `ConversationFactory`.
- [x] Crear `MessageFactory`.
- [x] Implementar tests en `ChatDatabaseTest.php`.
- [x] Crear modelo pivot `ConversationUser` para manejo de casts en atributos pivot.
- [x] Ejecutar migraciones y validar tests (Pest).

---

## Fase 2: El Motor Síncrono - Lógica Livewire Base (Completada)
- [x] Crear el componente Livewire `ChatWidget`.
- [x] Implementar propiedad computada `conversations` con Eager Loading (evitar N+1).
- [x] Implementar método `selectConversation` para cargar mensajes.
- [x] Implementar método `sendMessage` con validación de seguridad (pertenencia al chat).
- [x] Crear esqueleto básico de la vista Blade (placeholders).
- [x] Implementar tests de integración con Pest y Livewire (`ChatWidgetTest.php`).
- [x] Validar funcionamiento síncrono y persistencia en BD.

---

## Fase 3: El Latido del Sistema - Reverb y Eventos (Completada)
- [x] Configurar Laravel Reverb (`php artisan reverb:install`).
- [x] Crear evento `MessageSent` con `ShouldBroadcastNow`.
- [x] Optimizar payload del evento (`broadcastWith`).
- [x] Configurar canal privado `chat.{conversationId}` en `routes/channels.php`.
- [x] Implementar lógica de autorización estricta en el canal.
- [x] Refactorizar `sendMessage` para despachar el evento.
- [x] Implementar tests de broadcasting y autorización (`ChatBroadcastingTest.php`).

---
**Próxima Fase:** Fase 4: La Interfaz Reactiva (Flux UI y Optimistic UI)
