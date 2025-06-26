# DE SHAPE A PG - UN VIAJE DE IDA 

Se sube via qgis el shape de edificios a sig.cuis.public con el nombre edificios_temp

TIP: agregar newid para que no pinche por id duplicado

### vamos a chequear que haya cuis unicos

    select cui from public.edificios_temp group by cui having count(cui) >1

### si hay cuis repetidos, miramos los casos y eliminamos lo que no corresponda; en mi ejemplo dieron repetidos 201710 y 201418

    select * from public.edificios_temp where cui = 201710;
    
### lo chequeo contra padron nacion y por dire3ccion y anexo, debe quedar el nivmodcui = PriCom

    delete from public.edificios_temp where newid = 1766;
    select * from public.edificios_temp where cui = 201418;

### lo chequeo contra padron nacion y por direccion y anexo, debe quedar el nivmodcui = IniCom-PriCom

    delete from public.edificios_temp where newid = 1488;

### ahora vamos a completar la tabla predios

chequeamos que no haya nulls en cup y dato en nombre o viceversa; correjimos 

    select * from public.edificios_temp where nomb_predi is not null and cup is null;
    update public.edificios_temp set cup = '017' where newid = 2746;

### y hacemos update de predios

    truncate cuis.predios cascade;
    insert into cuis.predios(cup, nombre) 
    select cup, trim(upper(nomb_predi)) from public.edificios_temp where cup is not null or nomb_predi is not null group by cup, nomb_predi order by cup asc;

### chequeamos estado

    select distinct(estado) from public.edificios_temp group by estado

### chequeamos sector; a los 1-2 le ponemos "otro"

    select distinct(sectorcue) from public.edificios_temp group by sectorcue

### chequeamos gestionado; hay un hull; para avanzar con el desarrollo lo pongo en 'si'; con la capa definitiva esto no puede pasar

    select distinct(gestionado) from public.edificios_temp group by gestionado

### vamos a poner institucion tal como esta en el shape

### y a a esta altura ya estamos como para completar la tabla cuis.edificios

    insert into cuis.edificios	(cui, estado, sector, predio_id, x_gkba, y_gkba, gestionado, institucion, fecha_creacion, geom, ffrr_2022)
    select 
    	edi.cui, 
    	edi.estado AS estado, 
    	CASE
    		WHEN edi.sectorcue = '1' THEN 'publico'
    		WHEN edi.sectorcue = '2' THEN 'privado'
    		ELSE 'otro'
    	END as sector,
    	pre.id as predio_id, 
    	edi.point_x as x, 
    	edi.point_y as y,
    	CASE 
    		WHEN edi.gestionado = 'si' THEN true
    		ELSE false
    	END as gestionado,
    	TRIM(UPPER(edi.nomb_insti)) as institucion,
    	now() as fecha_creacion,
    	ST_SetSRID(ST_MakePoint(edi.point_x, edi.point_y), 9498) as geom,
    	fr.cod_indec
    from public.edificios_temp edi 
    left join cuis.predios pre on pre.cup = edi.cup
    left join public.cui_ffrr2022 fr on fr.cui = edi.cui::text 
    order by edi.cui asc

### vamos a completar la tabla de operativos

    insert into cuis.operativos (nombre) values ('CENIE 2010'),('CIE 2017')

### vamos a completar la tabla relevamientos; 

    insert into cuis.relevamientos (edificio_id, operativo_id, relevado)
    select cui.id as edificio_id, 1 as operativo_id, true as relevado
    from public.edificios_temp edi
    left join cuis.edificios cui on edi.cui::text = cui.cui
    where edi.cenie2010 = 'si'
    union
    select cui.id as edificio_id, 2 as operativo_id, true as relevado
    from public.edificios_temp edi
    left join cuis.edificios cui on edi.cui::text = cui.cui
    where edi.cie2017 = 'si'
    order by edificio_id asc

# vamos por las direcciones

vamos a masajear un excel con las direcciones del shape, de los dos campos en los que tenemos datos para generar un listado unico

le vamos a poner un campo boolean (geo) para distinguir las que tienen calle altura de las que no, para luego, darles otro tratamiento

vamos a crear una tabla temporal para alojar todos los datos de las API que luego vamos a volcar a las tablas definitivas

el script esta en GEOCABA

### excel para descargar y masajear

    select newid, cui, dir_calle, dir_numero, dir_calle2, dir_num2 from public.edificios_temp order by cui asc

### tabla para recolectar el masajeo y las API

    create table public.dirtemp (
    	cui int, --listo
    	calle_m text, --listo
    	altura_m int, --listo
    	geo boolean, --listo
    	-- para la tabla puertas
    	puerta_principal boolean, --listo
    	-- para la tabla direcciones
    	codigo_calle int, --listo
    	calle text, --listo
    	altura int, --listo
    	-- para la tabla ubicacion_administrativa
    	comuna text, --listo
    	barrio text, --listo
    	comisaria text, --listo
    	area_hospitalaria text, --listo
    	region_sanitaria text, --listo
    	distrito_escolar text, --listo
    	comisaria_vecinal text, --listo
    	seccion_catastral text, --listo
    	codigo_postal text, --listo
    	codigo_postal_argentino text, --listo
    	-- para la tabla parcelas; ver que la api trae cantidad de puertas, capaz...
    	smp text, 
    	seccion text,
    	manzana text,
    	parcela text,
    	superficie_total int,
    	superficie_cubierta int,
    	frente int,
    	fondo int,
    	propiedad_horizontal text,
    	pisos_bajo_rasante int,
    	pisos_sobre_rasante int,
    	unidades_funcionales int,
    	locales int,
    	-- para la tabla coordenadas
    	x_gkba numeric(20,10),
    	y_gkba numeric(20,10),
    	x_wgs84 numeric(10,6),
    	y_wgs84 numeric(10,6),
    	id serial,
    	api text
    )

-- ---------------------------------------------------------------------------------------------------------------------------------------
-- hay que pensar como vamos a cargar los registros que la API no resuelve como direccion; para los fines de prueba, no los vamos a cargar
-- ---------------------------------------------------------------------------------------------------------------------------------------

### Creamos una funcion que limpie lo vinculado a direcciones

    CREATE OR REPLACE FUNCTION cuis.limpiar_direcciones()
    RETURNS void AS $$
    BEGIN
      -- Iniciamos una transacción controlada
      BEGIN
        DELETE FROM cuis.edificios_direcciones;
        DELETE FROM cuis.direcciones;
        DELETE FROM cuis.coordenadas;
        DELETE FROM cuis.parcelas;
        DELETE FROM cuis.ubicacion_administrativa;
        RAISE NOTICE 'Tablas de direcciones y relacionadas limpiadas exitosamente.';
      EXCEPTION
        WHEN OTHERS THEN
          RAISE WARNING 'Ocurrió un error al limpiar las tablas: %', SQLERRM;
          ROLLBACK;
          RETURN;
      END;
    END;
    $$ LANGUAGE plpgsql;

### vamos a tener a mano estas herramientas

###funcion que limpia la transaccion

    ROLLBACK; 

### funcion que limpia las tablas vinculadas a direcciones

    SELECT cuis.limpiar_direcciones();

### vamos a hacer update de las tablas cuis

    DO
    $$
    BEGIN
      -- Transacción segura para desarrollo
      BEGIN
        -- Filtro de datos válidos
        WITH datos_filtrados AS (
          SELECT *
          FROM public.dirtemp
          WHERE codigo_calle IS NOT NULL
        ),
        -- Coordenadas
        ins_coordenadas AS (
          INSERT INTO cuis.coordenadas (x_gkba, y_gkba, x_wgs84, y_wgs84, geom_gkba, geom_wgs84)
          SELECT DISTINCT 
            x_gkba, y_gkba, x_wgs84, y_wgs84,
            ST_SetSRID(ST_MakePoint(x_gkba, y_gkba), 9498),
            ST_SetSRID(ST_MakePoint(x_wgs84, y_wgs84), 4326)
          FROM datos_filtrados
          WHERE x_gkba IS NOT NULL AND y_gkba IS NOT NULL
          RETURNING id, x_gkba, y_gkba
        ),
        -- Ubicación administrativa
        ins_ubicacion AS (
          INSERT INTO cuis.ubicacion_administrativa (
            comuna, barrio, comisaria, area_hospitalaria, region_sanitaria,
            distrito_escolar, comisaria_vecinal, seccion_catastral,
            codigo_postal, codigo_postal_argentino
          )
          SELECT DISTINCT 
            comuna, barrio, comisaria, area_hospitalaria, region_sanitaria,
            distrito_escolar, comisaria_vecinal, seccion_catastral,
            codigo_postal, codigo_postal_argentino
          FROM datos_filtrados
          RETURNING id, comuna, barrio
        ),
        -- Parcelas
        ins_parcelas AS (
          INSERT INTO cuis.parcelas (
            smp, seccion, manzana, parcela, superficie_total,
            superficie_cubierta, frente, fondo,
            propiedad_horizontal, pisos_bajo_rasante,
            pisos_sobre_rasante, unidades_funcionales, locales
          )
          SELECT DISTINCT 
            smp, seccion, manzana, parcela, superficie_total,
            superficie_cubierta, frente, fondo,
            propiedad_horizontal = 'SI',
            pisos_bajo_rasante, pisos_sobre_rasante,
            unidades_funcionales, locales
          FROM datos_filtrados
          RETURNING id, smp
        ),
        -- Direcciones
        ins_direcciones AS (
          INSERT INTO cuis.direcciones (
            codigo_calle, calle, altura, ubicacion_administrativa_id,
            parcela_id, coordenadas_id
          )
          SELECT 
            d.codigo_calle::text, d.calle, d.altura::text,
            ua.id, p.id, c.id
          FROM datos_filtrados d
          LEFT JOIN ins_ubicacion ua
            ON d.comuna = ua.comuna AND d.barrio = ua.barrio
          LEFT JOIN ins_parcelas p
            ON d.smp = p.smp
          LEFT JOIN ins_coordenadas c
            ON d.x_gkba = c.x_gkba AND d.y_gkba = c.y_gkba
          RETURNING id
        )
        -- Relación edificio-dirección
        INSERT INTO cuis.edificios_direcciones (edificio_id, direccion_id, es_principal)
        SELECT 
          e.id, d.id, TRUE
        FROM cuis.direcciones d
        JOIN public.dirtemp t ON d.codigo_calle = t.codigo_calle::text AND d.altura = t.altura::text
        JOIN cuis.edificios e ON e.cui::int = t.cui;
    
      EXCEPTION WHEN OTHERS THEN
        RAISE NOTICE 'Ocurrió un error: %', SQLERRM;
        ROLLBACK;
        RETURN;
      END;
      COMMIT;
    END;
    $$;

### Y vamos a corregir lo que no anda con este insert

    INSERT INTO cuis.edificios_direcciones (edificio_id, direccion_id, es_principal)
    SELECT DISTINCT ON (e.id) 
      e.id, d.id, TRUE
    FROM public.dirtemp t
    JOIN cuis.edificios e 
      ON e.cui = t.cui::text
    JOIN cuis.direcciones d 
      ON d.codigo_calle = t.codigo_calle::text
         AND regexp_replace(d.altura, '[^0-9]', '', 'g') = t.altura::text
    WHERE t.cui IS NOT NULL
    ORDER BY e.id, d.id;

### aca la coleccion de selects para comprobar que las tablas se completaron

    select * from cuis.coordenadas;
    select * from cuis.direcciones;
    select * from cuis.edificios_direcciones;
    select * from cuis.parcelas;
    select * from cuis.ubicacion_administrativa;
