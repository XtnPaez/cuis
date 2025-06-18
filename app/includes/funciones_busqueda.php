<?php
function buscarCUI($pdo, $cui) {
  $sql = "SELECT 
            e.id,
            e.cui,
            e.estado,
            e.sector,
            CASE WHEN e.institucion is null THEN 'Sin Institución Asociada' ELSE e.institucion END as institucion,
            CASE WHEN e.gestionado = true THEN 'Gestionado' ELSE 'No Gestionado' END as gestionado,
            e.x_gkba,
            e.y_gkba,
            e.x_wgs84,
            e.y_wgs84,
            CASE WHEN pre.cup is null THEN 'Sin Predio' ELSE pre.cup END as codpre,
            CASE WHEN pre.nombre is null THEN 'Sin Predio' ELSE pre.nombre END as predio,
            d.calle,
            d.altura,
            ua.comuna,
            ua.barrio,
            ua.comisaria,
            ua.area_hospitalaria,
            ua.region_sanitaria,
            ua.distrito_escolar,
            ua.comisaria_vecinal,
            ua.codigo_postal,
            ua.codigo_postal_argentino,
            rel.operativo_1 as op1,
            rel.operativo_2 as op2
          FROM cuis.edificios e
          LEFT JOIN cuis.predios pre ON e.predio_id = pre.id
          JOIN cuis.edificios_direcciones ed ON e.id = ed.edificio_id
          JOIN cuis.direcciones d ON ed.direccion_id = d.id
          LEFT JOIN cuis.v_edificios_relevamientos rel ON rel.id_edificio = e.id
          LEFT JOIN cuis.ubicacion_administrativa ua ON d.ubicacion_administrativa_id = ua.id
          WHERE e.cui = :cui";
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(':cui', $cui, PDO::PARAM_INT);
  $stmt->execute();
  return $stmt->fetch();
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
?>