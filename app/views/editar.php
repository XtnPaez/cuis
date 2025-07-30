<?php
  // chequeo inicio de sesiónº 
  session_start();
  // traigo la conexion
  require_once('../config/config.php'); 
  // inicializo las variables
  $edificio = null;
  $error = null;
  $exito = null;
  $predios = [];
  $estados = [];
  $sectores = [];
  // Obtener predios
  try {
    $stmtPredios = $pdo->query("SELECT id, nombre FROM cuis.predios ORDER BY nombre");
    $predios = $stmtPredios->fetchAll();
  } catch (Exception $e) {
    $error = "Error al obtener los predios: " . $e->getMessage();
  }
  // Obtener valores de los CHECK constraints
  try {
    $checkSql = "
      SELECT conname, pg_get_constraintdef(oid) AS definition
      FROM pg_constraint
      WHERE conrelid = 'cuis.edificios'::regclass AND contype = 'c';
    ";
    $stmtChecks = $pdo->query($checkSql);
    $checks = $stmtChecks->fetchAll();
    foreach ($checks as $check) {
      if (str_contains($check['definition'], 'estado = ANY')) {
        preg_match_all("/'([^']+)'/", $check['definition'], $matches);
        $estados = $matches[1];
      } elseif (str_contains($check['definition'], 'sector = ANY')) {
        preg_match_all("/'([^']+)'/", $check['definition'], $matches);
        $sectores = $matches[1];
      }
    }
  } catch (Exception $e) {
    $error = "Error al obtener los estados y sectores: " . $e->getMessage();
  }
  // Búsqueda por CUI con redirección (PRG)
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cui']) && !isset($_POST['confirmar'])) {
    $cui = $_POST['cui'];
    $sql = "SELECT * FROM cuis.edificios WHERE cui = :cui";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':cui', $cui, PDO::PARAM_STR);
    $stmt->execute();
    $edificio = $stmt->fetch();
    if ($edificio) {
      $_SESSION['edificio'] = $edificio;
      $_SESSION['original_edificio'] = $edificio;
      header("Location: " . $_SERVER['PHP_SELF']);
      exit;
    } else {
      $_SESSION['error'] = "No se encontró ningún edificio con ese CUI.";
      header("Location: " . $_SERVER['PHP_SELF']);
      exit;
    }
  }
  // Recuperar edificio y error si vienen de la sesión (después de redirigir)
  if (isset($_SESSION['edificio'])) {
    $edificio = $_SESSION['edificio'];
    unset($_SESSION['edificio']);
  }
  if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
  }
  if (isset($_SESSION['exito'])) {
  $exito = $_SESSION['exito'];
  unset($_SESSION['exito']);
  }
  // Si se llega con GET (por redirección), buscar el edificio
  if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['cui'])) {
    $cui = $_GET['cui'];
    $sql = "SELECT * FROM cuis.edificios WHERE cui = :cui";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':cui', $cui, PDO::PARAM_STR);
    $stmt->execute();
    $edificio = $stmt->fetch();
    if (!$edificio) {
      $error = "No se encontró ningún edificio con ese CUI.";
    }
  }
  // Confirmación de modificación
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar']) && $_POST['confirmar'] === 'si') {
    try {
      $original = $_SESSION['original_edificio'];
      $campos = ['estado', 'sector', 'predio_id', 'institucion', 'gestionado'];
      $a_actualizar = [];
      $valores = [];
      foreach ($campos as $campo) {
        $nuevo = $_POST[$campo] ?? null;
        if ($campo === 'gestionado') {
        $nuevo = $nuevo == 1 ? 1 : 0;
        $original[$campo] = $original[$campo] == 1 ? 1 : 0;
        } elseif ($campo === 'predio_id') {
        $nuevo = $nuevo === '' ? null : $nuevo;
        $original[$campo] = $original[$campo] ?? null;
        }
        if ($nuevo != $original[$campo]) {
        $a_actualizar[] = "$campo = :$campo";
        $valores[":$campo"] = $nuevo;
        }
      }
      if (!empty($a_actualizar)) {
        $sql = "UPDATE cuis.edificios SET " . implode(', ', $a_actualizar) . " WHERE id = :id";
        $valores[':id'] = $_POST['id'];
        $stmt = $pdo->prepare($sql);
        $stmt->execute($valores);
        $campos_actualizados = array_map(function($campo) {
          return explode(' =', $campo)[0]; // extrae el nombre del campo de "campo = :campo"
        }, $a_actualizar);
        $exito = "Se actualizaron los siguientes campos: " . implode(', ', $campos_actualizados);
        $_SESSION['exito'] = $exito;
        unset($_SESSION['original_edificio']);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
        } else {
          $exito = "No había cambios para guardar.";
        }
        unset($_SESSION['original_edificio']);
        $_POST = [];
        } catch (Exception $e) {
          $error = "Error al guardar los cambios: " . $e->getMessage();
        }
      }
      // Cancelar confirmación
      if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar']) && $_POST['confirmar'] === 'no') {
        $sql = "SELECT * FROM cuis.edificios WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $_POST['id']]);
        $edificio = $stmt->fetch();
        unset($_SESSION['original_edificio']);
        $_SESSION['edificio'] = $edificio;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
      }
?>
<!doctype html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
    <title>UEICEE : MAPA : CUIS : Editar</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/sticky.css" rel="stylesheet">
    <link rel="icon" href="../images/favicon.ico">
    <!-- Estilos de editar -->
    <style>
      .fade-out {
        animation: fadeOut 1s ease-in-out forwards;
        animation-delay: 3s;
      }
      @keyframes fadeOut {
        to {
          opacity: 0;
          visibility: hidden;
        }
      }
    </style>
  </head>
  <body class="d-flex flex-column min-vh-100">
    <!-- traigo el navbar -->
    <?php include('../includes/navbar.php'); ?>
    <main class="container mt-5 pt-5 flex-grow-1">
      <h2 class="mb-4 text-center">Editar CUI</h2>
      <?php if ($exito): ?>
        <div class="alert alert-success text-center fade-out"><?= $exito ?></div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert alert-danger text-center fade-out"><?= $error ?></div>
      <?php endif; ?>
      <form method="POST" class="mb-4">
        <div class="input-group">
          <input type="text" name="cui" class="form-control" placeholder="Ingresá el CUI" required value="<?= isset($edificio['cui']) ? htmlspecialchars($edificio['cui']) : '' ?>">
          <button class="btn btn-primary" type="submit">Buscar</button>
        </div>
      </form>
      <?php if ($edificio && !isset($_POST['confirmar'])): ?>
      <form method="POST">
        <input type="hidden" name="id" value="<?= htmlspecialchars($edificio['id']) ?>">
        <div class="mb-3">
          <label class="form-label">CUI</label>
          <input type="text" class="form-control" value="<?= htmlspecialchars($edificio['cui']) ?>" readonly>
        </div>
        <!-- muestro estado -->
        <div class="mb-3">
          <label class="form-label">Estado</label>
          <select name="estado" class="form-select">
            <?php foreach ($estados as $estado): ?>
              <option value="<?= $estado ?>" <?= ($edificio['estado'] === $estado) ? 'selected' : '' ?>><?= ucfirst($estado) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <!-- muestro sector -->
        <div class="mb-3">
          <label class="form-label">Sector</label>
          <select name="sector" class="form-select">
            <?php foreach ($sectores as $sector): ?>
              <option value="<?= $sector ?>" <?= ($edificio['sector'] === $sector) ? 'selected' : '' ?>><?= ucfirst($sector) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <!-- muestro predio -->
        <div class="mb-3">
          <label class="form-label">Predio</label>
          <select name="predio_id" class="form-select">
            <option value="" <?= is_null($edificio['predio_id']) ? 'selected' : '' ?>>(Sin predio)</option>
            <?php foreach ($predios as $predio): ?>
              <option value="<?= $predio['id'] ?>" <?= ($edificio['predio_id'] == $predio['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($predio['nombre']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <!-- muestro institucion -->
        <div class="mb-3">
          <label class="form-label">Institución</label>
          <input type="text" name="institucion" class="form-control" value="<?= htmlspecialchars($edificio['institucion']) ?>">
        </div>
        <!-- muestro gestionado -->
        <div class="mb-3">
          <label class="form-label">Gestionado</label>
          <select name="gestionado" class="form-select" required>
            <option value="1" <?= ($edificio['gestionado']) ? 'selected' : '' ?>>Sí</option>
            <option value="0" <?= (!$edificio['gestionado']) ? 'selected' : '' ?>>No</option>
          </select>
        </div>
        <!-- escondo las coordenadas -->
        <input type="hidden" name="x_gkba" value="<?= htmlspecialchars($edificio['x_gkba']) ?>">
        <input type="hidden" name="y_gkba" value="<?= htmlspecialchars($edificio['y_gkba']) ?>">
        <input type="hidden" name="x_wgs84" value="<?= htmlspecialchars($edificio['x_wgs84']) ?>">
        <input type="hidden" name="y_wgs84" value="<?= htmlspecialchars($edificio['y_wgs84']) ?>">
        <div class="text-center mt-4">
          <button type="submit" name="confirmar" value="pendiente" class="btn btn-success">Guardar Cambios</button>
        </div>
      </form>
      <?php elseif (isset($_POST['confirmar']) && $_POST['confirmar'] === 'pendiente'): ?>
        <?php
          if (!isset($_SESSION['original_edificio']) || !is_array($_SESSION['original_edificio'])) {
            echo '<div class="alert alert-danger">Error: no se encontró el estado original del edificio. Intentá buscarlo nuevamente.</div>';
          } else {
            $original = $_SESSION['original_edificio'];
            $campos = ['estado', 'sector', 'predio_id', 'institucion', 'gestionado'];
            $cambios = [];

            foreach ($campos as $campo) {
              $nuevo = $_POST[$campo] ?? null;

              if ($campo === 'gestionado') {
                $nuevo = $nuevo == 1 ? 1 : 0;
                $original[$campo] = $original[$campo] == 1 ? 1 : 0;
              } elseif ($campo === 'predio_id') {
                $nuevo = $nuevo === '' ? null : $nuevo;
                $original[$campo] = $original[$campo] ?? null;
              }

              if ($nuevo != $original[$campo]) {
                $cambios[$campo] = [
                  'de' => $original[$campo] ?? null,
                  'a' => $nuevo
                ];
              }
            }
        ?>
        <div class="alert alert-warning">
          <p><strong>¿Estás seguro de que querés guardar los siguientes cambios?</strong></p>
          <ul class="text-start">
            <?php foreach ($cambios as $campo => $valores): ?>
              <li><strong><?= ucfirst($campo) ?>:</strong>
                de <code>
                  <?php
                    if ($campo === 'gestionado') {
                      echo $valores['de'] ? 'Sí' : 'No';
                    } else {
                      echo ($valores['de'] === null || $valores['de'] === '') ? '[Campo sin datos]' : htmlspecialchars($valores['de']);
                    }
                  ?>
                </code>
                a <code>
                  <?php
                    if ($campo === 'gestionado') {
                      echo $valores['a'] ? 'Sí' : 'No';
                    } else {
                      echo ($valores['a'] === null || $valores['a'] === '') ? '[Campo sin datos]' : htmlspecialchars($valores['a']);
                    }
                  ?>
                </code>
              </li>
            <?php endforeach; ?>
          </ul>
          <?php if (empty($cambios)): ?>
            <div class="alert alert-info">No hay cambios para guardar.</div>
          <?php else: ?>
          <form method="POST">
            <?php foreach ($_POST as $key => $value): ?>
              <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>">
            <?php endforeach; ?>
            <button type="submit" name="confirmar" value="si" class="btn btn-success me-2">Sí, cambiar</button>
            <button type="submit" name="confirmar" value="no" class="btn btn-secondary">No, volver</button>
          </form>
          <?php endif; ?>
        </div>
        <?php } // cierra else ?>
      <?php endif; ?>
    </main>
    <?php include('../includes/footer.php'); ?>
    <script src="../js/bootstrap.bundle.min.js"></script>
  </body>
</html>