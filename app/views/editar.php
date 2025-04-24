<?php 
  session_start();
  require_once('../config/config.php'); 

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

  // Búsqueda por CUI
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
      $gestionado = (isset($_POST['gestionado']) && $_POST['gestionado'] === '1') ? 1 : 0;

      $sql = "UPDATE cuis.edificios SET
                estado = :estado,
                sector = :sector,
                predio_id = :predio_id,
                institucion = :institucion,
                gestionado = :gestionado,
                x_gkba = :x_gkba,
                y_gkba = :y_gkba,
                x_wgs84 = :x_wgs84,
                y_wgs84 = :y_wgs84
              WHERE id = :id";

      $stmt = $pdo->prepare($sql);
      $stmt->execute([
        ':estado' => $_POST['estado'],
        ':sector' => $_POST['sector'],
        ':predio_id' => $_POST['predio_id'],
        ':institucion' => $_POST['institucion'],
        ':gestionado' => $gestionado,
        ':x_gkba' => $_POST['x_gkba'],
        ':y_gkba' => $_POST['y_gkba'],
        ':x_wgs84' => $_POST['x_wgs84'],
        ':y_wgs84' => $_POST['y_wgs84'],
        ':id' => $_POST['id']
      ]);

      $exito = "Los cambios se guardaron correctamente.";
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
  <?php include('../includes/navbar.php'); ?>

  <main class="container mt-5 pt-5 flex-grow-1">
    <h2 class="mb-4 text-center">Editar Edificio</h2>

    <?php if ($exito): ?>
      <div class="alert alert-success text-center fade-out"><?= $exito ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="alert alert-danger text-center fade-out"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" class="mb-4">
      <div class="input-group">
        <input type="text" name="cui" class="form-control" placeholder="Ingresá el CUI" required>
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

        <div class="mb-3">
          <label class="form-label">Estado</label>
          <select name="estado" class="form-select">
            <?php foreach ($estados as $estado): ?>
              <option value="<?= $estado ?>" <?= ($edificio['estado'] === $estado) ? 'selected' : '' ?>><?= ucfirst($estado) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Sector</label>
          <select name="sector" class="form-select">
            <?php foreach ($sectores as $sector): ?>
              <option value="<?= $sector ?>" <?= ($edificio['sector'] === $sector) ? 'selected' : '' ?>><?= ucfirst($sector) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Predio</label>
          <select name="predio_id" class="form-select">
            <?php foreach ($predios as $predio): ?>
              <option value="<?= $predio['id'] ?>" <?= ($edificio['predio_id'] == $predio['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($predio['nombre']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Institución</label>
          <input type="text" name="institucion" class="form-control" value="<?= htmlspecialchars($edificio['institucion']) ?>">
        </div>

        <div class="mb-3">
          <label class="form-label">Gestionado</label>
          <select name="gestionado" class="form-select" required>
            <option value="1" <?= ($edificio['gestionado']) ? 'selected' : '' ?>>Sí</option>
            <option value="0" <?= (!$edificio['gestionado']) ? 'selected' : '' ?>>No</option>
          </select>
        </div>

        <input type="hidden" name="x_gkba" value="<?= htmlspecialchars($edificio['x_gkba']) ?>">
        <input type="hidden" name="y_gkba" value="<?= htmlspecialchars($edificio['y_gkba']) ?>">
        <input type="hidden" name="x_wgs84" value="<?= htmlspecialchars($edificio['x_wgs84']) ?>">
        <input type="hidden" name="y_wgs84" value="<?= htmlspecialchars($edificio['y_wgs84']) ?>">

        <div class="text-center mt-4">
          <button type="submit" name="confirmar" value="pendiente" class="btn btn-success">Guardar Cambios</button>
        </div>
      </form>
    <?php elseif (isset($_POST['confirmar']) && $_POST['confirmar'] === 'pendiente'): ?>
      <div class="alert alert-warning text-center">
        <p>¿Estás seguro de que querés guardar los cambios?</p>
        <form method="POST" class="d-inline">
          <?php foreach ($_POST as $key => $value): ?>
            <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>">
          <?php endforeach; ?>
          <button type="submit" name="confirmar" value="si" class="btn btn-success me-2">Sí, cambiar</button>
          <button type="submit" name="confirmar" value="no" class="btn btn-secondary">No, volver</button>
        </form>
      </div>
    <?php endif; ?>
    <!-- Div para comentarios y observaciones -->
    <div class="mt-3 p-3 border border-warning rounded bg-light">
              <h6 class="text-warning">Pendientes:</h6>
              <ul class="mb-0">
                <li>Faltan campos para la versión productiva. Consultar con el equipo.</li>
              </ul>
            </div>
            <br>
  </main>

  <?php include('../includes/footer.php'); ?>
  <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
