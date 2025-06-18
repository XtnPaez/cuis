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
    $resultado = $stmt->fetch();
    // chequeo si encontró resultados
    if ($resultado) {
      $_SESSION['busqueda_cui'] = $resultado;
      // Segunda consulta: traer los CUEANEXOS del CUI
      $sqlCueAnexos = " SELECT 
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
                        $stmtCue = $pdo->prepare($sqlCueAnexos);
                        $cui_str = str_pad(trim($_POST['cui']), 7, "0", STR_PAD_LEFT); // Asegura ceros a la izquierda y trim
                        $stmtCue->bindParam(':cui_str', $cui_str, PDO::PARAM_STR);
                        $stmtCue->execute();
                        $cueanexos = $stmtCue->fetchAll();
                        $_SESSION['cueanexos'] = $cueanexos;
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
    if (isset($_SESSION['cueanexos'])) {
    $cueanexos = $_SESSION['cueanexos'];
    unset($_SESSION['cueanexos']);
    } else {
        $cueanexos = [];
    }
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
      <!-- Mensaje de error si lo hay -->
      <?php if ($error): ?>
      <div class="alert alert-danger"><?= $error ?></div>
      <?php endif; ?>
      <!-- Mostrar los resultados cuando no hay error -->
      <?php if ($resultado): ?>
      <!-- Pestañas -->
      <ul class="nav nav-tabs mb-3" id="cuiTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#edificio" type="button" role="tab">Edificio</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="admin-tab" data-bs-toggle="tab" data-bs-target="#geolocalizacion" type="button" role="tab">Geolocalización</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="cueanexos-tab" data-bs-toggle="tab" data-bs-target="#cueanexos" type="button" role="tab">CUEANEXOS en el CUI</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="dires-tab" data-bs-toggle="tab" data-bs-target="#dires" type="button" role="tab">Direcciones Asociadas</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="renie-tab" data-bs-toggle="tab" data-bs-target="#renie" type="button" role="tab">Datos de RENIE</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="map-tab" data-bs-toggle="tab" data-bs-target="#mapa" type="button" role="tab">Mapa</button>
        </li>
      </ul>
      <!-- Contenido de las pestañas -->
      <div class="tab-content" id="cuiTabsContent">
        <!-- Edificio -->
        <div class="tab-pane fade show active" id="edificio" role="tabpanel">
          <div class="card shadow-sm mb-3">
            <div class="card-body">
              <p><strong>CUI:</strong> <?= htmlspecialchars($resultado['cui']) ?></p>
              <p><strong>Estado:</strong> <?= htmlspecialchars($resultado['estado']) ?></p>
              <p><strong>Sector:</strong> <?= htmlspecialchars($resultado['sector']) ?></p>
              <p><strong>Institución:</strong> <?= htmlspecialchars($resultado['institucion']) ?></p>
              <p><strong>Gestionado:</strong> <?= htmlspecialchars($resultado['gestionado']) ?></p>
              <p><strong>Predio:</strong> <?= htmlspecialchars($resultado['codpre']) ?> - <?= htmlspecialchars($resultado['predio']) ?></p>
              <p><strong>Coordenada XGK:</strong> <?= htmlspecialchars($resultado['x_gkba']) ?></p>
              <p><strong>Coordenada YGK:</strong> <?= htmlspecialchars($resultado['y_gkba']) ?></p>
            </div>
          </div>
        </div>
        <!-- Geolocalización -->
        <div class="tab-pane fade" id="geolocalizacion" role="tabpanel">
          <div class="card shadow-sm mb-3">
            <div class="card-body">
              <ul class="list-group list-group-flush">
                <li class="list-group-item"><strong>Dirección principal - Calle:</strong> <?= htmlspecialchars($resultado['calle']) ?></li>
                <li class="list-group-item"><strong>Dirección principal - Altura:</strong> <?= htmlspecialchars($resultado['altura']) ?></li>
                <li class="list-group-item"><strong>Comuna:</strong> <?= htmlspecialchars($resultado['comuna']) ?></li>
                <li class="list-group-item"><strong>Barrio:</strong> <?= htmlspecialchars($resultado['barrio']) ?></li>
                <li class="list-group-item"><strong>Comisaría:</strong> <?= htmlspecialchars($resultado['comisaria']) ?></li>
                <li class="list-group-item"><strong>Comisaría Vecinal:</strong> <?= htmlspecialchars($resultado['comisaria_vecinal']) ?></li>
                <li class="list-group-item"><strong>Área Hospitalaria:</strong> <?= htmlspecialchars($resultado['area_hospitalaria']) ?></li>
                <li class="list-group-item"><strong>Región Sanitaria:</strong> <?= htmlspecialchars($resultado['region_sanitaria']) ?></li>
                <li class="list-group-item"><strong>Código Postal:</strong> <?= htmlspecialchars($resultado['codigo_postal']) ?></li>
                <li class="list-group-item"><strong>CPA:</strong> <?= htmlspecialchars($resultado['codigo_postal_argentino']) ?></li>
              </ul>
            </div>
          </div>
        </div>
        <!-- CUEANEXOS -->
        <div class="tab-pane fade" id="cueanexos" role="tabpanel">
          <div class="card shadow-sm mb-3">
            <div class="card-body">
              <?php if (!empty($cueanexos)): ?>
              <div class="table-responsive">
                <table class="table table-sm table-striped">
                  <thead class="table-dark">
                    <tr>
                      <th>CUE</th>
                      <th>Anexo</th>
                      <th>Nombre</th>
                      <th>Jurisdiccional</th>
                      <th>Teléfono</th>
                      <th>Responsable</th>
                      <th>Email</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($cueanexos as $fila): ?>
                    <tr>
                      <td><?= htmlspecialchars($fila['cue']) ?></td>
                      <td><?= htmlspecialchars($fila['anexo']) ?></td>
                      <td><?= htmlspecialchars($fila['nombre']) ?></td>
                      <td><?= htmlspecialchars($fila['codigo_jurisdiccional']) ?></td>
                      <td><?= htmlspecialchars($fila['telefono']) ?></td>
                      <td><?= htmlspecialchars($fila['apellidor'] . ', ' . $fila['nombrer']) ?></td>
                      <td><?= htmlspecialchars($fila['email']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
              <?php else: ?>
                <p class="text-muted">No se encontraron CUEANEXOS asociados a este CUI.</p>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <!-- Direcciones Asociadas -->
        <div class="tab-pane fade" id="dires" role="tabpanel">
          <div class="card shadow-sm mb-3">
            <div class="card-body">
              <ul class="list-group list-group-flush">
                <li class="list-group-item"><strong>Calle:</strong> <?= htmlspecialchars($resultado['calle']) ?></li>
              </ul>
            </div>
          </div>
        </div>
        <!-- Datos de RENIE -->
        <div class="tab-pane fade" id="renie" role="tabpanel">
          <div class="card shadow-sm mb-3">
            <div class="card-body">
              <ul class="list-group list-group-flush">
                <li class="list-group-item"><strong>Calle:</strong> <?= htmlspecialchars($resultado['calle']) ?></li>
              </ul>
            </div>
          </div>
        </div>
        <!-- Mapa -->
        <div class="tab-pane fade" id="mapa" role="tabpanel">
          <div id="map" style="height: 400px;" class="rounded shadow-sm w-100"></div>
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
      </div> <!-- termina contenido de las pestañas -->
      <?php endif; ?>
      <!-- Pendientes -->
      <div class="mt-3 p-3 border border-warning rounded bg-light">
        <h6 class="text-warning">Pendientes:</h6>
        <ul class="mb-0">
          <li><b>REVISAR.</b><br>Agregar las direcciones asociadas al CUI. <br>La query parece ser esta : <br>
            select aso.calle, aso.altura<br>
            from cuis.edificios edi<br>
            join cuis.edificios_direcciones dir on edi.id = dir.edificio_id<br>
            join cuis.direcciones aso on dir.direccion_id = aso.id<br>
            where edi.cui = '200215'. <br>Pero la tabla puede estar mal poblada.<br>
          </li>
          <li>Traer datos de RENIE.</li>
          <li>Traer datos de parcela.</li>
        </ul>
      </div><!-- termina pendientes -->
    </main>
    <!-- traigo footer -->
    <?php include('../includes/footer.php'); ?>
    <script>
      // Leaflet: corregir tamaño del mapa cuando se activa la pestaña
      const cuiTabs = document.getElementById('cuiTabs');
      cuiTabs.addEventListener('shown.bs.tab', function (event) {
        if (event.target.id === 'map-tab') {
          setTimeout(() => {
            map.invalidateSize(); // Corrige el tamaño al mostrarse
          }, 100); // pequeño delay para asegurar render
        }
      });
    </script>
    <script src="../js/bootstrap.bundle.min.js"></script>
  </body>
</html>