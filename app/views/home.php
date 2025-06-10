<?php
  // chequeo inicio de sesión
  session_start();
  // traigo la conexion
  require_once('../config/config.php'); 
?>
<!doctype html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UEICEE : MAPA : CUIS : Home</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/sticky.css" rel="stylesheet">
    <link rel="apple-touch-icon" href="../images/apple-icon-180x180.png" sizes="180x180">
    <link rel="icon" href="../images/favicon-32x32.png" sizes="32x32" type="image/png">
    <link rel="icon" href="../images/favicon-16x16.png" sizes="16x16" type="image/png">
    <link rel="icon" href="../images/favicon.ico">
  </head>
  <body class="d-flex flex-column min-vh-100">
    <!-- Traigo navbar -->
    <?php include('../includes/navbar.php'); ?>
    <main class="container mt-5 pt-5 flex-grow-1">
      <div class="row row-cols-1 row-cols-md-3 g-4">
        <!-- Tarjeta 1: Buscar CUI -->
        <div class="col">
          <div class="card h-100 shadow-sm">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title">Buscar CUI</h5>
              <p class="card-text">Ingresá un Código Único de Infraestructura (CUI) o una Dirección y accedé a toda la información disponible del edificio educativo.</p>
              <a href="buscar.php" class="btn btn-primary mt-auto w-100">Ir a buscar</a>
            </div>
          </div>
        </div>
        <!-- Tarjeta 2: Editar CUI -->
        <div class="col">
          <div class="card h-100 shadow-sm">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title">Editar CUI</h5>
              <p class="card-text">Modificá los datos de un edificio educativo ingresando su CUI.</p>
              <a href="editar.php" class="btn btn-primary mt-auto w-100">Ir a editar</a>
            </div>
          </div>
        </div>
        <!-- Tarjeta 3: Alta de CUI -->
        <div class="col">
          <div class="card h-100 shadow-sm">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title">Alta de CUI</h5>
              <p class="card-text">Dar el Alta de un nuevo edificio educativo.</p>
              <a href="alta.php" class="btn btn-primary mt-auto w-100">Ir a alta</a>
            </div>
          </div>
        </div>
        <!-- Tarjeta 4: Gestión de Tablas -->
        <div class="col">
          <div class="card h-100 shadow-sm">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title">Gestión de Tablas</h5>
              <p class="card-text">ABM de tablas complementarias como Predios, Operativos, Direcciones u Observaciones.</p>
              <a href="actualizaciones.php" class="btn btn-primary mt-auto w-100">Ir a actualizaciones</a>
            </div>
          </div>
        </div>
        <!-- Tarjeta 5: Descargas -->
        <div class="col">
          <div class="card h-100 shadow-sm">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title">Descargas</h5>
              <p class="card-text">Accedé a reportes o archivos relevantes del sistema.</p>
              <a href="descargas.php" class="btn btn-primary mt-auto w-100">Ir a descargas</a>
            </div>
          </div>
        </div>
        <!-- Tarjeta 6: Usuarios -->
        <div class="col">
          <div class="card h-100 shadow-sm">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title">Gestión de Usuarios</h5>
              <p class="card-text">ABM de Usuarios (Sólo para Superadmins).</p>
              <a href="usuarios.php" class="btn btn-primary mt-auto w-100">Ir a usuarios</a>
            </div>
          </div>
        </div>
        <!-- Tarjeta 7: Mapa -->
        <div class="col">
          <div class="card h-100 shadow-sm">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title">Mapa</h5>
              <p class="card-text">Visualizador de Capas geográficas de MAPA.</p>
              <a href="mapa.php" class="btn btn-primary mt-auto w-100">Ir a mapa</a>
            </div>
          </div>
        </div>
      </div>
    </main>
    <!-- Traigo footer -->
    <?php include('../includes/footer.php'); ?>
  </body>
</html>