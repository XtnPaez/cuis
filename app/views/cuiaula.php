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
    <title>CUIS : CUIaula</title>
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
        <h2 class="text-center mb-4">Gestión de aulas</h2>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <!-- Tarjeta 1: Generar QRs -->
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Generar QR de aulas</h5>
                        <p class="card-text">Se generan los QR para todas las aulas y se almacena el archivo en una carpeta del sistema.</p>
                        <button type="button" class="btn btn-primary mt-auto w-100" id="btnGenerarQRs">Generar QRs</button>
                    </div>
                </div>
            </div>
            <!-- Tarjeta 2: Listar QRs -->
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Listar QR de aulas</h5>
                        <p class="card-text">Se genera la lista de todos los QR con la referencia CUI -> aula.</p>
                        <a href="#" class="btn btn-primary mt-auto w-100">Listar QRs</a>
                    </div>
                </div>
            </div>
            <!-- Tarjeta 3: Buscar QR por CUI -->
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Buscar QR por CUI</h5>
                        <p class="card-text">Muestra el formulario y trae los resultados a esta página.</p>
                        <a href="#" class="btn btn-primary mt-auto w-100">Buscar QR por CUI</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Pendientes -->
        <div class="mt-3 p-3 border border-warning rounded bg-light">
            <h6 class="text-warning">Pendientes:</h6>
            <ul class="mb-0">
                <li>Funcionalidad de las cards</li>
                <li>Generar QR debe generar solo para aquellos que no existan aun. Un flag en la tabla. Muestra un modal con el OK.</li>
                <li>Listar debe ser aqui mismo, paginado.</li>
                <li>Buscar QR por CUI</li>
            </ul>
        </div>      
        <!-- termina pendientes -->
    </main>
    <!-- Traigo footer -->
    <?php include('../includes/footer.php'); ?>
    <script src="../js/bootstrap.bundle.min.js"></script>
    <!-- Genera QRs -->
     <script>
        document.getElementById('btnGenerarQRs').addEventListener('click', function() {
            // Mostrar loading en el botón
            this.innerHTML = 'Generando... <span class="spinner-border spinner-border-sm" role="status"></span>';
            this.disabled = true;
            // Llamada AJAX
            fetch('../ajax/generar_qrs.php')
                .then(response => response.text())
                .then(html => {
                    // Inyectar modal en el DOM
                    document.body.insertAdjacentHTML('beforeend', html);
                    // Mostrar modal
                    const modal = new bootstrap.Modal(document.getElementById('resultadoModal'));
                    modal.show();
                    // Restaurar botón
                    this.innerHTML = 'Generar QRs';
                    this.disabled = false;
                    // Limpiar modal del DOM cuando se cierre
                    document.getElementById('resultadoModal').addEventListener('hidden.bs.modal', function () {
                        this.remove();
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    this.innerHTML = 'Generar QRs';
                    this.disabled = false;
                    alert('Error al generar QRs. Intenta nuevamente.');
                });
        });
    </script>
  </body>
</html>