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
**Próxima Fase:** Fase 2: El Motor Síncrono (Lógica Livewire Base)
