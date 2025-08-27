# Catálogo de Descargas — Especificación del Sistema

## Objetivo

Diseñar un sistema de descargas **metadata-driven** basado en PostgreSQL, donde el frontend sea 100% genérico y los recursos, consultas y formatos de descarga se definan completamente en la base de datos. Este enfoque elimina la necesidad de modificar código para agregar nuevos recursos o formatos, facilitando el mantenimiento y la escalabilidad.

El sistema gestiona descargas de listados provenientes de tablas de Padrón CUI que se actualizan frecuentemente, con snapshots trimestrales almacenados como vistas materializadas (VM). Se generan 4 snapshots por año y cuando se crea el quinto, el más antiguo se elimina. El módulo de Descargas se enfoca exclusivamente en descargas (CSV, XLSX, GeoJSON, y potencialmente Word/PDF pregenerados), con la acción `ver` (preview) como un "nice to have".

## Contexto

- **Datos**: 4 tablas de Padrón CUI por año y la actual.
- **Snapshots**: 4 vistas materializadas por año, creadas trimestralmente vía trigger. La VM más antigua se marca con estado `inactiva` al generar la quinta.
- **Formatos**: CSV y XLSX (generados con phpspreadsheet), GeoJSON (generado con PostGIS), y potencialmente Word/PDF (pregenerados, almacenados como archivos).
- **Permisos**: Acceso binario controlado por login; los usuarios autorizados acceden a todos los recursos, los no autorizados no ven el módulo.
- **Frontend**: 100% genérico, con una función `handleAction(id, accion, formato)` para manejar acciones.
- **Backend**: Endpoints REST que consultan las tablas de metadatos y sirven datos o archivos.

## Estructura de la Base de Datos

### 1. `catalogo`

Almacena los recursos disponibles (snapshots del Padrón CUI o datos dinámicos).

| Campo            | Tipo        | Descripción                                                                 |
|------------------|------------|-----------------------------------------------------------------------------|
| `id`             | PK         | Identificador único del recurso                                             |
| `titulo`         | text       | Nombre a mostrar (ej. "Padrón CUI 30/03/2025")                              |
| `descripcion`    | text       | Breve explicación del recurso                                               |
| `id_query`       | FK → querys.id | Referencia a la consulta o vista que define el recurso                  |
| `fecha_creacion` | timestamp  | Fecha de creación del recurso (ej. fecha del snapshot)                     |
| `estado`         | enum       | `activo`, `inactivo` (para marcar recursos obsoletos)                      |

### 2. `querys`

Define cómo se obtiene el recurso (consulta SQL sobre una vista materializada).

| Campo        | Tipo    | Descripción                                                  |
|--------------|--------|--------------------------------------------------------------|
| `id`         | PK     | Identificador único                                          |
| `nombre`     | text   | Nombre descriptivo (ej. "Padrón CUI 30/03/2025")             |
| `sql_firma`  | text   | Consulta SQL (ej. `SELECT * FROM vm_padron_cui_20250330`)    |
| `tipo`       | enum   | `snapshot` (para VMs), `dinamico` (futuro), `archivo` (futuro) |

**Notas**:
- `sql_firma` contiene un `SELECT` simple sobre la VM correspondiente.
- Convención de nombres para VMs: `vm_padron_cui_[AAAAMMDD]` (ej. `vm_padron_cui_20250330`).
- Ejemplo: `{ id: 1, nombre: "Padrón CUI 30/03/2025", sql_firma: "SELECT * FROM vm_padron_cui_20250330", tipo: "snapshot" }`.

### 3. `catalogo_acciones`

Asocia recursos con acciones y formatos disponibles (los "botones" de cada card en el frontend).

| Campo         | Tipo    | Descripción                                                              |
|---------------|--------|--------------------------------------------------------------------------|
| `id`          | PK     | Identificador único                                                      |
| `id_catalogo` | FK → catalogo.id | Recurso al que pertenece                                       |
| `accion`      | text   | Acción (`descargar`, opcional `ver`)                                    |
| `formato`     | FK → formatos.id | Formato (`csv`, `xlsx`, `geojson`, `pdf`)                      |
| `uri_archivo` | text   | Ruta al archivo pregenerado (ej. "/archivos/padron_20250330.pdf")        |
| `orden`       | integer | Orden de visualización de los botones en el frontend                     |

**Notas**:
- `uri_archivo` se usa para formatos pregenerados (Word/PDF).
- Ejemplo: `{ id: 1, id_catalogo: 1, accion: "descargar", formato: 1, uri_archivo: null, orden: 1 }` (para CSV).
- `orden` controla la posición de los botones en el frontend.

### 4. `formatos`

Estandariza los formatos soportados para descargas.

| Campo         | Tipo    | Descripción                                                              |
|---------------|--------|--------------------------------------------------------------------------|
| `id`          | PK     | Identificador único                                                      |
| `nombre`      | text   | Nombre del formato (ej. `csv`, `xlsx`, `geojson`, `pdf`)                 |
| `extension`   | text   | Extensión del archivo (ej. `.csv`, `.xlsx`, `.geojson`, `.pdf`)          |
| `content_type`| text   | MIME type (ej. `text/csv`, `application/pdf`)                            |

**Notas**:
- Ejemplo: `{ id: 1, nombre: "csv", extension: ".csv", content_type: "text/csv" }`.
- Permite agregar nuevos formatos sin modificar código.

### 5. `error_log`

Registra errores en la ejecución de consultas o generación de archivos.

| Campo         | Tipo    | Descripción                                                              |
|---------------|--------|--------------------------------------------------------------------------|
| `id`          | PK     | Identificador único                                                      |
| `id_query`    | FK → querys.id | Consulta que falló (opcional)                                     |
| `error_message` | text  | Detalle del error (ej. "Vista materializada no encontrada")              |
| `timestamp`   | timestamp | Fecha y hora del error                                                |
| `usuario`     | text   | Usuario que disparó la acción (opcional)                                 |

**Notas**:
- Sirve para debuggear fallos (VM inexistente, error de formato, etc.).

## Flujo Frontend ↔ Backend

### 1. Listado de Recursos
- **Endpoint**: `/catalogo/listar`
- **Descripción**: Devuelve un JSON con los recursos disponibles y sus acciones, filtrado por permisos del usuario (solo usuarios autenticados acceden).
- **Respuesta**:
  ```json
  [
    {
      "id": 1,
      "titulo": "Padrón CUI 30/03/2025",
      "descripcion": "Snapshot trimestral del padrón al 30/03/2025",
      "fecha_creacion": "2025-03-30 11:30:16",
      "estado": "activo",
      "acciones": [
        { "accion": "descargar", "formato": "csv", "orden": 1 },
        { "accion": "descargar", "formato": "xlsx", "orden": 2 },
        { "accion": "descargar", "formato": "geojson", "orden": 3 }
      ]
    },
    {
      "id": 2,
      "titulo": "Padrón CUI 30/06/2025",
      "descripcion": "Snapshot trimestral del padrón al 30/06/2025",
      "fecha_creacion": "2025-06-30 11:30:16",
      "estado": "activo",
      "acciones": [
        { "accion": "descargar", "formato": "csv", "orden": 1 },
        { "accion": "descargar", "formato": "pdf", "orden": 2, "uri_archivo": "/archivos/padron_20250630.pdf" }
      ]
    }
  ]
  ```
Permisos: Si el usuario no está autorizado, devuelve HTTP 403 con { "error": "No tenés permiso para acceder a Descargas" }.

### 2. Acción sobre un Recurso

Endpoint: /catalogo/accion?id=XX&accion=descargar&formato=csv

Descripción:
- Busca en catalogo_acciones la acción solicitada.
- Si hay uri_archivo, sirve el archivo directamente con el content_type de formatos.
- Si no, ejecuta el SELECT de querys.sql_firma y genera el archivo (CSV, XLSX, GeoJSON).
- Valida la existencia de la VM antes de ejecutar (SELECT EXISTS en pg_matviews).

Errores: 
- VM no encontrada: HTTP 500, { "error": "Recurso no disponible, contactá al administrador" }.
- Timeout o error de consulta: HTTP 500, { "error": "No se pudo generar el archivo, intentá de nuevo" }.
- Loguea todos los errores en error_log.

Frontend: 
- Llama a la función genérica handleAction(id, accion, formato) para disparar la acción.

## 3. Previews (Opcional)

Endpoint: /catalogo/accion?id=XX&accion=ver&formato=preview

Descripción: Devuelve un JSON con las primeras 50 filas de la VM para mostrar una tabla en el frontend (feature futuro, no prioritario).

Respuesta: { "columnas": ["col1", "col2", ...], "filas": [{}, {}, ...] }.

Notas: Puede usar una librería como DataTables para renderizar la tabla.

## Automatización de Snapshots

Frecuencia: 4 snapshots por año (trimestrales), ~4000 registros por VM, estructura idéntica.

Proceso:
- Un trigger o job (pg_cron) crea una VM (vm_padron_cui_[AAAAMMDD]).
- Inserta en querys (sql_firma: "SELECT * FROM vm_padron_cui_[AAAAMMDD]", tipo: "snapshot").
- Inserta en catalogo (titulo: "Padrón CUI [DD/MM/YYYY]", descripcion, fecha_creacion, id_query, estado: "activo").
- Inserta en catalogo_acciones las acciones/formatos disponibles (ej. descargar/csv, descargar/xlsx, descargar/geojson).
- Si hay 4 VMs, cambia el estado de la mas antigua.

Convención de nombres: vm_padron_cui_[AAAAMMDD] (ej. vm_padron_cui_20250330).

Notas: La implementación del trigger/job se definirá en una fase posterior.

## Manejo de Errores

Validaciones:
- Verificar existencia de la VM (SELECT EXISTS en pg_matviews).
- Timeout de 30 segundos para consultas.
- Límite de 5000 filas por consulta (suficiente para 4000 registros).

Logging: Errores en error_log (VM no encontrada, error de formato, etc.).

Feedback al usuario:
- HTTP 403: { "error": "No tenés permiso para acceder a Descargas" }.
- HTTP 500: { "error": "Recurso no disponible, contactá al administrador" } o { "error": "No se pudo generar el archivo, intentá de nuevo" }.
- El frontend muestra mensajes en un toast o alerta.

## Formatos Soportados
- CSV: Generado con phpspreadsheet.
- XLSX: Generado con phpspreadsheet.
- GeoJSON: Generado con PostGIS (ST_AsGeoJSON, validado con ST_IsValid).
- Word/PDF: Archivos pregenerados, almacenados en uri_archivo.

Notas:
- Validar datos antes de generar archivos (especialmente GeoJSON).
- Cachear archivos generados en disco (opcional, si las descargas son frecuentes).
- Consultar al equipo por formatos adicionales (poco probable).

## Permisos
- Acceso: Binario, controlado por el sistema de login.
- Usuarios autorizados acceden a todos los recursos.
- Usuarios no autorizados reciben HTTP 403.
- Auditoría: No requerida por ahora; opcional en el futuro con una tabla descargas_log (id_catalogo, usuario, formato, timestamp).

## Consideraciones Técnicas

Librerías:
- phpspreadsheet para CSV/XLSX.
- PostGIS para GeoJSON.
- Archivos Word/PDF servidos directamente con content_type correcto.

## Mantenimiento: 
- Job periódico para verificar VMs y marcar recursos inactivos en catalogo.

## Diagrama de Flujo

Evento trimestral:
- Trigger/job crea vm_padron_cui_[AAAAMMDD].
- Inserta en querys, catalogo, catalogo_acciones.
- Si hay 4 VMs, cambia de estado la más antigua.

## Usuario autenticado:
- Llama a /catalogo/listar → JSON con recursos y botones.
- Llama a /catalogo/accion → Descarga archivo o recibe error.

## Errores:
- Valida VM, loguea en error_log, notifica al usuario.

## Tareas Pendientes

Documentación:
- Finalizar estructura de triggers/jobs para automatización.
- Detallar manejo de errores en cada endpoint.

Implementación:
- Crear tablas (catalogo, querys, catalogo_acciones, formatos, error_log).
- Desarrollar endpoints y frontend genérico.
- Implementar trigger/job para VMs y eliminación automática.

Pruebas:
- Prototipar trigger/job en entorno de prueba.
- Validar generación de CSV, XLSX, GeoJSON, y entrega de Word/PDF.

Notas Finales

Este sistema está diseñado para ser flexible, escalable y fácil de mantener, con un frontend genético y un backend basado en metadatos. La automatización de snapshots y el manejo robusto de errores aseguran una experiencia confiable para los usuarios. La acción ver se deja como feature opcional para una fase futura.

May the force be with you, jedi master.
