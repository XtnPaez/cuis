<?php 
  session_start();
  require_once('../config/config.php'); 

  // Procesamiento de inserciones (Agregar registro)
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_registro']) && isset($_POST['tabla'])) {
    $tabla = $_POST['tabla'];

    if ($tabla === 'predios') {
      $cup = $_POST['cup'];
      $nombre = $_POST['nombre'];

      $stmt = $pdo->prepare("INSERT INTO cuis.predios (cup, nombre) VALUES (:cup, :nombre)");
      $stmt->execute([':cup' => $cup, ':nombre' => $nombre]);

    } elseif ($tabla === 'operativos') {
      $nombre = $_POST['nombre'];

      $stmt = $pdo->prepare("INSERT INTO cuis.operativos (nombre) VALUES (:nombre)");
      $stmt->execute([':nombre' => $nombre]);
    }

    // Redirige para evitar reenvío del formulario
    header("Location: actualizaciones.php");
    exit;
  }

  // Procesamiento de eliminación de registros
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_id']) && isset($_POST['tabla'])) {
    $tabla = $_POST['tabla'];
    $id = $_POST['eliminar_id'];

    if ($tabla === 'predios') {
      $stmt = $pdo->prepare("DELETE FROM cuis.predios WHERE id = :id");
      $stmt->execute([':id' => $id]);

    } elseif ($tabla === 'operativos') {
      $stmt = $pdo->prepare("DELETE FROM cuis.operativos WHERE id = :id");
      $stmt->execute([':id' => $id]);
    }

    // Redirige para evitar reenvío del formulario
    header("Location: actualizaciones.php");
    exit;
  }
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>UEICEE : MAPA : CUIS : Actualizaciones</title>
  <link href="../css/bootstrap.min.css" rel="stylesheet">
  <link href="../css/sticky.css" rel="stylesheet">
  <link rel="apple-touch-icon" href="../images/apple-icon-180x180.png" sizes="180x180">
  <link rel="icon" href="../images/favicon-32x32.png" sizes="32x32" type="image/png">
  <link rel="icon" href="../images/favicon-16x16.png" sizes="16x16" type="image/png">
  <link rel="icon" href="../images/favicon.ico">
</head>
<body class="d-flex flex-column min-vh-100">
  <?php include('../includes/navbar.php'); ?>

  <main class="container mt-5 pt-5 flex-grow-1">
    <h2 class="text-center mb-4">Gestión de Tablas</h2>

    <div class="row">
      <!-- Predios -->
      <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
          <div class="card-body">
            <h5 class="card-title">Predios</h5>
            <button class="btn btn-outline-primary mb-3" onclick="toggleTabla('tablaPredios')">Ver Datos</button>

            <div id="tablaPredios" class="d-none">
              <table class="table table-sm table-striped">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>CUP</th>
                    <th>Nombre</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                <?php
                  $query = "SELECT id, cup, nombre FROM cuis.predios ORDER BY id ASC";
                  $stmt = $pdo->query($query);
                  while ($row = $stmt->fetch()) {
                    $id = htmlspecialchars($row['id']);
                    $cup = htmlspecialchars($row['cup']);
                    $nombre = htmlspecialchars($row['nombre']);
                    echo "<tr>";
                    echo "<td>$id</td>";
                    echo "<td>$cup</td>";
                    echo "<td>$nombre</td>";
                    echo "<td>
                      <button class='btn btn-sm btn-warning'>Modificar</button>
                      <button class='btn btn-sm btn-danger' data-bs-toggle='modal' data-bs-target='#confirmarEliminarPredio$id'>Eliminar</button>
                    </td>";
                    echo "</tr>";

                    // Modal de confirmación para eliminar
                    echo "
                    <div class='modal fade' id='confirmarEliminarPredio$id' tabindex='-1'>
                      <div class='modal-dialog'>
                        <div class='modal-content'>
                          <div class='modal-header'>
                            <h5 class='modal-title'>Confirmar Eliminación</h5>
                            <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                          </div>
                          <div class='modal-body'>
                            ¿Está seguro de eliminar el predio <strong>$nombre</strong>?
                          </div>
                          <div class='modal-footer'>
                            <form method='POST'>
                              <input type='hidden' name='eliminar_id' value='$id'>
                              <input type='hidden' name='tabla' value='predios'>
                              <button type='submit' class='btn btn-danger'>Sí, eliminar</button>
                            </form>
                            <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>No, volver</button>
                          </div>
                        </div>
                      </div>
                    </div>";
                  }
                ?>
                </tbody>
              </table>
              <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#agregarPredioModal">Agregar Registro</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Operativos -->
      <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
          <div class="card-body">
            <h5 class="card-title">Operativos</h5>
            <button class="btn btn-outline-primary mb-3" onclick="toggleTabla('tablaOperativos')">Ver Datos</button>

            <div id="tablaOperativos" class="d-none">
              <table class="table table-sm table-striped">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                <?php
                  $query = "SELECT id, nombre FROM cuis.operativos ORDER BY id ASC";
                  $stmt = $pdo->query($query);
                  while ($row = $stmt->fetch()) {
                    $id = htmlspecialchars($row['id']);
                    $nombre = htmlspecialchars($row['nombre']);
                    echo "<tr>";
                    echo "<td>$id</td>";
                    echo "<td>$nombre</td>";
                    echo "<td>
                      <button class='btn btn-sm btn-warning'>Modificar</button>
                      <button class='btn btn-sm btn-danger' data-bs-toggle='modal' data-bs-target='#confirmarEliminarOperativo$id'>Eliminar</button>
                    </td>";
                    echo "</tr>";

                    // Modal de confirmación para eliminar
                    echo "
                    <div class='modal fade' id='confirmarEliminarOperativo$id' tabindex='-1'>
                      <div class='modal-dialog'>
                        <div class='modal-content'>
                          <div class='modal-header'>
                            <h5 class='modal-title'>Confirmar Eliminación</h5>
                            <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                          </div>
                          <div class='modal-body'>
                            ¿Está seguro de eliminar el operativo <strong>$nombre</strong>?
                          </div>
                          <div class='modal-footer'>
                            <form method='POST'>
                              <input type='hidden' name='eliminar_id' value='$id'>
                              <input type='hidden' name='tabla' value='operativos'>
                              <button type='submit' class='btn btn-danger'>Sí, eliminar</button>
                            </form>
                            <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>No, volver</button>
                          </div>
                        </div>
                      </div>
                    </div>";
                  }
                ?>
                </tbody>
              </table>
              <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#agregarOperativoModal">Agregar Registro</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Agregar Predio -->
    <div class="modal fade" id="agregarPredioModal" tabindex="-1" aria-labelledby="agregarPredioModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="agregarPredioModalLabel">Agregar Predio</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form method="POST">
              <div class="mb-3">
                <label for="cup" class="form-label">CUP</label>
                <input type="text" class="form-control" id="cup" name="cup" required>
              </div>
              <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required>
              </div>
              <input type="hidden" name="tabla" value="predios">
              <button type="submit" name="agregar_registro" class="btn btn-primary">Agregar Predio</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Agregar Operativo -->
    <div class="modal fade" id="agregarOperativoModal" tabindex="-1" aria-labelledby="agregarOperativoModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="agregarOperativoModalLabel">Agregar Operativo</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form method="POST">
              <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required>
              </div>
              <input type="hidden" name="tabla" value="operativos">
              <button type="submit" name="agregar_registro" class="btn btn-primary">Agregar Operativo</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <script>
      function toggleTabla(id) {
        const tabla = document.getElementById(id);
        tabla.classList.toggle('d-none');
      }
    </script>
  </main>

  <?php include('../includes/footer.php'); ?>

  <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
