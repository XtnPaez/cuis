-- 1. Ejecutar el SQL completo (como superuser)
-- 2. Habilitar pg_cron:
CREATE EXTENSION

-- 2. Habilitar pg_cron:
CREATE EXTENSION IF NOT EXISTS pg_cron;

-- 3. Programar el job trimestral:
SELECT cron.schedule(
   'snapshot-trimestral',
   '0 2 1 1,4,7,10 *',  -- 1ro de Ene, Abr, Jul, Oct a las 2:00 AM
   'SELECT crear_snapshot_trimestral();'
);

-- 4. Para testing, crear snapshot manual:
SELECT crear_snapshot_trimestral();

-- 5. Ver el resultado:
SELECT get_catalogo_completo();