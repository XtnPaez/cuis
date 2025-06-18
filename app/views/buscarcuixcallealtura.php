<?php
require_once '../config/config.php';
require_once '../includes/funciones_busqueda.php';
?>
<!doctype html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UEICEE : MAPA : CUIS : Buscar</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/sticky.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link rel="apple-touch-icon" href="../images/apple-icon-180x180.png" sizes="180x180">
    <link rel="icon" href="../images/favicon-32x32.png" sizes="32x32" type="image/png">
    <style>
    #map { height: 400px; }
  </style>
</head>
<body class="d-flex flex-column min-vh-100">

<?php include '../includes/navbar.php'; ?>

<main class="flex-grow-1 container py-5">
  <h2 class="text-center mb-5 mt-4">Buscar CUI por Calle y Altura</h2>

  <!-- Formulario -->
  <form id="form-busqueda" class="row g-3 mb-4">
    <div class="col-md-6">
      <input type="text" class="form-control" id="calle" name="calle" placeholder="Calle" list="sugerencias" autocomplete="off" required>
      <datalist id="sugerencias"></datalist>
    </div>
    <div class="col-md-3">
      <input type="number" class="form-control" id="altura" name="altura" placeholder="Altura" required>
    </div>
    <div class="col-md-3 d-flex align-items-end">
      <button type="submit" class="btn btn-primary w-100">Buscar</button>
    </div>
  </form>

<!-- Mensajes de error o info -->
<div id="mensaje" class="alert d-none" role="alert"></div>



<!-- Mapa -->
<div id="map" class="mb-4 rounded shadow-sm" style="height: 400px;"></div>


  <!-- Detalle -->
  <div id="detalle-cui" class="mt-4"></div>

  <!-- Pendientes -->
      <div class="mt-3 p-3 border border-warning rounded bg-light">
        <h6 class="text-warning">Pendientes:</h6>
        <ul class="mb-0">
          <li>Revisar lo de los 100 metros.</li>
        </ul>
      </div><!-- termina pendientes -->


</main>

<?php include '../includes/footer.php'; ?>

<script>
  function mostrarMensaje(tipo, texto) {
  const div = document.getElementById('mensaje');
  div.className = 'alert d-block'; // reset
  switch (tipo) {
    case 'exito':     // verde
      div.classList.add('alert-success');
      break;
    case 'advertencia': // amarillo
      div.classList.add('alert-warning');
      break;
    case 'error':     // rojo
      div.classList.add('alert-danger');
      break;
  }
  div.innerHTML = texto;
}
</script>


<script>
document.getElementById('calle').addEventListener('input', function () {
  const calleInput = this.value;

  if (calleInput.length < 2) return;

  fetch(`autocomplete_calle.php?term=${encodeURIComponent(calleInput)}`)
    .then(response => response.json())
    .then(data => {
      const dataList = document.getElementById('sugerencias');
      dataList.innerHTML = '';
      data.forEach(calle => {
        const option = document.createElement('option');
        option.value = calle;
        dataList.appendChild(option);
      });
    });
});
</script>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="../js/busqueda_direccion.js"></script>
<script src="../js/bootstrap.bundle.min.js"></script>

</body>
</html>
