<?php
session_start();
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
    <script src="../js/jquery.min.js"></script>
    <script src="../js/cuicueanexo.js"></script>
  </head>
  <body class="d-flex flex-column min-vh-100">
    <?php include('../includes/navbar.php'); ?>

    <main class="container mt-5 pt-5 flex-grow-1">
      <h2 class="text-center mb-5 mt-4">Relación CUI > CUEANEXO > CUEANEXO PADRÓN NACIÓN</h2>

      <!-- Card: Registro de actividad -->
      <div class="col mt-4">
        <div class="card h-100 shadow-sm">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title">Registro de actividad</h5>
            <p class="card-text">Formulario para registrar toda la actividad vinculada a CUI -> CUEANEXOS.</p>
            <a href="#" class="btn btn-primary mt-auto w-100">Abrir el formulario</a>
          </div>
        </div>
      </div>

      <br>

      <div class="row row-cols-1 row-cols-md-3 g-4">
        <!-- Card: Listado -->
        <div class="col">
          <div class="card h-100 shadow-sm">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title">Listado</h5>
              <p class="card-text">Listado de la relación CUI -> CUEANEXO para descargar.</p>
              <button id="btnListado" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalListado">Listado</button>
            </div>
          </div>
        </div>

        <!-- Card: Inconsistencias -->
        <div class="col">
          <div class="card h-100 shadow-sm">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title">Inconsistencias en EDDY</h5>
              <p class="card-text">Inconsistencias entre CUI -> CUEANEXO en la base de EDDY. Puede haber CUIs sin CUEANEXOs o viceversa.</p>
              <button id="btnInconsistencias" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalInconsistencias">Ver inconsistencias en EDDY</button>
            </div>
          </div>
        </div>

        <!-- Card: Registro de cambios -->
        <div class="col">
          <div class="card h-100 shadow-sm">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title">Registro de cambios</h5>
              <p class="card-text">Listado de los cambios y acciones entre CUI > CUEANEXO > CUANEXO PADRÓN NACIÓN.</p>
              <a href="#" class="btn btn-primary mt-auto w-100">Cambios</a>
            </div>
          </div>
        </div>
      </div>

      <!-- Bloque: Pendientes -->
      <div class="mt-4 p-3 border border-warning rounded bg-light">
        <h6 class="text-warning">Pendientes:</h6>
        <ul class="mb-0">
          <li>Vamos a repensar. Lo que queremos es ver la relación entre CUI y CUEANEXO en PADRON NACION?</li>
          <li>Que ningún CUEANEXO (PN) esté huérfano de CUI. Eso lo podemos chequear pero no lo podemos operar nosotros, hay que avisarle a PADRÓN</li>
          <li>(?) Generar modal para editar CUI - CUANEXO PADRÓN NACIÓN</li>
          <li>(?) Registrar actualizaciones del listado. Formulario con combos de CUI - CUANEXO - ACCION (Informe a Padrón - Modificación de relación entre CUI y CUEANEXO). Registrar el cambio y el usuario en la base.</li>
          <li>(?) Listar cambios. Todos, por fecha, por CUI, por CUEANEXO, por usuario.</li>
        </ul>
      </div>
    </main>

    <!-- Modal: Listado -->
    <div class="modal fade" id="modalListado" tabindex="-1" aria-labelledby="modalListadoLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalListadoLabel">Ejemplo de registros CUI -> CUEANEXO</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body" id="resultadoListado">
            <p>Cargando registros...</p>
          </div>
          <div class="modal-footer">
            <a href="../descargar/descargar_cui_cueanexo.php" class="btn btn-success">Descargar Excel Completo</a>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal: Inconsistencias -->
    <div class="modal fade" id="modalInconsistencias" tabindex="-1" aria-labelledby="modalInconsistenciasLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalInconsistenciasLabel">Inconsistencias internas en CUI -> CUEANEXO</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body" id="resultadoInconsistencias">
            <p>Cargando inconsistencias...</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>

    <?php include('../includes/footer.php'); ?>
    <script src="../js/bootstrap.bundle.min.js"></script>
  </body>
</html>