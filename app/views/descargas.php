<?php 
session_start();
require_once '../config/config.php'; // o el path correcto según tu estructura
setlocale(LC_TIME, 'es_AR.UTF-8'); // para fecha en español si querés
$hoy = date('d/m/Y');
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

<h2 class="text-center mb-5 mt-4">Descargas</h2>


    <div class="card mb-4 shadow-sm border-primary">
  <div class="card-body">
    <h5 class="card-title text-primary">
      Padrón de CUI actualizado al <?php echo $hoy; ?>
    </h5>
    <p class="card-text">
      Listado completo de edificios educativos con su ubicación principal, estado, sector y predio asociado.
    </p>
    <button type="button" class="btn btn-outline-secondary me-2" data-bs-toggle="modal" data-bs-target="#modalPadron">
      Ver muestra
    </button>
    <a href="descargar_padron.php?formato=csv" class="btn btn-success me-2">Descargar en CSV</a>
    <a href="descargar_padron.php?formato=xlsx" class="btn btn-primary">Descargar en Excel</a>
  </div>
</div>


    <!-- Div para comentarios y observaciones -->
    <div class="mt-3 p-3 border border-warning rounded bg-light">
              <h6 class="text-warning">Pendientes:</h6>
              <ul class="mb-0">
                <li>Hay que mejorar el script para que consulte el catalogo y escribir un descargar.php generico</li>
                <li>Para el productivo: crear el trigger que genere los cortes automaticamente (materialized views) y los catalogue.</li>
              </ul>
            </div>


  </main>
<!-- Modal -->
<div class="modal fade" id="modalPadron" tabindex="-1" aria-labelledby="modalPadronLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalPadronLabel">Muestra del padrón de CUI</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table class="table table-sm table-striped">
            <thead>
              <tr>
                <th>CUI</th>
                <th>Estado</th>
                <th>Sector</th>
                <th>Gestionado</th>
                <th>Predio</th>
                <th>Dirección</th>
                <th>Comuna</th>
                <th>Barrio</th>
                <th>CP</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $sql = "SELECT cui, estado, sector, gestionado, predio, direccion_principal, comuna, barrio, codigo_postal
                      FROM cuis.v_padron_cui LIMIT 10";
              $stmt = $pdo->query($sql);
              while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>";
                foreach ($row as $val) {
                  echo "<td>" . htmlspecialchars($val) . "</td>";
                }
                echo "</tr>";
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <a href="descargar_padron.php?formato=csv" class="btn btn-success">Descargar en CSV</a>
        <a href="descargar_padron.php?formato=xlsx" class="btn btn-primary">Descargar en Excel</a>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

  <?php include('../includes/footer.php'); ?>

  <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>