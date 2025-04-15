<?php 
  session_start();
  require_once('../config/config.php'); 

  $resultado = null;
  $error = null;

  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cui'])) {
    $cui = $_POST['cui'];

    $sql = "SELECT 
              e.cui,
              e.estado,
              e.sector,
              CASE WHEN e.gestionado = true THEN 'Gestionado' ELSE 'No Gestionado' END as gestionado,
              e.x_wgs84,
              e.y_wgs84,
              d.calle,
              d.altura,
              ua.comuna,
              ua.barrio,
              ua.comisaria,
              ua.area_hospitalaria,
              ua.region_sanitaria,
              ua.codigo_postal
            FROM cuis.edificios e
            JOIN cuis.edificios_direcciones ed ON e.id = ed.edificio_id
            JOIN cuis.direcciones d ON ed.direccion_id = d.id
            LEFT JOIN cuis.ubicacion_administrativa ua ON d.ubicacion_administrativa_id = ua.id
            WHERE e.cui = :cui";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':cui', $cui, PDO::PARAM_INT);
    $stmt->execute();
    $resultado = $stmt->fetch();

    if (!$resultado) {
      $error = "No se encontró ningún edificio con el CUI ingresado.";
    }
  }
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Buscar CUI</title>
  <link href="../css/bootstrap.min.css" rel="stylesheet">
  <link href="../css/sticky.css" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <link rel="apple-touch-icon" href="../images/apple-icon-180x180.png" sizes="180x180">
  <link rel="icon" href="../images/favicon-32x32.png" sizes="32x32" type="image/png">
</head>
<body class="d-flex flex-column min-vh-100">
  <?php include('../includes/navbar.php'); ?>
  <main class="flex-grow-1 container py-5">
    <h2 class="text-center mb-5 mt-4">Buscar CUI</h2>

    <form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="mb-4">
      <div class="input-group">
        <input type="number" name="cui" class="form-control" placeholder="Ingresá el CUI" required>
        <button class="btn btn-primary" type="submit">Buscar</button>
      </div>
    </form>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= $error ?></div>
    <?php elseif ($resultado): ?>
      <div class="row">
        <!-- Datos del edificio -->
        <div class="col-md-6 mb-4">
          <div class="card shadow-sm">
            <div class="card-body">
              <h5 class="card-title">Datos del Edificio</h5>
              <ul class="list-group list-group-flush">
                <li class="list-group-item"><strong>CUI:</strong> <?= htmlspecialchars($resultado['cui']) ?></li>
                <li class="list-group-item"><strong>Estado:</strong> <?= htmlspecialchars($resultado['estado']) ?></li>
                <li class="list-group-item"><strong>Sector:</strong> <?= htmlspecialchars($resultado['sector']) ?></li>
                <li class="list-group-item"><strong>Gestionado:</strong> <?= htmlspecialchars($resultado['gestionado']) ?></li>
                <li class="list-group-item"><strong>Calle:</strong> <?= htmlspecialchars($resultado['calle']) ?></li>
                <li class="list-group-item"><strong>Altura:</strong> <?= htmlspecialchars($resultado['altura']) ?></li>
                <li class="list-group-item"><strong>Comuna:</strong> <?= htmlspecialchars($resultado['comuna']) ?></li>
                <li class="list-group-item"><strong>Barrio:</strong> <?= htmlspecialchars($resultado['barrio']) ?></li>
                <li class="list-group-item"><strong>Comisaría:</strong> <?= htmlspecialchars($resultado['comisaria']) ?></li>
                <li class="list-group-item"><strong>Área Hospitalaria:</strong> <?= htmlspecialchars($resultado['area_hospitalaria']) ?></li>
                <li class="list-group-item"><strong>Región Sanitaria:</strong> <?= htmlspecialchars($resultado['region_sanitaria']) ?></li>
                <li class="list-group-item"><strong>Código Postal:</strong> <?= htmlspecialchars($resultado['codigo_postal']) ?></li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Mapa -->
        <div class="col-md-6 mb-4">
          <div id="map" style="height: 300px;" class="rounded shadow-sm"></div>
          <script>
            const coord = [<?= $resultado['y_wgs84'] ?>, <?= $resultado['x_wgs84'] ?>];
            const direccion = "<?= htmlspecialchars($resultado['calle']) . ' ' . htmlspecialchars($resultado['altura']) ?>";

            const map = L.map('map').setView(coord, 16);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
              attribution: '&copy; OpenStreetMap contributors | UEICEE | MAPA'
            }).addTo(map);

            const circle = L.circle(coord, {
              color: 'orange',
              fillColor: 'yellow',
              fillOpacity: 0.5,
              radius: 15
            }).addTo(map);

            circle.bindPopup("CUI: <?= htmlspecialchars($resultado['cui']) ?><br>Dirección: " + direccion);
          </script>
        </div>
      </div>
    <?php endif; ?>
  </main>
  <?php include('../includes/footer.php'); ?>
</body>
</html>
