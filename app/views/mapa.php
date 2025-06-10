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
    <title>UEICEE : MAPA : CUIS : Mapa</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/sticky.css" rel="stylesheet">
    <link rel="apple-touch-icon" href="../images/apple-icon-180x180.png" sizes="180x180">
    <link rel="icon" href="../images/favicon-32x32.png" sizes="32x32" type="image/png">
    <link rel="icon" href="../images/favicon-16x16.png" sizes="16x16" type="image/png">
    <link rel="icon" href="../images/favicon.ico">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <style>
      #map {
        height: 666px;
        width: 100%;
      }
    </style>
  </head>
  <body class="d-flex flex-column min-vh-100">
    <!-- Traigo navbar -->
    <?php include('../includes/navbar.php'); ?>
    <main class="flex-grow-1 container py-5">
      <div id="map" class="mb-5 mt-4"></div>
      <!-- Div para comentarios y observaciones -->
      <div class="mt-3 p-3 border border-warning rounded bg-light">
        <h6 class="text-warning">Pendientes:</h6>
        <ul class="mb-0">
          <li>Agregar capas de Distrito Escolar, Barrios, Comunas.</li>
          <li>Mejorar las consultas para que los popups muestren "direccion principal" en CUI y "CUI" en Direcciones.</li>
          <li>Pensar en un visualizador para corregir posiciones geográficas de los CUI. PARECE que a veces están en parcelas vecinas.</li>
        </ul>
      </div><!-- termina pendientes -->
    </main>
    <!-- Traigo footer -->
    <?php include('../includes/footer.php'); ?>    
    <!-- Mapa -->
    <script src="../js/mapa.js"></script>
  </body>
</html>