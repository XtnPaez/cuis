<?php
session_start();
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
  <?php include('../includes/navbar.php'); ?>

  <main class="container mt-5 pt-5 flex-grow-1">
    <h2 class="text-center mb-4">Gestión de aulas</h2>
    <div class="row row-cols-1 row-cols-md-3 g-4">
      <!-- Card 1: Generar QRs -->
      <div class="col">
        <div class="card h-100 shadow-sm">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title">Generar QR de aulas</h5>
            <p class="card-text">Se generan los QR para todas las aulas y se almacena el archivo en una carpeta del sistema.</p>
            <button type="button" class="btn btn-primary mt-auto w-100" id="btnGenerarQRs">Generar QRs</button>
          </div>
        </div>
      </div>
      <!-- Card 2: Listar QRs -->
      <div class="col">
        <div class="card h-100 shadow-sm">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title">Listar QR de aulas</h5>
            <p class="card-text">Se genera la lista de todos los QR con la referencia CUI -> aula.</p>
            <button type="button" class="btn btn-primary mt-auto w-100" id="btnListarQRs">Listar QRs</button>
          </div>
        </div>
      </div>
      <!-- Card 3: Buscar QR por CUI -->
      <div class="col">
        <div class="card h-100 shadow-sm">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title">Buscar QR por CUI</h5>
            <p class="card-text">Muestra el formulario y trae los resultados a esta página.</p>
            <button type="button" class="btn btn-primary mt-auto w-100" id="btnBuscarQR">Buscar QR por CUI</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Contenedor para resultados -->
    <div id="resultadoQRs" class="mt-5"></div>

    
  </main>

  <?php include('../includes/footer.php'); ?>
  <script src="../js/bootstrap.bundle.min.js"></script>

  <!-- JS Funcionalidades -->
  <script>
  document.addEventListener('DOMContentLoaded', function() {

    // ===== Generar QRs =====
    document.getElementById('btnGenerarQRs').addEventListener('click', function() {
      const btn = this;
      btn.innerHTML = 'Generando... <span class="spinner-border spinner-border-sm"></span>';
      btn.disabled = true;

      fetch('../ajax/generar_qrs.php')
        .then(r => r.text())
        .then(html => {
          document.body.insertAdjacentHTML('beforeend', html);
          const modal = new bootstrap.Modal(document.getElementById('resultadoModal'));
          modal.show();
          btn.innerHTML = 'Generar QRs';
          btn.disabled = false;

          document.getElementById('resultadoModal').addEventListener('hidden.bs.modal', function () {
            this.remove();
          });
        })
        .catch(err => {
          console.error(err);
          btn.innerHTML = 'Generar QRs';
          btn.disabled = false;
          alert('Error al generar QRs.');
        });
    });

    // ===== Listar / Buscar QRs =====
    const resultadoDiv = document.getElementById('resultadoQRs');
    let filtroCUI = '';

    function cargarQRs(page = 1, cui = '') {
      resultadoDiv.innerHTML = '<div class="text-center"><div class="spinner-border text-primary"></div> Cargando...</div>';
      filtroCUI = cui;

      const url = cui ? `../ajax/listar_qrs.php?cui=${encodeURIComponent(cui)}&page=${page}` 
                      : `../ajax/listar_qrs.php?page=${page}`;

      fetch(url)
        .then(r => r.text())
        .then(html => resultadoDiv.innerHTML = html)
        .catch(err => {
          console.error(err);
          resultadoDiv.innerHTML = '<div class="alert alert-danger">Error al cargar los QRs.</div>';
        });
    }

    // Botón Listar QRs
    document.getElementById('btnListarQRs').addEventListener('click', function() {
      cargarQRs(1);
    });

    // Botón Buscar CUI
    const modalBuscar = document.getElementById('modalBuscarCUI');
    document.getElementById('btnBuscarQR').addEventListener('click', function() {
      const modal = new bootstrap.Modal(modalBuscar);
      modal.show();
    });

    document.getElementById('btnBuscarCUIConfirm').addEventListener('click', function() {
      const cui = document.getElementById('inputCUI').value.trim();
      if(!cui){ alert('Ingrese un CUI válido'); return; }
      cargarQRs(1, cui);
      const modal = bootstrap.Modal.getInstance(modalBuscar);
      if(modal) modal.hide();
    });

    // Función para paginado (usada en select)
    window.cargarPagina = function(page) {
      cargarQRs(page, filtroCUI);
    }

  });
  </script>

  <!-- Modal Buscar CUI -->
  <div class="modal fade" id="modalBuscarCUI" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Buscar QR por CUI</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="inputCUI" class="form-label">Ingrese CUI:</label>
            <input type="text" class="form-control" id="inputCUI" placeholder="Ej: 123456">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-primary" id="btnBuscarCUIConfirm">Buscar</button>
        </div>
      </div>
    </div>
  </div>
</body>
</html>