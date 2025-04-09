<?php 
  session_start();
  require_once('../config/config.php'); 
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CUIS</title>
  <link href="../css/bootstrap.min.css" rel="stylesheet">
  <link href="../css/sticky.css" rel="stylesheet">
  <!-- Favicons -->
  <link rel="apple-touch-icon" href="../images/apple-icon-180x180.png" sizes="180x180">
  <link rel="icon" href="../images/favicon-32x32.png" sizes="32x32" type="image/png">
  <link rel="icon" href="../images/favicon-16x16.png" sizes="16x16" type="image/png">
  <link rel="icon" href="../images/favicon.ico">
</head>
<body class="d-flex flex-column min-vh-100">
  <?php include('../includes/navbar.php'); ?>
  <main class="flex-grow-1 container text-center d-flex align-items-center justify-content-center">
    <h1>Bienvenido a la app</h1>
  </main>
  <?php include('../includes/footer.php'); ?>
</body>
</html>