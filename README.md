<p align="center">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="FarmaCorp Logo">
</p>

<h1 align="center">Plex 25 (FarmaCorp)</h1>

<p align="center">
    <strong>Sistema Integral de Gestión Farmacéutica Mono-Sucursal</strong><br>
    Arquitectura robusta, trazabilidad de inventario y control de caja en tiempo real.
</p>

<p align="center">
    <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP Version">
    <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel Version">
    <img src="https://img.shields.io/badge/Livewire-4.x-FB70A9?style=flat-square&logo=livewire&logoColor=white" alt="Livewire Version">
    <img src="https://img.shields.io/badge/PostgreSQL-Local-336791?style=flat-square&logo=postgresql&logoColor=white" alt="PostgreSQL">
    <img src="https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=flat-square&logo=tailwind-css&logoColor=white" alt="Tailwind CSS">
</p>

---

## Descripción General

**Plex 25** (comercialmente operando como FarmaCorp) es una plataforma monolítica de alto rendimiento diseñada exclusivamente para resolver las complejidades operativas y normativas de una farmacia de sucursal única. 

El sistema abandona las soluciones genéricas de inventario para adoptar un enfoque estrictamente farmacéutico: gestión transaccional por **lotes físicos**, control estricto de fechas de vencimiento, auditoría de psicotrópicos y un ecosistema de caja fluido preparado para múltiples medios de pago. Todo esto envuelto en una interfaz de usuario reactiva, rápida y de alta usabilidad, diseñada para minimizar la fricción en el mostrador.

## Características Principales

El proyecto se divide en módulos altamente cohesivos para garantizar la escalabilidad y el cumplimiento de los Requerimientos Funcionales (RF):

* **🛡️ Autenticación y Autorización (Spatie):** Control de acceso granular (RBAC) inmutable a nivel de código, con paneles de gestión dinámicos para administradores.
* **📦 Catálogo y Especificación Farmacológica:** Diferenciación arquitectónica entre artículos comerciales genéricos y medicamentos con rigor médico (prospectos, niveles, psicotrópicos).
* **📊 Gestión de Stock Transaccional (Kardex):** El inventario se gestiona mediante Lotes. Todo ingreso o egreso ejecuta transacciones seguras en la base de datos (`DB::transaction`) generando un historial de auditoría inmutable.
* **🚨 Motor de Alertas Preventivas:** Bloqueo lógico automático (Global Scopes) para impedir la facturación de mercadería vencida, junto con alertas tempranas de quiebre de stock en el dashboard gerencial.
* **⚡ Búsqueda en Tiempo Real:** Integración nativa con **Laravel Scout** (Driver PostgreSQL) para búsquedas instantáneas de pacientes, medicamentos y lotes sin comprometer el rendimiento.

## Stack Tecnológico

FarmaCorp está construido sobre el ecosistema **TALL** moderno, priorizando el renderizado del lado del servidor (SSR) reactivo para evitar la complejidad de las APIs SPA, manteniendo una UI de nivel empresarial.

- **Backend:** PHP 8.2+, Laravel 12.
- **Frontend Reactivo:** Livewire 4, Alpine.js.
- **UI & Estilos:** Tailwind CSS, Flux UI (Componentes nativos accesibles).
- **Base de Datos:** PostgreSQL (Instalación local Bare-metal, optimizada con índices relacionales).
- **Testing (E2E & Unit):** Pest PHP, Playwright CLI.

## Requisitos Previos

Asegúrese de contar con el siguiente software instalado en su entorno local antes de proceder con la instalación:

- [PHP 8.2](https://www.php.net/downloads.php) o superior.
- [Composer](https://getcomposer.org/).
- [Node.js](https://nodejs.org/en/) y NPM.
- [PostgreSQL](https://www.postgresql.org/download/) (Servidor local en ejecución).

## Instalación y Configuración

Siga estos pasos para desplegar el entorno de desarrollo local:

1. **Clonar el repositorio:**
   ```bash
   git clone [https://github.com/tu-usuario/FarmaCorp.git](https://github.com/tu-usuario/FarmaCorp.git)
   cd farmacorp