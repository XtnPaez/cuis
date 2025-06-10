<?php
  // chequeo el inicio de sesión
  session_start();
  // traigo la conexion
  require_once('../config/config.php');
  // inicializo las variables
  $resultado = null;
  $error = null;
  // Procesamiento del POST
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cui'])) {
    $cui = $_POST['cui'];
    $sql = "SELECT 
            e.id,
            e.cui,
            e.estado,
            e.sector,
            CASE WHEN e.institucion is null THEN 'Sin Institución Asociada' ELSE e.institucion END as institucion,
            CASE WHEN e.gestionado = true THEN 'Gestionado' ELSE 'No Gestionado' END as gestionado,
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
    $resultado = $stmt->fetch();
    // chequeo si encontró resultados
    if ($resultado) {
      $_SESSION['busqueda_cui'] = $resultado;
    } else {
      $_SESSION['error_cui'] = "No se encontró ningún edificio con el CUI ingresado.";
    }
    // Redirigir para evitar reenvío
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
  } // cierra el if
  // GET después del redirect
  if (isset($_SESSION['busqueda_cui'])) {
    $resultado = $_SESSION['busqueda_cui'];
    unset($_SESSION['busqueda_cui']);
  }
  if (isset($_SESSION['error_cui'])) {
    $error = $_SESSION['error_cui'];
    unset($_SESSION['error_cui']);
  }
?>
<!doctype html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UEICEE : MAPA : CUIS : Buscar</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/sticky.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link rel="apple-touch-icon" href="../images/apple-icon-180x180.png" sizes="180x180">
    <link rel="icon" href="../images/favicon-32x32.png" sizes="32x32" type="image/png">
  </head>
  <body class="d-flex flex-column min-vh-100">
    <!-- traigo el navbar -->
    <?php include('../includes/navbar.php'); ?>
    <main class="flex-grow-1 container py-5">
      <h2 class="text-center mb-5 mt-4">Buscar CUI por código</h2>
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
                  <li class="list-group-item"><strong>Calle:</strong> <?= htmlspecialchars($resultado['calle']) ?></li>
                  <li class="list-group-item"><strong>Altura:</strong> <?= htmlspecialchars($resultado['altura']) ?></li>
                  <li class="list-group-item"><strong>Gestionado:</strong> <?= htmlspecialchars($resultado['gestionado']) ?></li>
                  <li class="list-group-item"><strong>Institución:</strong> <?= htmlspecialchars($resultado['institucion']) ?></li>
                  <li class="list-group-item"><strong>Código de Predio:</strong> <?= htmlspecialchars($resultado['codpre']) ?></li>
                  <li class="list-group-item"><strong>Predio:</strong> <?= htmlspecialchars($resultado['predio']) ?></li>
                  <li class="list-group-item"><strong>Comuna:</strong> <?= htmlspecialchars($resultado['comuna']) ?></li>
                  <li class="list-group-item"><strong>Barrio:</strong> <?= htmlspecialchars($resultado['barrio']) ?></li>
                  <li class="list-group-item"><strong>Comisaría:</strong> <?= htmlspecialchars($resultado['comisaria']) ?></li>
                  <li class="list-group-item"><strong>Comisaría Vecinal:</strong> <?= htmlspecialchars($resultado['comisaria_vecinal']) ?></li>
                  <li class="list-group-item"><strong>Área Hospitalaria:</strong> <?= htmlspecialchars($resultado['area_hospitalaria']) ?></li>
                  <li class="list-group-item"><strong>Región Sanitaria:</strong> <?= htmlspecialchars($resultado['region_sanitaria']) ?></li>
                  <li class="list-group-item"><strong>Código Postal:</strong> <?= htmlspecialchars($resultado['codigo_postal']) ?></li>
                  <li class="list-group-item"><strong>Código Postal Argentino:</strong> <?= htmlspecialchars($resultado['codigo_postal_argentino']) ?></li>
                  <li class="list-group-item"><strong>CENIE 2010:</strong> <?= htmlspecialchars($resultado['op1']) ?></li>
                  <li class="list-group-item"><strong>CIE 2017:</strong> <?= htmlspecialchars($resultado['op2']) ?></li>
                </ul>
              </div>
            </div>
          </div><!-- termina datos del edificio -->
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
              // armo el marcador
              const circle = L.circle(coord, {
                color: 'orange',
                fillColor: 'yellow',
                fillOpacity: 0.5,
                radius: 15
              }).addTo(map);
              // armo el popup
              circle.bindPopup("CUI: <?= htmlspecialchars($resultado['cui']) ?><br>Dirección: " + direccion);
            </script>
          </div><!-- termimna mapa -->
        </div>
      <?php endif; ?>
    <!-- Div para comentarios y observaciones -->
            <div class="mt-3 p-3 border border-warning rounded bg-light">
              <h6 class="text-warning">Pendientes:</h6>
              <ul class="mb-0">
                <li>Agregar las direcciones asociadas al CUI. No se completó bien la base y ahora tenemos solo una para cada CUI.</li>
                <li>Traer listado de CUEANEXOS asociados a CUI.</li>
                <li>Traer datos de RENIE.</li>
                <li>Buscar CUI por dirección: mostrar el listado de direcciones aproximadas a la buscada con su nuemro de cui y un link. al hacer click en el link, se muestra la info</li>
              </ul>
            </div><!-- termina pendientes -->  
    </main>
    <!-- traigo footer -->
    <?php include('../includes/footer.php'); ?>
  </body>
</html>