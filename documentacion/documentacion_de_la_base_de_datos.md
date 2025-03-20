# Documentación del Diseño de Base de Datos

## Objetivo

El objetivo de este diseño es estructurar una base de datos para gestionar información sobre edificios, direcciones administrativas, parcelas y coordenadas. Además, se contempla la gestión de observaciones y operativos de relevamiento relacionados con los edificios.

## Principios del Diseño

La solución está diseñada para:
- Evitar redundancia: Se crean tablas separadas para direcciones, parcelas y ubicaciones administrativas.
- Optimizar consultas: Se establecen relaciones mediante claves foráneas para facilitar consultas cruzadas.
- Usar tipos de datos adecuados: Se emplean TEXT, VARCHAR, NUMERIC, BOOLEAN, y GEOMETRY para almacenar coordenadas.

## Normalización del Modelo de Datos

El diseño está normalizado y cuenta con las siguientes entidades principales:
- direcciones: Almacena la dirección normalizada con código de calle y altura.
- ubicacion_administrativa: Contiene datos administrativos como comuna, barrio y código postal.
- parcelas: Representa la parcela catastral con SMP, dimensiones y superficie.
- coordenadas: Guarda la georreferenciación de la dirección.
- edificios: Representa cada edificio educativo con su CUI y atributos principales.
- predios: Contiene los datos de los predios y permite agrupar edificios.
- operativos_relevamiento: Tabla intermedia para relacionar edificios con operativos (CIE 2017, CENIE 2010 y futuros).
- edificios_direcciones: Tabla intermedia para relacionar edificios con direcciones y marcar cuál es la principal.
- instituciones: Contiene las instituciones a las que pueden pertenecer los edificios.

## Explicación del Diseño de Tablas

### Tabla direcciones
- Contiene la dirección normalizada con codigo_calle, calle y altura.
- No almacena detalles administrativos ni catastrales, sino que referencia a las otras tablas.
- Evita redundancia al no repetir información de ubicación o parcela en cada consulta.

### Tabla ubicacion_administrativa
- Contiene datos como comuna, barrio, distrito escolar, etc.
- Se relaciona con direcciones mediante ubicacion_administrativa_id.
- Centraliza información para evitar duplicaciones.

### Tabla parcelas
- Guarda información catastral (SMP, dimensiones, superficies, pisos, etc.).
- Relacionada con direcciones y puertas.

### Tabla puertas
- Almacena los accesos de una parcela.
- Permite diferenciar puertas principales u oficiales.
- Evita listas de accesos dentro de la tabla parcelas.

### Tabla coordenadas
- Almacena coordenadas geográficas.
- Compatible con PostGIS para optimizar consultas espaciales.

### Tabla edificios
- Contiene datos clave como CUI, estado, sector y gestión.
- Puede estar vinculado a un predio opcionalmente.

### Tabla predios
- Permite agrupar varios edificios en una misma unidad territorial.

### Tabla instituciones
- Almacena instituciones asociadas a los edificios.

### Tabla operativos_relevamiento
- Permite registrar múltiples relevamientos para cada edificio.

### Tabla edificios_direcciones
- Relaciona edificios con direcciones, permitiendo definir la dirección principal.

## Beneficios del Modelo
- Eficiencia: Reduce el espacio de almacenamiento.
- Mantenimiento simplificado: Al actualizar una entidad, no se generan inconsistencias.
- Velocidad de consulta: Optimiza la indexación y las relaciones para mejorar el rendimiento.

Este modelo garantiza la integridad y escalabilidad de los datos, permitiendo una gestión eficiente de la información sobre los edificios y sus atributos relacionados.

## Anexo: Scripts de Creación de Tablas
Tabla de direcciones normalizadas
```
CREATE TABLE cuis.direcciones (
    id SERIAL PRIMARY KEY,
    codigo_calle TEXT NOT NULL,
    calle TEXT NOT NULL,
    altura TEXT NOT NULL,
    ubicacion_administrativa_id INT UNIQUE,
    parcela_id INT UNIQUE,
    coordenadas_id INT UNIQUE
);
```

Tabla de información administrativa
```
CREATE TABLE cuis.ubicacion_administrativa (
    id SERIAL PRIMARY KEY,
    comuna TEXT,
    barrio TEXT,
    comisaria TEXT,
    area_hospitalaria TEXT,
    region_sanitaria TEXT,
    distrito_escolar TEXT,
    comisaria_vecinal TEXT,
    seccion_catastral TEXT,
    codigo_postal TEXT,
    codigo_postal_argentino TEXT
);
```

Tabla de parcelas catastrales
```
CREATE TABLE cuis.parcelas (
    id SERIAL PRIMARY KEY,
    smp TEXT UNIQUE NOT NULL,
    seccion TEXT,
    manzana TEXT,
    parcela TEXT,
    superficie_total NUMERIC,
    superficie_cubierta NUMERIC,
    frente NUMERIC,
    fondo NUMERIC,
    propiedad_horizontal BOOLEAN,
    pisos_bajo_rasante INT,
    pisos_sobre_rasante INT,
    unidades_funcionales INT,
    locales INT
);
```

Tabla de puertas asociadas a una parcela
```
CREATE TABLE cuis.puertas (
    id SERIAL PRIMARY KEY,
    parcela_id INT NOT NULL,
    codigo_calle TEXT NOT NULL,
    calle TEXT NOT NULL,
    altura TEXT NOT NULL,
    puerta_principal BOOLEAN,
    puerta_oficial BOOLEAN,
    fuente TEXT,
    fecha_actualizacion DATE,
    FOREIGN KEY (parcela_id) REFERENCES cuis.parcelas(id) ON DELETE CASCADE
);
```

Tabla de coordenadas
```
CREATE TABLE cuis.coordenadas (
    id SERIAL PRIMARY KEY,
    x_gkba NUMERIC NOT NULL,
    y_gkba NUMERIC NOT NULL,
    x_wgs84 NUMERIC NOT NULL,
    y_wgs84 NUMERIC NOT NULL,
    geom_gkba GEOMETRY,
    geom_wgs84 GEOMETRY
);
```

Crear relaciones entre tablas
```
ALTER TABLE cuis.direcciones ADD CONSTRAINT fk_ubicacion FOREIGN KEY (ubicacion_administrativa_id) REFERENCES cuis.ubicacion_administrativa(id) ON DELETE SET NULL;
ALTER TABLE cuis.direcciones ADD CONSTRAINT fk_parcela FOREIGN KEY (parcela_id) REFERENCES cuis.parcelas(id) ON DELETE SET NULL;
ALTER TABLE cuis.direcciones ADD CONSTRAINT fk_coordenadas FOREIGN KEY (coordenadas_id) REFERENCES cuis.coordenadas(id) ON DELETE SET NULL;
```

Tabla de Usuarios
```
CREATE TABLE IF NOT EXISTS cuis.usuarios (
    id SERIAL PRIMARY KEY,
    nombre TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    rol TEXT CHECK (rol IN ('admin', 'editor', 'visualizador')) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT now()
);
```

Tabla de Predios
```
CREATE TABLE IF NOT EXISTS cuis.predios (
    id SERIAL PRIMARY KEY,
    cup TEXT UNIQUE NOT NULL,
    nombre TEXT
);
```

Tabla de Edificios
```
CREATE TABLE IF NOT EXISTS cuis.edificios (
    id SERIAL PRIMARY KEY,
    cui TEXT UNIQUE NOT NULL,
    estado TEXT CHECK (estado IN ('activo', 'inactivo', 'baja', 'proyecto', 'otro', 'sin datos')) NOT NULL,
    sector TEXT CHECK (sector IN ('publico', 'privado', 'otro', 'sin datos')) NOT NULL,
    predio_id INT REFERENCES cuis.predios(id) ON DELETE SET NULL,
    x NUMERIC,
    y NUMERIC,
    gestionado BOOLEAN NOT NULL,
    institucion TEXT,
    fecha_creacion TIMESTAMP DEFAULT now()
);
```

Relación Edificios - Direcciones (Muchos a Muchos, con una principal)
```
CREATE TABLE IF NOT EXISTS cuis.edificios_direcciones (
    id SERIAL PRIMARY KEY,
    edificio_id INT REFERENCES cuis.edificios(id) ON DELETE CASCADE,
    direccion_id INT REFERENCES cuis.direcciones(id) ON DELETE CASCADE,
    es_principal BOOLEAN DEFAULT FALSE,
    CONSTRAINT unica_principal UNIQUE (edificio_id, es_principal)
);
```

Tabla de Operativos de Relevamiento
```
CREATE TABLE IF NOT EXISTS cuis.operativos (
    id SERIAL PRIMARY KEY,
    nombre TEXT NOT NULL UNIQUE
);
```

Relación Edificios - Operativos
```
CREATE TABLE IF NOT EXISTS cuis.relevamientos (
    id SERIAL PRIMARY KEY,
    edificio_id INT REFERENCES cuis.edificios(id) ON DELETE CASCADE,
    operativo_id INT REFERENCES cuis.operativos(id) ON DELETE CASCADE,
    relevado BOOLEAN NOT NULL
);
```

Tabla de Registro de Cambios
```
CREATE TABLE IF NOT EXISTS cuis.registro_cambios (
    id SERIAL PRIMARY KEY,
    edificio_id INT REFERENCES cuis.edificios(id) ON DELETE CASCADE,
    usuario_id INT REFERENCES cuis.usuarios(id) ON DELETE SET NULL,
    fecha_cambio TIMESTAMP DEFAULT now()
);
```

Tabla de Observaciones
```
CREATE TABLE IF NOT EXISTS cuis.observaciones (
    id SERIAL PRIMARY KEY,
    edificio_id INT REFERENCES cuis.edificios(id) ON DELETE CASCADE,
    usuario_id INT REFERENCES cuis.usuarios(id) ON DELETE SET NULL,
    observacion TEXT NOT NULL,
    fecha_observacion TIMESTAMP DEFAULT now()
);
```
