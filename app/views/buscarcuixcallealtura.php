<?php
session_start();
require_once('../config/config.php');
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Buscar por Calle y Altura</title>
  <link href="../css/bootstrap.min.css" rel="stylesheet">
  <link href="../css/sticky.css" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body class="d-flex flex-column min-vh-100">
  <?php include('../includes/navbar.php'); ?>
  <main class="flex-grow-1 container py-5 mt-5">
    <h3>Buscar dirección</h3>
    <form method="GET" class="row g-3 mb-4">
      <div class="col-md-6">
        <label for="calle" class="form-label">Calle</label>
        <input type="text" name="calle" id="calle" class="form-control" list="sugerencias" autocomplete="off" required>
        <datalist id="sugerencias"></datalist>

      </div>
      <div class="col-md-6">
        <label for="altura" class="form-label">Altura</label>
        <input type="number" name="altura" id="altura" class="form-control" required>
      </div>
      <div class="col-12">
        <button type="submit" class="btn btn-primary">Buscar</button>
      </div>
    </form>

    <div id="alertas"></div>
<div id="map" style="height: 400px;"></div>


<?php
if (isset($_GET['calle'], $_GET['altura'])) {
  $calle = $_GET['calle'];
  $altura = (int)$_GET['altura'];

  // Búsqueda exacta
  $sql = "SELECT calle, altura, x_wgs84, y_wgs84
          FROM cuis.v_direcciones_coordenadas
          WHERE calle ILIKE :calle AND altura = :altura
          LIMIT 1";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([':calle' => $calle, ':altura' => $altura]);
  $punto = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($punto) {
    echo "<script>
  document.getElementById('alertas').innerHTML = `<div class=\"alert alert-success mt-3\">Dirección exacta encontrada: {$punto['calle']} {$punto['altura']}</div>`;
</script>";

    echo "<script>
      let map = L.map('map').setView([{$punto['y_wgs84']}, {$punto['x_wgs84']}], 17);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
      L.marker([{$punto['y_wgs84']}, {$punto['x_wgs84']}]).addTo(map)
        .bindPopup('{$punto['calle']} {$punto['altura']}')
        .openPopup();
    </script>";
  } else {
    // Si no hay coincidencia exacta, buscamos en un radio de 100 metros
    $sql2 = "
      SELECT calle, altura, x_wgs84, y_wgs84
      FROM cuis.v_direcciones_coordenadas
      WHERE calle ILIKE :calle
        AND ST_DWithin(
              ST_SetSRID(ST_MakePoint(x_wgs84, y_wgs84), 4326)::geometry,
              ST_SetSRID(ST_MakePoint(:x, :y), 4326)::geometry,
              100
            )";
    // Aproximamos la coordenada estimada (esto es solo para buscar alrededor de esa altura)
    // En una app real deberías tener una referencia real de coordenadas por altura.
    $coord_estimada = $pdo->prepare("SELECT x_wgs84, y_wgs84 FROM cuis.v_direcciones_coordenadas WHERE calle ILIKE :calle AND altura BETWEEN :a1 AND :a2 LIMIT 1");
    $coord_estimada->execute([
      ':calle' => $calle,
      ':a1' => $altura - 10,
      ':a2' => $altura + 10,
    ]);
    $ref = $coord_estimada->fetch(PDO::FETCH_ASSOC);

    if ($ref) {
      $stmt2 = $pdo->prepare($sql2);
      $stmt2->execute([
        ':calle' => $calle,
        ':x' => $ref['x_wgs84'],
        ':y' => $ref['y_wgs84']
      ]);
      $cercanos = $stmt2->fetchAll(PDO::FETCH_ASSOC);

      if (count($cercanos) > 0) {
        echo "<script>
  document.getElementById('alertas').innerHTML = `<div class=\"alert alert-success mt-3\">No se encontró esa altura exacta. Mostrando puntos cercanos en 100 metros</div>`;
</script>";
        echo "<script>
          let map = L.map('map').setView([{$ref['y_wgs84']}, {$ref['x_wgs84']}], 17);
          L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);";
        foreach ($cercanos as $p) {
          echo "L.marker([{$p['y_wgs84']}, {$p['x_wgs84']}]).addTo(map)
                  .bindPopup('{$p['calle']} {$p['altura']}');";
        }
        echo "</script>";
      } else {
        echo "<script>
  document.getElementById('alertas').innerHTML = `<div class=\"alert alert-success mt-3\">No se encontró esa altura exacta. Mostrando puntos cercanos en 100 metros</div>`;
</script>";
      }
    } else {
      echo "<script>
  document.getElementById('alertas').innerHTML = `<div class=\"alert alert-success mt-3\">No se encontraron datos para ese par Calle y Altura</div>`;
</script>";
    }
  }
}
?>
<!-- Div para comentarios y observaciones -->
      <div class="mt-3 p-3 border border-warning rounded bg-light">
        <h6 class="text-warning">Pendientes:</h6>
        <ul class="mb-0">
          <li>Falta poner CUI en la vista para poder crear el link que muestre info</li>
          </ul>
      </div><!-- termina pendientes -->
  </main>
  <?php include('../includes/footer.php'); ?>
  
  <script>
document.getElementById('calle').addEventListener('input', function () {
  const calleInput = this.value;

  if (calleInput.length < 2) return;

  fetch(`autocomplete_calle.php?term=${encodeURIComponent(calleInput)}`)
    .then(response => response.json())
    .then(data => {
      const dataList = document.getElementById('sugerencias');
      dataList.innerHTML = '';
      data.forEach(calle => {
        const option = document.createElement('option');
        option.value = calle;
        dataList.appendChild(option);
      });
    });
});
</script>
<script>
  // Limpiar alertas y mapa antes de cada búsqueda
  document.querySelector('form').addEventListener('submit', function () {
    document.getElementById('alertas').innerHTML = '';
    document.getElementById('map').innerHTML = '';
  });
</script>
<script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
