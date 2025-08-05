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
    <title>CUIS : Inicio</title>
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
      <h2 class="text-center mb-5 mt-4">Relación CUI > CUEANEXO > CUEANEXO PADRÓN NACIÓN</h2>
      <div class="row row-cols-1 row-cols-md-3 g-4">
        <!-- Tarjeta 1: Listado CUI - CUEANEXO - CUANEXO PADRON NACION -->
        <div class="col">
          <div class="card h-100 shadow-sm">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title">Listado</h5>
              <p class="card-text">Listado comparativo entre CUI > CUEANEXO > CUANEXO PADRÓN NACIÓN.</p>
              <a href="#" class="btn btn-primary mt-auto w-100">Generar listado</a>
            </div>
          </div>
        </div>
        <!-- Tarjeta 2: Listado de inconsistencias -->
        <div class="col">
          <div class="card h-100 shadow-sm">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title">Inconsistencias</h5>
              <p class="card-text">Listado de inconsistencias entre CUI > CUEANEXO > CUANEXO PADRÓN NACIÓN.</p>
              <a href="#" class="btn btn-primary mt-auto w-100">Generar listado</a>
            </div>
          </div>
        </div>      
        <!-- Tarjeta 3: Listado de cambios -->
        <div class="col">
          <div class="card h-100 shadow-sm">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title">Registro de cambios</h5>
              <p class="card-text">Listado de los cambios y acciones entre CUI > CUEANEXO > CUANEXO PADRÓN NACIÓN.</p>
              <a href="#" class="btn btn-primary mt-auto w-100">Generar listado</a>
            </div>
          </div>
        </div>
      </div>
      <br>
      <!-- Tarjeta 4: Registro de actividad -->
        <div class="col">
          <div class="card h-100 shadow-sm">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title">Registro de actividad</h5>
              <p class="card-text">Formulario para registrar toda la actividad vinculada a CUI.</p>
              <a href="#" class="btn btn-primary mt-auto w-100">Abrir el formulario</a>
            </div>
          </div>
        </div>
      <!-- Pendientes -->
      <div class="mt-3 p-3 border border-warning rounded bg-light">
        <h6 class="text-warning">Pendientes:</h6>
        <ul class="mb-0">
          <li>Botón: Generar listado CUI - CUANEXO - CUANEXO PNAC.</li>
          <li>Botón: Generar listado paginado de inconsistencias entre CUI - CUANEXO - CUANEXO PNAC.</li>
          <li>Botón: Registrar actualizaciones del listado. Formulario con combos de CUI - CUANEXO - ACCION (Informe a Padrón - Modificación de relación entre CUI y CUEANEXO). Regitrar el cambio y el usuario en la base.</li>
          <li>Botón: Listar cambios. Todos, por fecha, por CUI, por CUEANEXO, por usuario.</li>
        </ul>
      </div>
      <!-- termina pendientes -->
    </main>
    <!-- Traigo footer -->
    <?php include('../includes/footer.php'); ?>
    <script src="../js/bootstrap.bundle.min.js"></script>
  </body>
</html>