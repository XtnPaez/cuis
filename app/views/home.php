<?php 
  session_start();
  require_once('../config/config.php'); // Asegúrate de tener la conexión a la DB
  require_once('../includes/navbar_footer.php'); // Incluye el navbar y el footer
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link href="./css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/sticky.css" rel="stylesheet">
  </head>
  <body class="d-flex flex-column h-100">
    <!-- Aquí va el contenido de tu página de inicio -->
    <main class="flex-shrink-0">
      <h1>Bienvenido a la app</h1>
      <!-- Otros contenidos -->
    </main>
  </body>
</html>
