<?php
session_start();
require_once('../config/config.php');

// Inicializo variables
$edificio = null;
$error = null;
$exito = null;
$predios = [];
$estados = [];
$sectores = [];

// Obtener predios y map
try {
    $stmtPredios = $pdo->query("SELECT id, nombre FROM cuis.predios ORDER BY nombre");
    $predios = $stmtPredios->fetchAll();
    $prediosMap = [];
    foreach ($predios as $row) {
        $prediosMap[$row['id']] = $row['nombre'];
    }
} catch (Exception $e) {
    $error = "Error al obtener los predios: " . $e->getMessage();
}

// Obtener estados y map
try {
    $stmtEstados = $pdo->query("SELECT id, descripcion FROM cuis.estados ORDER BY descripcion");
    $estados = $stmtEstados->fetchAll();
    $estadosMap = [];
    foreach ($estados as $row) {
        $estadosMap[$row['id']] = $row['descripcion'];
    }
} catch (Exception $e) {
    $error = "Error al obtener los estados: " . $e->getMessage();
}

// Sectores hardcodeados (si no cambian)
$sectores = ['publico', 'privado', 'otro'];

// Búsqueda por CUI
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cui']) && !isset($_POST['confirmar'])) {
    $cui = $_POST['cui'];
    $sql = "
        SELECT edi.*, est.descripcion AS estado_text
        FROM cuis.edificios edi
        LEFT JOIN cuis.estados est ON edi.estado = est.id
        WHERE edi.cui = :cui
    ";
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

// Recuperar edificio y mensajes de sesión
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

// Confirmación de modificación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar'])) {
    $original = $_SESSION['original_edificio'] ?? [];
    $campos = ['estado', 'sector', 'predio_id', 'institucion', 'gestionado'];
    $cambios = [];
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
            $cambios[$campo] = [
                'de' => $original[$campo],
                'a' => $nuevo
            ];
            $valores[":$campo"] = $nuevo;
        }
    }

    if ($_POST['confirmar'] === 'si' && !empty($cambios)) {
        try {
            $sql = "UPDATE cuis.edificios SET " . implode(', ', array_map(fn($c) => "$c = :$c", array_keys($cambios))) . " WHERE id = :id";
            $valores[':id'] = $_POST['id'];
            $stmt = $pdo->prepare($sql);
            $stmt->execute($valores);
            $exito = "Se actualizaron los siguientes campos: " . implode(', ', array_keys($cambios));
            $_SESSION['exito'] = $exito;
            unset($_SESSION['original_edificio']);
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } catch (Exception $e) {
            $error = "Error al guardar los cambios: " . $e->getMessage();
        }
    } elseif ($_POST['confirmar'] === 'no') {
        unset($_SESSION['original_edificio']);
        header("Location: " . $_SERVER['PHP_SELF'] . "?cui=" . urlencode($_POST['cui']));
        exit;
    }
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
    to { opacity: 0; visibility: hidden; }
}
</style>
</head>
<body class="d-flex flex-column min-vh-100">
<?php include('../includes/navbar.php'); ?>
<main class="container mt-5 pt-5 flex-grow-1">
<h2 class="mb-4 text-center">Editar CUI</h2>

<?php if ($exito): ?>
<div class="alert alert-success text-center fade-out"><?= $exito ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-danger text-center fade-out"><?= $error ?></div>
<?php endif; ?>

<!-- Formulario búsqueda -->
<form method="POST" class="mb-4">
<div class="input-group">
<input type="text" name="cui" class="form-control" placeholder="Ingresá el CUI" required value="<?= htmlspecialchars($edificio['cui'] ?? '') ?>">
<button class="btn btn-primary" type="submit">Buscar</button>
</div>
</form>

<?php if ($edificio && (!isset($_POST['confirmar']) || $_POST['confirmar'] === 'pendiente')): ?>
<form method="POST">
<input type="hidden" name="id" value="<?= htmlspecialchars($edificio['id']) ?>">
<div class="mb-3">
<label class="form-label">CUI</label>
<input type="text" class="form-control" value="<?= htmlspecialchars($edificio['cui']) ?>" readonly>
</div>

<!-- Estado -->
<div class="mb-3">
<label class="form-label">Estado</label>
<select name="estado" class="form-select">
<?php foreach ($estados as $estado): ?>
<option value="<?= $estado['id'] ?>" <?= ($edificio['estado'] == $estado['id']) ? 'selected' : '' ?>>
<?= ucfirst($estado['descripcion']) ?>
</option>
<?php endforeach; ?>
</select>
</div>

<!-- Sector -->
<div class="mb-3">
<label class="form-label">Sector</label>
<select name="sector" class="form-select">
<?php foreach ($sectores as $sector): ?>
<option value="<?= $sector ?>" <?= ($edificio['sector'] === $sector) ? 'selected' : '' ?>><?= ucfirst($sector) ?></option>
<?php endforeach; ?>
</select>
</div>

<!-- Predio -->
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

<!-- Institución -->
<div class="mb-3">
<label class="form-label">Institución</label>
<input type="text" name="institucion" class="form-control" value="<?= htmlspecialchars($edificio['institucion']) ?>">
</div>

<!-- Gestionado -->
<div class="mb-3">
<label class="form-label">Gestionado</label>
<select name="gestionado" class="form-select" required>
<option value="1" <?= ($edificio['gestionado']) ? 'selected' : '' ?>>Sí</option>
<option value="0" <?= (!$edificio['gestionado']) ? 'selected' : '' ?>>No</option>
</select>
</div>

<div class="text-center mt-4">
<button type="submit" name="confirmar" value="pendiente" class="btn btn-success">Guardar Cambios</button>
</div>
</form>
<?php endif; ?>

<?php
// Confirmación de cambios
if (isset($_POST['confirmar']) && $_POST['confirmar'] === 'pendiente' && isset($_SESSION['original_edificio'])):
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
            $cambios[$campo] = ['de' => $original[$campo], 'a' => $nuevo];
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
if ($campo === 'estado') echo $estadosMap[$valores['de']] ?? '[Sin valor]';
elseif ($campo === 'predio_id') echo $prediosMap[$valores['de']] ?? '[Sin predio]';
elseif ($campo === 'gestionado') echo $valores['de'] ? 'Sí' : 'No';
else echo ($valores['de'] === null || $valores['de'] === '') ? '[Campo sin datos]' : htmlspecialchars($valores['de']);
?>
</code> a <code>
<?php
if ($campo === 'estado') echo $estadosMap[$valores['a']] ?? '[Sin valor]';
elseif ($campo === 'predio_id') echo $prediosMap[$valores['a']] ?? '[Sin predio]';
elseif ($campo === 'gestionado') echo $valores['a'] ? 'Sí' : 'No';
else echo ($valores['a'] === null || $valores['a'] === '') ? '[Campo sin datos]' : htmlspecialchars($valores['a']);
?>
</code>
</li>
<?php endforeach; ?>
</ul>

<?php if (!empty($cambios)): ?>
<form method="POST">
<?php foreach ($_POST as $key => $value): ?>
<input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>">
<?php endforeach; ?>
<button type="submit" name="confirmar" value="si" class="btn btn-success me-2">Sí, cambiar</button>
<button type="submit" name="confirmar" value="no" class="btn btn-secondary">No, volver</button>
</form>
<?php else: ?>
<div class="alert alert-info">No hay cambios para guardar.</div>
<?php endif; ?>
</div>
<?php endif; ?>

</main>
<?php include('../includes/footer.php'); ?>
<script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
