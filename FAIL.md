# Reporte de Fallos de Testing

| Test Name | Función/Módulo | ¿Por qué falla? |
|-----------|----------------|-----------------|
| `it('correctly calculates unread messages for each conversation')` | `ChatWidget::conversations()` (Computed Property) | El motor de base de datos **SQLite** (usado en tests) tiene dificultades comparando precisiones de milisegundos entre `messages.created_at` y `conversation_user.last_read_at`. Esto causa que mensajes antiguos (creados antes del tiempo de lectura) sean contados erróneamente como "posteriores" (`>`), devolviendo un conteo de 2 en lugar de 1. |
