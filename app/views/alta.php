<?php 
session_start(); 
require_once('../config/config.php'); 
?>

<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>UEICEE : MAPA : CUIS : Alta de Edificio</title>
  <link href="../css/bootstrap.min.css" rel="stylesheet">
  <link href="../css/sticky.css" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body class="d-flex flex-column min-vh-100">
<?php include('../includes/navbar.php'); ?>
<main class="flex-grow-1 container py-5">

  <form id="formDireccion" class="row g-3 mb-4 mt-4">
    <div class="col-md-6">
      <label for="calle" class="form-label">Calle</label>
      <input type="text" class="form-control" id="calle" required>
    </div>
    <div class="col-md-3">
      <label for="altura" class="form-label">Altura</label>
      <input type="number" class="form-control" id="altura" required>
    </div>
    <div class="col-md-3 d-flex align-items-end">
      <button type="submit" class="btn btn-primary w-100">Buscar</button>
    </div>
  </form>

  <div id="resultado" class="mt-4">
    <!-- Se mostrará el contenido aquí -->
  </div>


   <!-- Div para comentarios y observaciones -->
   <div class="mt-3 p-3 border border-warning rounded bg-light">
              <h6 class="text-warning">Pendientes:</h6>
              <ul class="mb-0">
                <li>Crear la query de insert.</li>
              </ul>
            </div>




</main>
<?php include('../includes/footer.php'); ?>
<script>
document.getElementById('formDireccion').addEventListener('submit', async function(e) {
  e.preventDefault();
  const calleInput = document.getElementById('calle').value.trim();
  const alturaInput = document.getElementById('altura').value.trim();
  const resultado = document.getElementById('resultado');
  resultado.innerHTML = "<p>Buscando dirección...</p>";

  try {
    const url1 = `https://ws.usig.buenosaires.gob.ar/rest/normalizar_direcciones?calle=${encodeURIComponent(calleInput)}&altura=${encodeURIComponent(alturaInput)}&desambiguar=1`;
    const res1 = await fetch(url1);
    const json1 = await res1.json();

    const direcciones = json1?.DireccionesCalleAltura?.direcciones;
    if (!direcciones || direcciones.length === 0) {
      resultado.innerHTML = `
        <div class="alert alert-warning">
          No se encontró la dirección.<br> 
          <a href="altamapa.php" class="btn btn-sm btn-outline-primary mt-2">Ingresar manualmente desde un mapa</a>
        </div>
      `;
      return;
    }

    const dir = direcciones[0];
    const codigoCalle = dir.CodigoCalle;
    const calleJson = dir.Calle;
    const alturaJson = dir.Altura;

    // API 2 - Datos útiles
    const url2 = `https://ws.usig.buenosaires.gob.ar/datos_utiles?calle=${encodeURIComponent(calleJson)}&altura=${encodeURIComponent(alturaJson)}`;
    const res2 = await fetch(url2);
    const datosUtiles = await res2.json();

    // API 3 - Coordenadas Gauss-Krüger
    const url3 = `https://ws.usig.buenosaires.gob.ar/geocoder/2.2/geocoding?cod_calle=${codigoCalle}&altura=${alturaJson}&metodo=puertas`;
    const res3 = await fetch(url3);
    const texto3 = await res3.text();
    const limpio3 = texto3.replace(/^\(/, '').replace(/\)$/, '');
    const coordGK = JSON.parse(limpio3);
    const xGK = coordGK.x;
    const yGK = coordGK.y;

    // API 4 - Convertir a lon/lat
    const url4 = `https://ws.usig.buenosaires.gob.ar/rest/convertir_coordenadas?x=${xGK}&y=${yGK}&output=lonlat`;
    const res4 = await fetch(url4);
    const coordGeo = await res4.json();
    const xLon = coordGeo.resultado?.x;
    const yLat = coordGeo.resultado?.y;

    // Mostrar los resultados
    resultado.innerHTML = `
      <div class="row">
        <!-- Bloque de resultados de las APIs -->
        <div class="col-md-6">
          <h4>Datos de la Dirección</h4>
          <ul class="list-group">
            <li class="list-group-item"><strong>Calle ingresada:</strong> ${calleInput}</li>
            <li class="list-group-item"><strong>Altura ingresada:</strong> ${alturaInput}</li>
            <li class="list-group-item"><strong>Código de calle:</strong> ${codigoCalle}</li>
            <li class="list-group-item"><strong>Calle normalizada:</strong> ${calleJson}</li>
            <li class="list-group-item"><strong>Altura normalizada:</strong> ${alturaJson}</li>
            <li class="list-group-item"><strong>Comuna:</strong> ${datosUtiles.comuna || '<em>No disponible</em>'}</li>
            <li class="list-group-item"><strong>Barrio:</strong> ${datosUtiles.barrio || '<em>No disponible</em>'}</li>
            <li class="list-group-item"><strong>Comisaría:</strong> ${datosUtiles.comisaria || '<em>No disponible</em>'}</li>
            <li class="list-group-item"><strong>Área hospitalaria:</strong> ${datosUtiles.area_hospitalaria || '<em>No disponible</em>'}</li>
            <li class="list-group-item"><strong>Región sanitaria:</strong> ${datosUtiles.region_sanitaria || '<em>No disponible</em>'}</li>
            <li class="list-group-item"><strong>Distrito escolar:</strong> ${datosUtiles.distrito_escolar || '<em>No disponible</em>'}</li>
            <li class="list-group-item"><strong>Comisaría vecinal:</strong> ${datosUtiles.comisaria_vecinal || '<em>No disponible</em>'}</li>
            <li class="list-group-item"><strong>Sección catastral:</strong> ${datosUtiles.seccion_catastral || '<em>No disponible</em>'}</li>
            <li class="list-group-item"><strong>Código postal:</strong> ${datosUtiles.codigo_postal || '<em>No disponible</em>'}</li>
            <li class="list-group-item"><strong>CPA:</strong> ${datosUtiles.codigo_postal_argentino || '<em>No disponible</em>'}</li>
            <li class="list-group-item"><strong>Coordenadas Gauss-Krüger:</strong> X: ${xGK}, Y: ${yGK}</li>
            <li class="list-group-item"><strong>Coordenadas Geográficas:</strong> Long: ${xLon}, Lat: ${yLat}</li>
          </ul>
        </div>

        <!-- Bloque del mapa -->
        <div class="col-md-6">
          <h4>Mapa</h4>
          <div id="map" style="height: 400px;"></div>
        </div>
      </div>
    `;

    // Crear el mapa solo si se encontró la dirección
    const map = L.map('map').setView([yLat, xLon], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    L.circle([yLat, xLon], {
      color: 'red',
      radius: 10,
    }).addTo(map).bindPopup(`<b>Dirección:</b><br>${calleJson} ${alturaJson}`).openPopup();

  } catch (error) {
    console.error(error);
    resultado.innerHTML = `
      <div class="alert alert-danger">
        Ocurrió un error al consultar la dirección. Inténtalo nuevamente.
      </div>
    `;
  }
});
</script>
</body>
</html>
