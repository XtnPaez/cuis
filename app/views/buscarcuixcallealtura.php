<?php
  session_start();
  require_once('../config/config.php');
?>
<!doctype html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
    <title>UEICEE : MAPA : CUIS : Buscar CUI por Calle y Altura</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/sticky.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  </head>
  <body class="d-flex flex-column min-vh-100">
    <?php include('../includes/navbar.php'); ?>
    <main class="flex-grow-1 container py-5 mt-5">
      <h3>Buscar CUI por Calle y Altura</h3>
      <form method="GET" class="row g-3 mb-4">
        <div class="col-md-5">
          <input type="text" name="calle" id="calle" class="form-control" list="sugerencias" autocomplete="off" placeholder="Busque una Calle" required>
          <datalist id="sugerencias"></datalist>
        </div>
        <div class="col-md-5">
          <input type="number" name="altura" id="altura" class="form-control" placeholder="Ingrese Altura buscada" required>
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary">Buscar</button>
        </div>
      </form>
      <div id="alertas"></div>
      <div id="map" style="height: 400px;"></div>
      <!-- Div para el detalle del CUI seleccionado -->
      <div id="detalle-cui" class="mt-4"></div>
      <?php
        if (isset($_GET['calle'], $_GET['altura'])) {
          $calle = $_GET['calle'];
          $altura = (int)$_GET['altura'];
          // 1. Buscar exactamente calle + altura
          $stmt_exact = $pdo->prepare("
                                        SELECT calle, altura, x_wgs84, y_wgs84, cui
                                        FROM cuis.v_direcciones_coordenadas
                                        WHERE calle ILIKE :calle AND altura = :altura
                                      ");
          $stmt_exact->execute([
                                ':calle' => $calle,
                                ':altura' => $altura,
                              ]);
          $resultados_exactos = $stmt_exact->fetchAll(PDO::FETCH_ASSOC);
          if (count($resultados_exactos) > 0) {
            // Mostrar mapa con los puntos exactos encontrados
            $ref = $resultados_exactos[0];
            echo "<script>
                    document.getElementById('alertas').innerHTML = `<div class=\"alert alert-success mt-3\">Resultado exacto para <strong>{$calle} {$altura}</strong></div>`;
                    let map = L.map('map').setView([{$ref['y_wgs84']}, {$ref['x_wgs84']}], 17);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
                 ";
            foreach ($resultados_exactos as $p) {
              $calle_esc = htmlspecialchars($p['calle']);
              $altura_esc = htmlspecialchars($p['altura']);
              $cui_esc = htmlspecialchars($p['cui']);
              $popup = "$calle_esc $altura_esc<br><strong>CUI:</strong> <a href=\"#\" class=\"ver-cui\" data-cui=\"$cui_esc\">$cui_esc</a>";
              echo "L.marker([{$p['y_wgs84']}, {$p['x_wgs84']}]).addTo(map)
                      .bindPopup(" . json_encode($popup) . ");";
            }
            echo "</script>";
          } else {
            // No exacto: buscar coordenada aproximada para luego buscar puntos a 100m
            $coord_estimada = $pdo->prepare("
                                              SELECT x_wgs84, y_wgs84
                                              FROM cuis.v_direcciones_coordenadas
                                              WHERE calle ILIKE :calle AND altura BETWEEN :a1 AND :a2
                                              LIMIT 1
                                            ");
            $coord_estimada->execute([
                                      ':calle' => $calle,
                                      ':a1' => $altura - 10,
                                      ':a2' => $altura + 10,
                                    ]);
            $ref = $coord_estimada->fetch(PDO::FETCH_ASSOC);
            

    if ($ref) {
      // Buscar todos los puntos a 100m alrededor de la coordenada estimada
      $sql = "
        SELECT calle, altura, x_wgs84, y_wgs84, cui
        FROM cuis.v_direcciones_coordenadas
        WHERE ST_DWithin(
          ST_SetSRID(ST_MakePoint(x_wgs84, y_wgs84), 4326)::geography,
          ST_SetSRID(ST_MakePoint(:x, :y), 4326)::geography,
          100
        )
      ";
      $stmt = $pdo->prepare($sql);
      $stmt->execute([
        ':x' => $ref['x_wgs84'],
        ':y' => $ref['y_wgs84']
      ]);
      $cercanos = $stmt->fetchAll(PDO::FETCH_ASSOC);

      if (count($cercanos) > 0) {
        echo "<script>
          document.getElementById('alertas').innerHTML = `<div class=\"alert alert-info mt-3\">
            No se encontró el par exacto, pero se muestran direcciones a 100 metros de <strong>{$calle} {$altura}</strong>
          </div>`;
          let map = L.map('map').setView([{$ref['y_wgs84']}, {$ref['x_wgs84']}], 17);
          L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        ";
        foreach ($cercanos as $p) {
  $calle = htmlspecialchars($p['calle']);
  $altura = htmlspecialchars($p['altura']);
  $cui = htmlspecialchars($p['cui']);
  $popup = "$calle $altura<br><strong>CUI:</strong> <a href=\"#\" class=\"ver-cui\" data-cui=\"$cui\">$cui</a>";

  echo "L.circleMarker([{$p['y_wgs84']}, {$p['x_wgs84']}], {
    color: 'orange',
    fillColor: 'yellow',
    fillOpacity: 0.7,
    radius: 7
  }).addTo(map).bindPopup(" . json_encode($popup) . ");";
}

        echo "</script>";
      } else {
        echo "<script>
          document.getElementById('alertas').innerHTML = `<div class=\"alert alert-warning mt-3\">
            No se encontraron direcciones dentro de 100 metros
          </div>`;
        </script>";
      }
    } else {
      echo "<script>
        document.getElementById('alertas').innerHTML = `<div class=\"alert alert-warning mt-3\">
          No se encontraron coordenadas aproximadas para esa calle y altura
        </div>`;
      </script>";
    }
  }
}
echo "</script>";

// Agregar esta línea para limpiar la URL
echo "<script>if (window.history.replaceState) window.history.replaceState(null, null, window.location.pathname);</script>";

?>


<!-- Div para comentarios y observaciones -->
      <div class="mt-3 p-3 border border-warning rounded bg-light">
        <h6 class="text-warning">Pendientes:</h6>
        <ul class="mb-0">
          <li>Falta mostrar los datos de CUI como si buscaras por código</li>
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
<script>
document.addEventListener('click', function (e) {
  if (e.target && e.target.classList.contains('ver-cui')) {
    e.preventDefault();
    const cui = e.target.getAttribute('data-cui');
    fetch('../views/detalle_cui.php?cui=' + encodeURIComponent(cui))
      .then(response => response.text())
      .then(html => {
        document.getElementById('detalle-cui').innerHTML = html;
      })
      .catch(err => {
        document.getElementById('detalle-cui').innerHTML = '<div class="alert alert-danger">Error al cargar los datos del CUI</div>';
      });
  }
});
</script>

<script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
