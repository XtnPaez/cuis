<?php
function buscarCUI($pdo, $cui) {
  $sql = "SELECT 
            edi.cui, UPPER(edi.estado) as estado, UPPER(edi.sector) as sector, 
              CASE WHEN edi.institucion is null THEN 'Sin Institución Asociada' ELSE edi.institucion END as institucion,
              CASE WHEN edi.gestionado = true THEN 'Gestionado' ELSE 'No Gestionado' END as gestionado, edi.x_gkba, edi.y_gkba, edi.ffrr_2022,
              dir.codigo_calle, dir.calle, dir.altura, 
            CASE WHEN pre.cup is null THEN 'Sin Predio' ELSE pre.cup END as codpre, 
            CASE WHEN pre.nombre is null THEN 'Sin Predio' ELSE pre.nombre END as predio,
            edr.operativo_1, edr.operativo_2,
            par.smp, par.superficie_total, par.superficie_cubierta, par.frente, par.fondo, par.propiedad_horizontal, par.pisos_bajo_rasante, 
              par.pisos_sobre_rasante, 
            uba.comuna, uba.barrio, uba.comisaria, uba.area_hospitalaria, uba.region_sanitaria, uba.distrito_escolar, uba.comisaria_vecinal, 
              uba.seccion_catastral, uba.codigo_postal, uba.codigo_postal_argentino,
            coo.x_wgs84, coo.y_wgs84
          FROM cuis.edificios edi
          left join cuis.edificios_direcciones edd on edi.id = edd.edificio_id
          left join cuis.predios pre on edi.predio_id = pre.id
          left join cuis.v_edificios_relevamientos edr on edi.cui = edr.cui
          left join cuis.direcciones dir on edd.direccion_id = dir.id
          left join cuis.parcelas par on dir.parcela_id = par.id
          left join cuis.ubicacion_administrativa uba on dir.ubicacion_administrativa_id = uba.id
          left join cuis.coordenadas coo on dir.coordenadas_id = coo.id
          WHERE edi.cui = :cui";
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(':cui', $cui, PDO::PARAM_INT);
  $stmt->execute();
  return $stmt->fetch();
}

function buscarDireccionesPorCUI($pdo, $cui) {
  $sql2 = "SELECT 
            d.calle, d.altura, udi.codigo_postal, par.smp
            from cuis.edificios edi
            join cuis.edificios_direcciones edd on edi.id = edd.edificio_id
            join cuis.direcciones d on edd.direccion_id = d.id
            join cuis.ubicacion_administrativa udi on d.ubicacion_administrativa_id = udi.id
            join cuis.parcelas par on d.parcela_id = par.id
            WHERE edi.cui = :cui";
  $stmt2 = $pdo->prepare($sql2);
  $stmt2->bindParam(':cui', $cui, PDO::PARAM_INT);
  $stmt2->execute();
  return $stmt2->fetchAll();
}

function buscarCueAnexos($pdo, $cui) {
  $sql = "SELECT 
            dom.cui,
            est.cue,
            loc.anexo,
            est.nombre,
            loc.codigo_jurisdiccional,
            loc.telefono,
            initcap(res.apellido) as apellidor,
            initcap(res.nombre) as nombrer,
            lower(res.email) as email
          FROM padronnacion_fdw.domicilio dom
          JOIN padronnacion_fdw.localizacion_domicilio ldo ON dom.id_domicilio = ldo.id_domicilio
          JOIN padronnacion_fdw.localizacion loc ON loc.id_localizacion = ldo.id_localizacion	
          JOIN padronnacion_fdw.establecimiento est ON est.id_establecimiento = loc.id_establecimiento
          JOIN padronnacion_fdw.responsable res ON res.id_responsable = est.id_responsable
          WHERE TRIM(dom.cui) = :cui_str";
  $stmt = $pdo->prepare($sql);
  $cui_str = str_pad(trim($cui), 7, "0", STR_PAD_LEFT);
  $stmt->bindParam(':cui_str', $cui_str, PDO::PARAM_STR);
  $stmt->execute();
  return $stmt->fetchAll();
}

function buscarReniePorCUI($pdo, $cui) {
  $sql3 = "SELECT 
            e.cui,
            -- Conteo de construcciones activas
            (SELECT COUNT(*) 
            FROM mapa_fdw.construcciones_madre c 
            WHERE c.cui = e.cui AND c.borrado = 0) AS construcciones_validas,
            -- Conteo de áreas exteriores activas
            (SELECT COUNT(*) 
            FROM mapa_fdw.areas_exteriores_madre a 
            WHERE a.cui = e.cui AND a.borrado = 0) AS areas_exteriores_validas,
            -- Conteo de locales 
            (SELECT COUNT(*) 
            FROM mapa_fdw.locales_madre l 
            WHERE l.cui = e.cui AND l.borrado = 0) AS cantidad_locales,
            -- Conteo de escaleras 
            (SELECT COUNT(*) 
            FROM mapa_fdw.escaleras_madre es 
            WHERE es.cui = e.cui AND es.borrado = 0) AS cantidad_escaleras,
            -- Conteo de tableros
            (SELECT COUNT(*) 
            FROM mapa_fdw.tableros_madre t 
            WHERE t.cui = e.cui) AS cantidad_tableros
            FROM mapa_fdw.edificios_madre e
            WHERE e.cui = :cui";
  $stmt3 = $pdo->prepare($sql3);
  $stmt3->bindParam(':cui', $cui, PDO::PARAM_INT);
  $stmt3->execute();
  return $stmt3->fetch();
}








?>