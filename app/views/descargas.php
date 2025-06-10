<?php 
session_start();
require_once('../config/config.php'); 
?>

<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>UEICEE : MAPA : CUIS : Descargas</title>
  <link href="../css/bootstrap.min.css" rel="stylesheet">
  <link href="../css/sticky.css" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <link rel="apple-touch-icon" href="../images/apple-icon-180x180.png" sizes="180x180">
  <link rel="icon" href="../images/favicon-32x32.png" sizes="32x32" type="image/png">
</head>
<body class="d-flex flex-column min-vh-100">
  <?php include('../includes/navbar.php'); ?>

  <main class="flex-grow-1 container py-5 mt-5">
    <?php
      $sql = "SELECT * FROM descargas.catalogo WHERE visible = true ORDER BY (nombre_origen = 'v_padron_cui') DESC, fecha DESC";
      $stmt = $pdo->query($sql);
      $descargas = $stmt->fetchAll(PDO::FETCH_ASSOC);

      echo '<div class="row row-cols-1 row-cols-md-2 g-4">';
      foreach ($descargas as $i => $d) {
        $isPrincipal = $d['nombre_origen'] === 'v_padron_cui';

        if ($isPrincipal) {
          echo '</div>'; // cerrar grilla de 2 columnas
          echo '<div class="card shadow-lg mb-4">';
          echo '  <div class="card-body">';
          echo "    <h4 class='card-title'>{$d['titulo']}</h4>";
          echo "    <h6 class='card-subtitle mb-2 text-muted'>Fecha: {$d['fecha']}</h6>";
          echo "    <p class='card-text'>{$d['descripcion']}</p>";
          echo "    <a href='descargar.php?id={$d['id']}' class='btn btn-primary'>Descargar</a>";
          if ($d['formato'] === 'xlsx') {
            echo " <button class='btn btn-outline-secondary ms-2' data-bs-toggle='modal' data-bs-target='#modal{$d['id']}'>Ver ejemplo</button>";
          }
          echo '  </div>';
          echo '</div>';
          echo '<div class="row row-cols-1 row-cols-md-2 g-4">'; // reabrir grilla
        } else {
          echo '<div class="col">';
          echo '  <div class="card shadow-sm h-100">';
          echo '    <div class="card-body">';
          echo "      <h5 class='card-title'>{$d['titulo']}</h5>";
          echo "      <h6 class='card-subtitle mb-2 text-muted'>Fecha: {$d['fecha']}</h6>";
          echo "      <p class='card-text'>{$d['descripcion']}</p>";
          echo "      <a href='descargar.php?id={$d['id']}' class='btn btn-primary'>Descargar</a>";
          if ($d['formato'] === 'xlsx') {
            echo " <button class='btn btn-outline-secondary ms-2' data-bs-toggle='modal' data-bs-target='#modal{$d['id']}'>Ver ejemplo</button>";
          }
          echo '    </div>';
          echo '  </div>';
          echo '</div>';
        }
      }
      echo '</div>'; // cierre final grilla

      // Modal con ejemplos (solo para los Excel)
      foreach ($descargas as $d) {
        if ($d['formato'] === 'xlsx') {
          echo "<div class='modal fade' id='modal{$d['id']}' tabindex='-1' aria-labelledby='modalLabel{$d['id']}' aria-hidden='true'>";
          echo "  <div class='modal-dialog modal-lg'>";
          echo "    <div class='modal-content'>";
          echo "      <div class='modal-header'>";
          echo "        <h5 class='modal-title' id='modalLabel{$d['id']}'>Ejemplo de '{$d['titulo']}'</h5>";
          echo "        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Cerrar'></button>";
          echo "      </div>";
          echo "      <div class='modal-body'>";

          // Obtener 10 registros de muestra si es tabla o vista
          if (in_array($d['tipo_origen'], ['vista', 'tabla'])) {
            $tabla = $d['esquema_origen'] . '.' . $d['nombre_origen'];
            $stmtEj = $pdo->query("SELECT * FROM $tabla LIMIT 10");
            $ejemplos = $stmtEj->fetchAll(PDO::FETCH_ASSOC);

            if ($ejemplos) {
              echo "<div class='table-responsive'><table class='table table-sm table-bordered'>";
              echo "<thead><tr>";
              foreach (array_keys($ejemplos[0]) as $col) {
                echo "<th>$col</th>";
              }
              echo "</tr></thead><tbody>";
              foreach ($ejemplos as $fila) {
                echo "<tr>";
                foreach ($fila as $valor) {
                  echo "<td>$valor</td>";
                }
                echo "</tr>";
              }
              echo "</tbody></table></div>";
            } else {
              echo "<p class='text-muted'>No se encontraron registros de ejemplo.</p>";
            }
          } else {
            echo "<p class='text-muted'>Este recurso no permite vista previa.</p>";
          }

          echo "      </div>";
          echo "      <div class='modal-footer'>";
          echo "        <a href='descargar.php?id={$d['id']}' class='btn btn-primary'>Descargar completo</a>";
          echo "        <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Cerrar</button>";
          echo "      </div>";
          echo "    </div>";
          echo "  </div>";
          echo "</div>";
        }
      }
    ?>

    <!-- Div para comentarios y observaciones -->
    <div class="mt-3 p-3 border border-warning rounded bg-light">
              <h6 class="text-warning">Pendientes:</h6>
              <ul class="mb-0">
                <li>Los archivos que traigan datos desde la base, hay que generarlos dinamicamente y alojarlos. Ahora, para testear funcionamiento
                  están clavados en el código y en una carpeta. Ofrecer en la misma card diferentes formatos.</li>
                <li>Chequear si los cortes pueden generarse automáticamente con una función en el servidor.</li>
              </ul>
            </div>


  </main>

  <?php include('../includes/footer.php'); ?>

  <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>