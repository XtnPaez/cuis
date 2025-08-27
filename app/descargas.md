# Catálogo de Descargas — Definición de Estructura

## Objetivo

Diseñar un sistema de descargas **metadata-driven**, donde el front sea 100% genérico y los recursos/acciones se definan desde la base de datos (Postgres).  

Esto evita tener que modificar código cada vez que se agregue un nuevo recurso o formato de descarga.

---

## Tablas principales

### 1. `catalogo`

Representa los recursos disponibles (ej. Padrón CUI actualizado, snapshot trimestral, etc.).

| Campo            | Tipo        | Descripción                                                                 |
|------------------|------------|-----------------------------------------------------------------------------|
| `id`             | PK         | Identificador único del recurso                                             |
| `titulo`         | text       | Nombre a mostrar (ej. "Padrón CUI 30/03/2025")                              |
| `descripcion`    | text       | Breve explicación del recurso                                               |
| `id_query`       | FK → querys.id | Referencia a la consulta o vista que define el recurso                      |
| `fecha_creacion` | timestamp  | Cuándo se creó el recurso (ej. fecha de snapshot)                           |

---

### 2. `querys`

Define **cómo se obtiene** el recurso (query SQL, vista materializada, archivo pre-generado).

| Campo        | Tipo    | Descripción                                                  |
|--------------|--------|--------------------------------------------------------------|
| `id`         | PK     | Identificador único                                          |
| `nombre`     | text   | Nombre descriptivo                                           |
| `sql_firma`  | text   | SQL de referencia o nombre de vista/materialized view        |
| `tipo`       | enum   | `dinamico`, `snapshot`, `archivo` (define comportamiento)    |

---

### 3. `catalogo_acciones`

Asocia recursos (`catalogo`) con las acciones/formatos disponibles.  

Ejemplo: un recurso puede ofrecer `ver + csv + xlsx`, otro `ver + geojson`, otro `sql` únicamente.

| Campo         | Tipo    | Descripción                                                              |
|---------------|--------|--------------------------------------------------------------------------|
| `id`          | PK     | Identificador único                                                      |
| `id_catalogo` | FK → catalogo.id | Recurso al que pertenece                                         |
| `accion`      | text   | Acción (`ver`, `descargar`)                                              |
| `formato`     | text   | Formato (`csv`, `xlsx`, `geojson`, `sql`, `preview`, etc.)               |
| `uri_archivo` | text   | (Opcional) Ruta al archivo si está pre-generado (Excel/CSV estático)     |

---

## Flujo Frontend ↔ Backend

### 1. Listado de recursos
- **Endpoint**: `/catalogo/listar`
- **Backend**: devuelve JSON con recursos y sus acciones.
- **Ejemplo de respuesta**:

```json
[
  {
    "id": 1,
    "titulo": "Padrón CUI (actualizado)",
    "descripcion": "Listado de CUIs al día de la fecha",
    "fecha_creacion": null,
    "acciones": [
      { "accion": "ver", "formato": "preview" },
      { "accion": "descargar", "formato": "csv" },
      { "accion": "descargar", "formato": "xlsx" }
    ]
  },
  {
    "id": 2,
    "titulo": "Padrón CUI 30/03/2025",
    "descripcion": "Listado de CUIs en marzo 2025",
    "fecha_creacion": "2025-03-30 11:30:16",
    "acciones": [
      { "accion": "ver", "formato": "preview" },
      { "accion": "descargar", "formato": "csv" }
    ]
  },
  {
    "id": 3,
    "titulo": "Edificios con coordenadas",
    "descripcion": "Export de edificios georreferenciados",
    "fecha_creacion": "2025-06-30 11:30:11",
    "acciones": [
      { "accion": "ver", "formato": "preview" },
      { "accion": "descargar", "formato": "geojson" }
    ]
  }
]
```

### 2. Acción sobre un recurso

Endpoint: /catalogo/accion?id=XX&accion=descargar&formato=csv

Backend:
- Busca en catalogo_acciones la acción solicitada.
- Si existe uri_archivo, sirve archivo directamente.
- Si no, usa querys.sql_firma para ejecutar y generar salida.

Front: 
- todos los botones llaman a una misma función genérica handleAction(id, accion, formato).

Beneficios: 
- Escalabilidad: se agregan recursos/acciones solo tocando metadata en Postgres.
- Front genérico: no necesita modificaciones al crecer el catálogo.
- Auditoría y trazabilidad: queries centralizadas en querys; recursos versionados en catalogo.
- Flexibilidad: un recurso puede ofrecer múltiples formatos según su naturaleza.