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
      <div class="row row-cols-1 row-cols-md-3 g-4">
        <!-- Tarjeta 1: Buscar CUI por Código -->
        <div class="col">
          <div class="card h-100 shadow-sm">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title">Buscar CUI por Código</h5>
              <p class="card-text">Ingresá un Código Único de Infraestructura (CUI) o una Dirección y accedé a toda la información disponible del edificio educativo.</p>
              <a href="buscarcuixcodigo.php" class="btn btn-primary mt-auto w-100">Ir a buscar por código</a>
            </div>
          </div>
        </div>
        <!-- Tarjeta 2: Buscar CUI por Calle y Altura -->
        <div class="col">
          <div class="card h-100 shadow-sm">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title">Buscar CUI por Calle y Altura</h5>
              <p class="card-text">Ingresá Calle y Altura y accedé a la info del CUI asociado a ella. Si no la encontramos, te vamos a mostrar CUIs en un radio de 100 mts.</p>
              <a href="buscarcuixcallealtura.php" class="btn btn-primary mt-auto w-100">Ir a buscar por calle y altura</a>
            </div>
          </div>
        </div>
        <!-- Tarjeta 3: Editar CUI -->
        <div class="col">
          <div class="card h-100 shadow-sm">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title">Editar CUI</h5>
              <p class="card-text">Modificá los datos de un edificio educativo ingresando su CUI.</p>
              <a href="editar.php" class="btn btn-primary mt-auto w-100">Ir a editar CUI</a>
            </div>
          </div>
        </div>
        <!-- Tarjeta 4: Alta de CUI por dirección -->
        <div class="col">
          <div class="card h-100 shadow-sm">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title">Alta de CUI por Dirección</h5>
              <p class="card-text">Dar el Alta de un nuevo edificio educativo a partir de la dirección encontrada en la API de CABA.</p>
              <a href="altaxdireccion.php" class="btn btn-primary mt-auto w-100">Ir a dar de alta un CUI por dirección</a>
            </div>
          </div>
        </div>
        <!-- Tarjeta 5: Alta de CUI por mapa -->
        <div class="col">
          <div class="card h-100 shadow-sm">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title">Alta de CUI por Mapa</h5>
              <p class="card-text">Dar el Alta de un nuevo edificio educativo ingresando la ubicación en un mapa (cuando no se encuentra la dirección exacta en la API de CABA).</p>
              <a href="altaxmapa.php" class="btn btn-primary mt-auto w-100">Ir a dar de alta un CUI por mapa</a>
            </div>
          </div>
        </div>
        <!-- Tarjeta 6: Gestión de Tablas de Dominio -->
        <div class="col">
          <div class="card h-100 shadow-sm">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title">Gestión de Tablas de Dominio</h5>
              <p class="card-text">ABM de tablas de Dominio como Predios, Operativos, Direcciones u Observaciones.</p>
              <a href="actualizaciones.php" class="btn btn-primary mt-auto w-100">Ir a actualizaciones</a>
            </div>
          </div>
        </div>
        <!-- Tarjeta 7: Descargas -->
        <div class="col">
          <div class="card h-100 shadow-sm">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title">Descargas</h5>
              <p class="card-text">Accedé a reportes o archivos relevantes del sistema.</p>
              <a href="descargas.php" class="btn btn-primary mt-auto w-100">Ir a descargas</a>
            </div>
          </div>
        </div>
        <!-- Tarjeta 8: Usuarios -->
        <div class="col">
          <div class="card h-100 shadow-sm">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title">Gestión de Usuarios</h5>
              <p class="card-text">ABM de Usuarios (Sólo para Superadmins).</p>
              <a href="usuarios.php" class="btn btn-primary mt-auto w-100">Ir a usuarios</a>
            </div>
          </div>
        </div>
        <!-- Tarjeta 9: Mapa -->
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
      <!-- Pendientes -->
      <div class="mt-3 p-3 border border-warning rounded bg-light">
        <h6 class="text-warning">Pendientes:</h6>
        <ul class="mb-0">
          <li>Para el MVP, falta Descargas y Alta de CUI (que es lo último que vamos a hacer).</li>
          <li>Para el productivo: poblar las direcciones sin datos porque la API no las encuentra, pero tenemos el punto de coordenadas.</li>
          <li>Para el productivo: pensar en un visualizador para corregir posiciones geográficas de los CUI. PARECE que a veces están en parcelas vecinas.</li>
          <li>Para el productivo: pensar en un visualizador para corregir posiciones geográficas de Direcciones. PARECE que a veces están sobre CUI.</li>
          <li>Para el productivo: poblar la tabla puertas. No es indispensable, pero si alguna vez podemos saber qué hay en cada puerta, vendría bien.</li>
        </ul>
      </div>
      
      <!-- termina pendientes -->
    </main>
    <!-- Traigo footer -->
    <?php include('../includes/footer.php'); ?>
    <script src="../js/bootstrap.bundle.min.js"></script>
  </body>
</html>