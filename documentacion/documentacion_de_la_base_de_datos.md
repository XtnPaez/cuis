# Documentación del Diseño de Base de Datos

## 1 Objetivo
El objetivo de este diseño es estructurar una base de datos para gestionar información sobre edificios, niveles y modalidades de los mismos (nivmod), direcciones administrativas, parcelas y coordenadas. 
Además, se contempla la gestión de observaciones y operativos de relevamiento relacionados con los edificios. 
Con la modificación propuesta, se incorpora una relación muchos a muchos entre edificios y nivmod, permitiendo que cada edificio pueda estar asociado a uno o más niveles y modalidades.

## 2 Tablas principales

### 2.1 Tabla direccion_administrativa
Esta tabla almacena información administrativa asociada a las ubicaciones de los edificios.

```
CREATE TYPE sector AS ENUM ('PÚBLICO', 'PRIVADO', 'OTRO', 'SIN DATOS');
CREATE TYPE estado_edificio AS ENUM ('ACTIVO', 'INACTIVO', 'BAJA', 'PROYECTO', 'OTRO', 'SIN DATOS');
CREATE TABLE cuis.direccion_administrativa (
    id SERIAL PRIMARY KEY,
    comuna character varying(16),
    barrio character varying(64),
    comisaria character varying(32),
    area_hospitalaria character varying(32),
    region_sanitaria character varying(32),
    distrito_escolar character varying(64),
    comisaria_vecinal character varying(16),
    seccion_catastral integer,
    distrito_economico character varying(64),
    codigo_de_planeamiento_urbano character varying(16)
);
```
Descripción:

•	sector y estado_edificio son tipos de datos ENUM que se utilizan para clasificar los edificios.

•	Almacena información administrativa de ubicación de los edificios, como la comuna, barrio, comisaría, entre otros.

### 2.2 Tabla parcela
Almacena información geográfica y catastral de las parcelas donde se encuentran los edificios.

```
CREATE TABLE cuis.parcela (
    id SERIAL PRIMARY KEY,
    smp character varying(16),
    seccion character varying(16),
    manzana character varying(16),
    parcela character varying(16),
    superficie_total NUMERIC,
    superficie_cubierta NUMERIC,
    frente NUMERIC,
    fondo NUMERIC,
    pisos_bajo_rasante INT,
    pisos_sobre_rasante INT,
    fuente character varying(64)
);
```

Descripción:

•	Almacena información catastral de cada parcela: sección, manzana, parcela, superficies y detalles sobre los pisos.
 
### 2.3 Tabla coordenadas
Almacena las coordenadas geográficas en dos sistemas de referencia: GKBA y WGS84.

```
CREATE TABLE cuis.coordenadas (
    id SERIAL PRIMARY KEY,
    x_gkba NUMERIC(20,10),
    y_gkba NUMERIC(20,10),
    x_wgs84 NUMERIC(10,6),
    y_wgs84 NUMERIC(10,6)
);
```

Descripción:

•	Guarda las coordenadas geográficas de los edificios en los sistemas de coordenadas GKBA y WGS84.
 
### 2.4 Tabla nivmod
Almacena información sobre los niveles y modalidades de los edificios.

```
CREATE TABLE cuis.nivmod (
    id SERIAL PRIMARY KEY,
    nivel character varying(32),
    nivel_sigla character varying(4),
    modalidad character varying(32),
    modalidad_sigla character varying(4),
    sigla_combinada character varying(8) GENERATED ALWAYS AS (initcap(nivel_sigla || modalidad_sigla)) STORED
);
```

Descripción:

•	La tabla nivmod almacena los niveles y modalidades de los edificios, junto con las siglas correspondientes. La columna sigla_combinada es generada automáticamente a partir de las siglas del nivel y modalidad.
 
### 2.5 Tabla edificio_nivmod
Esta tabla intermedia gestiona la relación muchos a muchos entre los edificios y los nivmod.

```
CREATE TABLE cuis.edificio_nivmod (
    edificio_id INT REFERENCES cuis.edificios(id) ON DELETE CASCADE,
    nivmod_id INT REFERENCES cuis.nivmod(id) ON DELETE CASCADE,
    PRIMARY KEY (edificio_id, nivmod_id)
);
```

Descripción:

•	Relaciona cada edificio con uno o más nivmod.

•	Cada registro en esta tabla asocia un edificio a un nivel y modalidad específica, permitiendo que un edificio esté vinculado a varios niveles y modalidades.

•	La relación es de muchos a muchos.

### 2.6 Tabla direcciones
Almacena la información de las direcciones de los edificios, incluyendo referencias a la ubicación administrativa, parcela y coordenadas.

```
CREATE TABLE cuis.direcciones (
    id SERIAL PRIMARY KEY,
    codigo_calle integer NOT NULL,
    calle character varying(128) NOT NULL,
    altura int NOT NULL,
    codigo_postal character varying(8),
    codigo_postal_argentino character varying(16),
    ubicacion_administrativa_id INT REFERENCES direccion_administrativa(id),
    parcela_id INT REFERENCES parcela(id),
    coordenadas_id INT REFERENCES coordenadas(id),
    fuente character varying(32),
    fecha_actualizacion TIMESTAMP
);
```

Descripción:

•	Registra las direcciones físicas de los edificios, incluyendo información adicional sobre su ubicación administrativa, catastral y geográfica.
 
### 2.7 Tabla predios
Almacena información sobre los predios donde se encuentran los edificios.

```
CREATE TABLE cuis.predios (
    id SERIAL PRIMARY KEY,
    nombre character varying(32) NOT NULL
);
```

Descripción:

•	Almacena los nombres de los predios, que pueden agrupar varios edificios.
 
### 2.8 Tabla usuarios
Gestiona la autenticación y seguimiento de los usuarios que realizan modificaciones en los edificios.

```
CREATE TABLE cuis.usuarios (
    id SERIAL PRIMARY KEY,
    nombre character varying(32) NOT NULL,
    email character varying(128),
    rol character varying(32) NOT NULL
);
```

Descripción:

•	Almacena información sobre los usuarios que pueden modificar la base de datos, incluyendo su nombre, correo electrónico y rol.
 
### 2.9 Tabla edificios
Almacena la información principal de los edificios, incluyendo su estado, sector, ubicación y coordenadas.

```
CREATE TABLE cuis.edificios (
    id SERIAL PRIMARY KEY,
    estado estado_edificio NOT NULL,
    sector sector NOT NULL,
    direccion_principal_id INT UNIQUE REFERENCES direcciones(id) ON DELETE SET NULL,
    predio_id INT REFERENCES predios(id),
    x_gkba NUMERIC(20,10),
    y_gkba NUMERIC(20,10),
    usuario_modificacion INT REFERENCES usuarios(id) NOT NULL,
    fecha_modificacion TIMESTAMP NOT NULL
);
```

Descripción:

•	Registra los detalles básicos de los edificios, incluyendo su estado (activo, inactivo, baja, etc.), sector (público, privado, etc.) y referencias a la dirección principal y predio.

### 2.10 Tabla operativos_relevamiento
Almacena información sobre los operativos de relevamiento realizados en los edificios.

```
CREATE TABLE cuis.operativos_relevamiento (
    id SERIAL PRIMARY KEY,
    edificio_id INT REFERENCES edificaciones(id),
    operativo character varying(16), 
    fecha_relevamiento TIMESTAMP
);
```

Descripción:

•	Registra los operativos de relevamiento realizados sobre los edificios, junto con la fecha en que se realizaron.
 
### 2.11 Tabla observaciones
Permite registrar observaciones sobre los edificios realizadas por los usuarios.

```
CREATE TABLE cuis.observaciones (
    id SERIAL PRIMARY KEY,
    edificio_id INT REFERENCES edificaciones(id),
    comentario TEXT,
    usuario_id INT REFERENCES usuarios(id),  
    fecha TIMESTAMP
);
```

Descripción:

•	Registra comentarios o anotaciones sobre los edificios, junto con el usuario que realizó la observación y la fecha de la misma.
 
## 3 Relaciones entre las Tablas

* Tabla edificio_nivmod: Relaciona un edificio con uno o más nivmod.
* Tabla edificios: Relaciona un edificio con direcciones, predios y usuarios (para seguimiento de modificaciones).
* Tablas direcciones, predios, coordenadas y parcela: Almacenan información geográfica y administrativa asociada a cada edificio.
  
Este diseño facilita la gestión y el análisis de los edificios, proporcionando una estructura flexible y escalable para asociar edificios con múltiples niveles y modalidades, a la vez que mantiene una organización eficiente de los datos.


