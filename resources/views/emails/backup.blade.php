<x-mail::message>
# FarmaCorp: Respaldo de Seguridad

Hola **{{ $userName }}**,

Se ha generado correctamente un punto de restauración del sistema. Adjunto a este correo encontrarás el archivo `.sql` con toda la información consolidada.

**Detalles del archivo:**
- **Fecha:** {{ now()->format('d/m/Y H:i') }}
- **Origen:** Sistema FarmaCorp

Te recomendamos almacenar este archivo en un lugar seguro.

Gracias,<br>
Soporte Técnico {{ config('app.name') }}
</x-mail::message>