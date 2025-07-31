<?php 
session_start();
require_once '../config/config.php'; // Asegurate que esta ruta sea correcta
setlocale(LC_TIME, 'es_AR.UTF-8');
$hoy = date('d/m/Y');
?>

<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CUIS : Descargas</title>
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

    <h2 class="text-center mb-5 mt-4">Descargas</h2>

    <?php
    $sql = "SELECT * FROM descargas.catalogo WHERE visible = true ORDER BY fecha DESC";
    $stmt = $pdo->query($sql);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
      $id = $row['id'];
      $titulo = $row['titulo'];
      $descripcion = $row['descripcion'];
      $formato = $row['formato'];
      $tipo = $row['tipo_origen'];
      $esquema = $row['esquema_origen'];
      $origen = $row['nombre_origen'];
      $fecha = date("d/m/Y", strtotime($row['fecha']));
    ?>
    <div class="card mb-4 shadow-sm border-primary">
      <div class="card-body">
        <h5 class="card-title text-primary"><?= htmlspecialchars($titulo) ?> (<?= $fecha ?>)</h5>
        <p class="card-text"><?= htmlspecialchars($descripcion) ?></p>

        <?php if ($tipo === 'vista' || $tipo === 'tabla'): ?>
          <button type="button" class="btn btn-outline-secondary me-2" data-bs-toggle="modal" data-bs-target="#modalMuestra<?= $id ?>">
            Ver muestra
          </button>
        <?php endif; ?>

        <a href="descargar.php?id=<?= $id ?>&formato=csv" class="btn btn-success me-2">Descargar CSV</a>
        <a href="descargar.php?id=<?= $id ?>&formato=xlsx" class="btn btn-primary">Descargar Excel</a>
      </div>
    </div>

    <?php if ($tipo === 'vista' || $tipo === 'tabla'): ?>
    <!-- Modal para muestra -->
    <div class="modal fade" id="modalMuestra<?= $id ?>" tabindex="-1" aria-labelledby="modalLabel<?= $id ?>" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalLabel<?= $id ?>">Muestra: <?= htmlspecialchars($titulo) ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <div class="table-responsive">
              <table class="table table-sm table-striped">
                <thead>
                  <tr>
                    <?php
                    $cols = [];
                    $sqlCols = "SELECT * FROM " . pg_escape_identifier($esquema) . "." . pg_escape_identifier($origen) . " LIMIT 1";
                    $stmtCols = $pdo->query($sqlCols);
                    if ($stmtCols && $fila = $stmtCols->fetch(PDO::FETCH_ASSOC)) {
                      foreach ($fila as $col => $_) {
                        $cols[] = $col;
                        echo "<th>" . htmlspecialchars($col) . "</th>";
                      }
                    }
                    ?>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $sqlDatos = "SELECT * FROM " . pg_escape_identifier($esquema) . "." . pg_escape_identifier($origen) . " LIMIT 10";
                  $stmtDatos = $pdo->query($sqlDatos);
                  while ($fila = $stmtDatos->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    foreach ($cols as $col) {
                      echo "<td>" . htmlspecialchars($fila[$col]) . "</td>";
                    }
                    echo "</tr>";
                  }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
          <div class="modal-footer">
            <a href="descargar.php?id=<?= $id ?>&formato=csv" class="btn btn-success">Descargar CSV</a>
            <a href="descargar.php?id=<?= $id ?>&formato=xlsx" class="btn btn-primary">Descargar Excel</a>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <?php endwhile; ?>

    <!-- Comentarios -->
    <div class="mt-3 p-3 border border-warning rounded bg-light">
      <h6 class="text-warning">Pendientes:</h6>
      <ul class="mb-0">
        <li>Los archivos que traigan datos desde la base se generan dinámicamente. Los archivos estáticos siguen en carpeta para testeo.</li>
        <li>Chequear si los cortes pueden generarse automáticamente con una función en el servidor.</li>
      </ul>
    </div>

  </main>

  <?php include('../includes/footer.php'); ?>
  <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
