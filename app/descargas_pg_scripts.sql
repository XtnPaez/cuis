-- =====================================================
-- SISTEMA DE CATÁLOGO DE DESCARGAS - IMPLEMENTACIÓN COMPLETA
-- =====================================================

-- 1. CREAR TABLAS PRINCIPALES
-- =====================================================

-- Tabla de formatos soportados
CREATE TABLE formatos (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(50) UNIQUE NOT NULL,
    extension VARCHAR(10) NOT NULL,
    content_type VARCHAR(100) NOT NULL
);

-- Insertar formatos básicos
INSERT INTO formatos (nombre, extension, content_type) VALUES
('csv', '.csv', 'text/csv'),
('xlsx', '.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
('geojson', '.geojson', 'application/geo+json'),
('pdf', '.pdf', 'application/pdf'),
('docx', '.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

-- Tabla de consultas/queries
CREATE TABLE querys (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    sql_firma TEXT NOT NULL,
    tipo VARCHAR(50) DEFAULT 'snapshot' CHECK (tipo IN ('snapshot', 'dinamico', 'archivo')),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla principal del catálogo
CREATE TABLE catalogo (
    id SERIAL PRIMARY KEY,
    titulo VARCHAR(300) NOT NULL,
    descripcion TEXT,
    id_query INTEGER REFERENCES querys(id) ON DELETE CASCADE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado VARCHAR(20) DEFAULT 'activo' CHECK (estado IN ('activo', 'inactivo'))
);

-- Tabla de acciones disponibles por recurso
CREATE TABLE catalogo_acciones (
    id SERIAL PRIMARY KEY,
    id_catalogo INTEGER REFERENCES catalogo(id) ON DELETE CASCADE,
    accion VARCHAR(50) NOT NULL,
    formato INTEGER REFERENCES formatos(id),
    uri_archivo VARCHAR(500), -- Para archivos pregenerados
    orden INTEGER DEFAULT 1,
    UNIQUE(id_catalogo, accion, formato)
);

-- Tabla de logs de errores
CREATE TABLE error_log (
    id SERIAL PRIMARY KEY,
    id_query INTEGER REFERENCES querys(id),
    error_message TEXT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario VARCHAR(100),
    contexto TEXT -- Info adicional del error
);

-- Crear índices
CREATE INDEX idx_catalogo_estado ON catalogo(estado);
CREATE INDEX idx_catalogo_fecha ON catalogo(fecha_creacion DESC);
CREATE INDEX idx_querys_tipo ON querys(tipo);
CREATE INDEX idx_catalogo_acciones_recurso ON catalogo_acciones(id_catalogo);
CREATE INDEX idx_error_log_timestamp ON error_log(timestamp DESC);

-- =====================================================
-- 2. FUNCIONES DE UTILIDAD
-- =====================================================

-- Función para validar existencia de vista materializada
CREATE OR REPLACE FUNCTION vm_exists(vm_name TEXT) 
RETURNS BOOLEAN AS $$
BEGIN
    RETURN EXISTS(
        SELECT 1 FROM pg_matviews 
        WHERE schemaname = 'public' AND matviewname = vm_name
    );
END;
$$ LANGUAGE plpgsql;

-- Función para obtener el nombre de la VM más antigua
CREATE OR REPLACE FUNCTION get_oldest_vm() 
RETURNS TEXT AS $$
DECLARE
    oldest_vm TEXT;
BEGIN
    SELECT SUBSTRING(sql_firma FROM 'vm_padron_cui_(\d{8})')
    INTO oldest_vm
    FROM querys q
    JOIN catalogo c ON c.id_query = q.id
    WHERE q.tipo = 'snapshot' AND c.estado = 'activo'
    ORDER BY c.fecha_creacion ASC
    LIMIT 1;
    
    RETURN 'vm_padron_cui_' || oldest_vm;
END;
$$ LANGUAGE plpgsql;

-- Función para contar VMs activas
CREATE OR REPLACE FUNCTION count_active_snapshots() 
RETURNS INTEGER AS $$
BEGIN
    RETURN (
        SELECT COUNT(*)
        FROM catalogo c
        JOIN querys q ON q.id = c.id_query
        WHERE q.tipo = 'snapshot' AND c.estado = 'activo'
    );
END;
$$ LANGUAGE plpgsql;

-- =====================================================
-- 3. FUNCIÓN PRINCIPAL PARA CREAR SNAPSHOT
-- =====================================================

CREATE OR REPLACE FUNCTION crear_snapshot_trimestral() 
RETURNS TEXT AS $$
DECLARE
    fecha_snapshot TEXT;
    vm_name TEXT;
    query_sql TEXT;
    new_query_id INTEGER;
    new_catalogo_id INTEGER;
    oldest_vm TEXT;
    registro_count INTEGER;
    error_msg TEXT;
BEGIN
    -- Generar nombre con fecha actual (YYYYMMDD)
    fecha_snapshot := TO_CHAR(CURRENT_DATE, 'YYYYMMDD');
    vm_name := 'vm_padron_cui_' || fecha_snapshot;
    
    -- Verificar si ya existe la VM para esta fecha
    IF vm_exists(vm_name) THEN
        RETURN 'ERROR: Ya existe snapshot para la fecha ' || TO_CHAR(CURRENT_DATE, 'DD/MM/YYYY');
    END IF;
    
    BEGIN
        -- Crear la vista materializada
        query_sql := 'CREATE MATERIALIZED VIEW ' || vm_name || ' AS ' ||
                    'SELECT cui, estado, sector, gestionado, predio, direccion_principal, ' ||
                    'comuna, barrio, codigo_postal, x_gkba, y_gkba, x_wgs84, y_wgs84 ' ||
                    'FROM cuis.v_padron_cui';
                    
        EXECUTE query_sql;
        
        -- Crear índice en la VM para mejor performance
        EXECUTE 'CREATE INDEX idx_' || vm_name || '_cui ON ' || vm_name || '(cui)';
        
        -- Verificar que tiene datos
        EXECUTE 'SELECT COUNT(*) FROM ' || vm_name INTO registro_count;
        
        IF registro_count = 0 THEN
            -- Si no tiene datos, eliminar la VM y reportar error
            EXECUTE 'DROP MATERIALIZED VIEW ' || vm_name;
            error_msg := 'La vista materializada ' || vm_name || ' se creó vacía';
            
            INSERT INTO error_log (error_message, contexto) 
            VALUES (error_msg, 'crear_snapshot_trimestral - VM sin datos');
            
            RETURN 'ERROR: ' || error_msg;
        END IF;
        
        -- Insertar en querys
        INSERT INTO querys (nombre, sql_firma, tipo) 
        VALUES (
            'Padrón CUI ' || TO_CHAR(CURRENT_DATE, 'DD/MM/YYYY'),
            'SELECT * FROM ' || vm_name,
            'snapshot'
        ) RETURNING id INTO new_query_id;
        
        -- Insertar en catálogo
        INSERT INTO catalogo (titulo, descripcion, id_query, estado)
        VALUES (
            'Padrón CUI ' || TO_CHAR(CURRENT_DATE, 'DD/MM/YYYY'),
            'Snapshot trimestral del padrón al ' || TO_CHAR(CURRENT_DATE, 'DD/MM/YYYY') || ' (' || registro_count || ' registros)',
            new_query_id,
            'activo'
        ) RETURNING id INTO new_catalogo_id;
        
        -- Insertar acciones disponibles (CSV, XLSX, GeoJSON)
        INSERT INTO catalogo_acciones (id_catalogo, accion, formato, orden) VALUES
        (new_catalogo_id, 'descargar', (SELECT id FROM formatos WHERE nombre = 'csv'), 1),
        (new_catalogo_id, 'descargar', (SELECT id FROM formatos WHERE nombre = 'xlsx'), 2),
        (new_catalogo_id, 'descargar', (SELECT id FROM formatos WHERE nombre = 'geojson'), 3);
        
        -- Verificar si tenemos más de 4 snapshots activos
        IF count_active_snapshots() > 4 THEN
            -- Marcar el más antiguo como inactivo
            UPDATE catalogo SET estado = 'inactivo'
            WHERE id_query IN (
                SELECT q.id FROM querys q
                JOIN catalogo c ON c.id_query = q.id
                WHERE q.tipo = 'snapshot' AND c.estado = 'activo'
                ORDER BY c.fecha_creacion ASC
                LIMIT 1
            );
            
            -- Opcional: eliminar físicamente la VM más antigua
            -- oldest_vm := get_oldest_vm();
            -- IF oldest_vm IS NOT NULL AND vm_exists(oldest_vm) THEN
            --     EXECUTE 'DROP MATERIALIZED VIEW ' || oldest_vm;
            -- END IF;
        END IF;
        
        RETURN 'SUCCESS: Snapshot ' || vm_name || ' creado exitosamente (' || registro_count || ' registros)';
        
    EXCEPTION WHEN OTHERS THEN
        error_msg := 'Error creando snapshot: ' || SQLERRM;
        
        -- Limpiar VM si se creó parcialmente
        IF vm_exists(vm_name) THEN
            EXECUTE 'DROP MATERIALIZED VIEW ' || vm_name;
        END IF;
        
        -- Loguear error
        INSERT INTO error_log (error_message, contexto) 
        VALUES (error_msg, 'crear_snapshot_trimestral - ' || vm_name);
        
        RETURN 'ERROR: ' || error_msg;
    END;
END;
$$ LANGUAGE plpgsql;

-- =====================================================
-- 4. AUTOMATIZACIÓN CON PG_CRON
-- =====================================================

-- Habilitar extensión pg_cron (ejecutar como superuser)
-- CREATE EXTENSION IF NOT EXISTS pg_cron;

-- Programar ejecución trimestral (1ro de cada trimestre a las 2:00 AM)
-- Enero 1, Abril 1, Julio 1, Octubre 1
/*
SELECT cron.schedule(
    'snapshot-trimestral',
    '0 2 1 1,4,7,10 *',  -- minuto hora día mes díadelasemana
    'SELECT crear_snapshot_trimestral();'
);
*/

-- Para testing, programar ejecución diaria a las 3:00 AM (comentar cuando esté en producción)
/*
SELECT cron.schedule(
    'snapshot-testing',
    '0 3 * * *',  -- Todos los días a las 3:00 AM
    'SELECT crear_snapshot_trimestral();'
);
*/

-- Ver jobs programados:
-- SELECT * FROM cron.job;

-- Eliminar un job:
-- SELECT cron.unschedule('snapshot-trimestral');

-- =====================================================
-- 5. FUNCIONES PARA CONSULTAS DEL BACKEND
-- =====================================================

-- Función para obtener el catálogo completo (endpoint /catalogo/listar)
CREATE OR REPLACE FUNCTION get_catalogo_completo()
RETURNS JSON AS $$
DECLARE
    resultado JSON;
BEGIN
    SELECT JSON_AGG(
        JSON_BUILD_OBJECT(
            'id', c.id,
            'titulo', c.titulo,
            'descripcion', c.descripcion,
            'fecha_creacion', TO_CHAR(c.fecha_creacion, 'YYYY-MM-DD HH24:MI:SS'),
            'estado', c.estado,
            'acciones', (
                SELECT JSON_AGG(
                    JSON_BUILD_OBJECT(
                        'accion', ca.accion,
                        'formato', f.nombre,
                        'orden', ca.orden,
                        'uri_archivo', ca.uri_archivo
                    ) ORDER BY ca.orden
                )
                FROM catalogo_acciones ca
                JOIN formatos f ON f.id = ca.formato
                WHERE ca.id_catalogo = c.id
            )
        ) ORDER BY c.fecha_creacion DESC
    )
    INTO resultado
    FROM catalogo c
    JOIN querys q ON q.id = c.id_query
    WHERE c.estado = 'activo';
    
    RETURN COALESCE(resultado, '[]'::JSON);
END;
$$ LANGUAGE plpgsql;

-- Función para validar acción disponible
CREATE OR REPLACE FUNCTION validar_accion(
    p_id_catalogo INTEGER,
    p_accion VARCHAR(50),
    p_formato VARCHAR(50)
)
RETURNS TABLE(
    es_valida BOOLEAN,
    sql_query TEXT,
    uri_archivo TEXT,
    content_type TEXT,
    extension TEXT
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        CASE WHEN ca.id IS NOT NULL THEN TRUE ELSE FALSE END as es_valida,
        q.sql_firma as sql_query,
        ca.uri_archivo,
        f.content_type,
        f.extension
    FROM catalogo c
    LEFT JOIN catalogo_acciones ca ON ca.id_catalogo = c.id 
        AND ca.accion = p_accion
    LEFT JOIN formatos f ON f.id = ca.formato AND f.nombre = p_formato
    LEFT JOIN querys q ON q.id = c.id_query
    WHERE c.id = p_id_catalogo
        AND c.estado = 'activo'
    LIMIT 1;
END;
$$ LANGUAGE plpgsql;

-- =====================================================
-- 6. DATOS DE PRUEBA Y TESTING
-- =====================================================

-- Crear un snapshot de prueba manualmente
-- SELECT crear_snapshot_trimestral();

-- Ver el catálogo
-- SELECT get_catalogo_completo();

-- Validar una acción
-- SELECT * FROM validar_accion(1, 'descargar', 'csv');

-- Ver snapshots activos
-- SELECT c.titulo, c.fecha_creacion, c.estado 
-- FROM catalogo c 
-- JOIN querys q ON q.id = c.id_query 
-- WHERE q.tipo = 'snapshot' 
-- ORDER BY c.fecha_creacion DESC;

-- Ver logs de errores
-- SELECT * FROM error_log ORDER BY timestamp DESC LIMIT 10;

-- =====================================================
-- NOTAS DE IMPLEMENTACIÓN:
-- =====================================================
-- 1. Ejecutar como superuser para crear la extensión pg_cron
-- 2. Ajustar los horarios del cron según necesidades
-- 3. La función crear_snapshot_trimestral() maneja todos los errores
-- 4. Los archivos pregenerados van en uri_archivo de catalogo_acciones
-- 5. El backend PHP usa get_catalogo_completo() y validar_accion()
-- 6. Para testing, crear snapshots manualmente con SELECT crear_snapshot_trimestral();