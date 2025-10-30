# Roadmap técnico del proyecto CUI

## Situación actual

El sistema de gestión de CUI (Código Único de Infraestructura) se encuentra en un estado avanzado de desarrollo.

Las principales funcionalidades —como la búsqueda, visualización geográfica, generación de QRs y descargas— están operativas y consolidadas.

El código muestra el rastro de un desarrollo progresivo y modular, con componentes funcionales pero estructuralmente heterogéneos.

## Diagnóstico general

Fortalezas:
- Núcleo funcional estable y probado.
- Integración sólida con PostgreSQL/PostGIS.
- Uso correcto de CUI como campo pivote entre distintas fuentes.
- Arquitectura flexible basada en PHP crudo y Ajax.

Debilidades detectadas:
- Estructura de carpetas no unificada y mezcla de roles (lógica, vistas, endpoints, recursos).
- Repetición de lógicas en distintos archivos (ej. búsquedas y descargas).
- Falta de normalización completa y auditoría en algunas tablas.
- Ausencia de convención en nombres y rutas.
- Falta de vistas o consultas consolidadas para la API.

## 3. Recomendaciones estructurales

### Organización de carpetas

Propuesta de esquema más claro y escalable:

/config/        → parámetros de conexión y constantes globales
/core/          → funciones utilitarias, helpers, conexión DB
/controllers/   → endpoints PHP (Ajax, API)
  /cui/
  /descargas/
  /usuarios/
  ...
/views/         → vistas PHP o HTML puras
/assets/
  /css/
  /js/
  /images/
/storage/       → archivos generados (QR, etiquetas, exportaciones)
/db/            → estructura SQL y seeds
/docs/          → manuales, roadmap, documentación técnica

### Estructura de la base de datos

- Consolidar todo en un esquema único (cuis o infraestructura).
- Incorporar auditoría básica: created_at, updated_at, usuario_id.
- Normalizar tablas de referencia (tipos, estados, fuentes).
- Crear vistas unificadas (vw_cui_detalle, vw_inconsistencias).
- Asegurar índices GIST en campos geométricos.

## Estrategia de desarrollo

Se adopta un enfoque mixto y progresivo:
- Congelar módulos estables: QR, login, búsqueda básica.
- Completar funcionalidades faltantes:
- Carga y edición de CUI.
- Módulo de inconsistencias.
- Reportes y descargas avanzadas.

Refactorización final:
- Reorganizar estructura de carpetas.
- Unificar includes y helpers.
- Revisar rutas, documentación y SQLs.
- Cada etapa se documentará mediante issues y commits separados.

## Próximos pasos (a grandes rasgos)

- Issue 1: Confirmar estructura funcional actual y definir carpeta /core/.
- Issue 2: Crear esquema SQL base con auditoría y vistas recomendadas.
- Issue 3: Centralizar funciones comunes en un archivo core/functions.php.
- Issue 4: Revisar y estandarizar endpoints AJAX / API.
- Issue 5: Diseñar plan de refactorización visual (vistas y includes).

Una vez completados estos cinco pasos, se procederá a la fase 2 del roadmap: normalización total de la base + reorganización definitiva del código + documentación técnica final.