<?php
require_once('../config/config.php'); // o tu conexión PDO

if (isset($_GET['cui'])) {
  $cui = $_GET['cui'];

  $sql = "
    SELECT 
      e.id, e.cui, e.estado, e.sector,
      COALESCE(e.institucion, 'Sin Institución Asociada') as institucion,
      CASE WHEN e.gestionado = true THEN 'Gestionado' ELSE 'No Gestionado' END as gestionado,
      e.x_wgs84, e.y_wgs84,
      COALESCE(pre.cup, 'Sin Predio') as codpre,
      COALESCE(pre.nombre, 'Sin Predio') as predio,
      d.calle, d.altura,
      ua.comuna, ua.barrio, ua.comisaria,
      ua.area_hospitalaria, ua.region_sanitaria,
      ua.distrito_escolar, ua.comisaria_vecinal,
      ua.codigo_postal, ua.codigo_postal_argentino,
      rel.operativo_1 as op1, rel.operativo_2 as op2
    FROM cuis.edificios e
    LEFT JOIN cuis.predios pre ON e.predio_id = pre.id
    JOIN cuis.edificios_direcciones ed ON e.id = ed.edificio_id
    JOIN cuis.direcciones d ON ed.direccion_id = d.id
    LEFT JOIN cuis.v_edificios_relevamientos rel ON rel.id_edificio = e.id
    LEFT JOIN cuis.ubicacion_administrativa ua ON d.ubicacion_administrativa_id = ua.id
    WHERE e.cui = :cui
    LIMIT 1";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([':cui' => $cui]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($row) {
    echo "<div class='card'>";
    echo "<div class='card-body'>";
    echo "<h5 class='card-title'>Detalle del CUI {$row['cui']}</h5>";
    echo "<p><strong>Estado:</strong> {$row['estado']}</p>";
    echo "<p><strong>Sector:</strong> {$row['sector']}</p>";
    echo "<p><strong>Institución:</strong> {$row['institucion']}</p>";
    echo "<p><strong>Predio:</strong> {$row['codpre']} - {$row['predio']}</p>";
    echo "<p><strong>Dirección:</strong> {$row['calle']} {$row['altura']}</p>";
    echo "<p><strong>Comuna:</strong> {$row['comuna']} | <strong>Barrio:</strong> {$row['barrio']}</p>";
    echo "<p><strong>Operativos:</strong> " . ($row['op1'] ?? '-') . " / " . ($row['op2'] ?? '-') . "</p>";
    echo "</div></div>";
  } else {
    echo "<div class='alert alert-warning'>No se encontraron datos para el CUI solicitado.</div>";
  }
}
?>
