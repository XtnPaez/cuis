<?php 
  session_start();
  require_once('../config/config.php'); 
  $resultado = null;
  $error = null;

  if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cui'])) {
      $cui = $_POST['cui'];
      $sql = "SELECT cui, estado, sector, gestionado, x_gkba AS x, y_gkba AS y FROM cuis.edificios WHERE id = :cui";
      $stmt = $pdo->prepare($sql);
      $stmt->bindParam(':cui', $cui, PDO::PARAM_INT);
      $stmt->execute();
      $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
      if (!$resultado) {
          $error = "No se encontró el CUI ingresado.";
      }
  }
?>

<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CUIS</title>
  <link href="../css/bootstrap.min.css" rel="stylesheet">
  <link href="../css/sticky.css" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <!-- Favicons -->
  <link rel="icon" href="../images/favicon-32x32.png" sizes="32x32" type="image/png">
</head>
<body class="d-flex flex-column min-vh-100">
  <?php include('../includes/navbar.php'); ?>

  <main class="flex-grow-1 container py-4">
  </main>

  <?php include('../includes/footer.php'); ?>
</body>
</html>
