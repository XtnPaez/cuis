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
    <!-- Título encima del mapa -->
    <h6 class="text-center mb-4 mt-4">Ingrese en el mapa el punto que busca</h6>
    
    <!-- Mapa -->
    <div id="map" style="height: 400px;"></div>

    <!-- Coordenadas -->
    <div class="mt-3">
      <p><strong>Coordenadas:</strong> <span id="latLng">Latitud: -, Longitud: -</span></p>
      <p><strong>Puerta:</strong> <span id="address">No disponible</span></p>
      <p><strong>Calle:</strong> <span id="calle">No disponible</span></p>
      <p><strong>Altura:</strong> <span id="altura">No disponible</span></p>
      <p><strong>Parcela:</strong> <span id="parcela">No disponible</span></p>
      <p><strong>Puerta XGK:</strong> <span id="puerta_x">No disponible</span></p>
      <p><strong>Puerta YGK:</strong> <span id="puerta_y">No disponible</span></p>
      <p><strong>Calle y Alturas:</strong> <span id="calle_alturas">No disponible</span></p>
    </div>
    <!-- Div para comentarios y observaciones -->
    <div class="mt-3 p-3 border border-warning rounded bg-light">
              <h6 class="text-warning">Pendientes:</h6>
              <ul class="mb-0">
                <li>YA CONSEGUÍ PARSEAR LA DIRECCION -> Ahora hay que traer los datos de las apis y mostrarlos igual que con direccion.</li>
                <li>El campo direccion deberia ser editable para cargar lo que padron nos mande. Poner Observaciones.</li>
              </ul>
            </div>
           
  </main>
  <?php include('../includes/footer.php'); ?>
  <script>
    // Inicialización del mapa con OpenStreetMap
    var map = L.map('map').setView([-34.6037, -58.3816], 13);  // Centrado en Buenos Aires
/*
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

*/

    L.tileLayer('https://servicios.usig.buenosaires.gob.ar/mapcache/tms/1.0.0/amba_con_transporte_3857@GoogleMapsCompatible/{z}/{x}/{-y}.png ', {
      attribution: '&copy; CABA Tiles'
    }).addTo(map);


function separarCalleYAltura(direccion) {
  if (!direccion) return { calle: null, altura: null };

  const match = direccion.trim().match(/^(.*\D)\s+(\d+|S\/N)$/);
  if (match) {
    return {
      calle: match[1].trim(),
      altura: match[2]
    };
  } else {
    return {
      calle: direccion.trim(),
      altura: null
    };
  }
}



    // Agregar un marcador y un popup cuando se haga clic en el mapa
    map.on('click', function(e) {
      var lat = e.latlng.lat;
      var lng = e.latlng.lng;

      // Mostrar las coordenadas debajo del mapa
      document.getElementById("latLng").textContent = "Latitud: " + lat.toFixed(6) + ", Longitud: " + lng.toFixed(6);

      // Crear un popup con las coordenadas
      L.popup()
        .setLatLng(e.latlng)
        .setContent('Latitud: ' + lat.toFixed(6) + '<br>Longitud: ' + lng.toFixed(6))
        .openOn(map);

      // Llamar a la API para obtener la dirección
      fetch(`https://datosabiertos-usig-apis.buenosaires.gob.ar/geocoder/2.2/reversegeocoding?x=${lng}&y=${lat}`)
        .then(response => response.text())  // Cambiar a .text() para manipular el string antes de parsear
        .then(data => {
          // Eliminar los paréntesis alrededor del JSON
          var jsonString = data.replace(/^[(]|[)]$/g, '');  // Elimina los paréntesis del principio y del final

          // Parsear el JSON limpio
          return JSON.parse(jsonString);
        })
        .then(jsonData => {
          // Mostrar los datos de la API debajo de las coordenadas
          document.getElementById("address").textContent = jsonData.puerta || 'No disponible';
          // Separar calle y altura
const separados = separarCalleYAltura(jsonData.puerta);

// Mostrar calle y altura separados
document.getElementById("calle").textContent = separados.calle || 'No disponible';
document.getElementById("altura").textContent = separados.altura || 'No disponible';

          document.getElementById("parcela").textContent = jsonData.parcela || 'No disponible';
          document.getElementById("puerta_x").textContent = jsonData.puerta_x || 'No disponible';
          document.getElementById("puerta_y").textContent = jsonData.puerta_y || 'No disponible';
          document.getElementById("calle_alturas").textContent = jsonData.calle_alturas || 'No disponible';
        })
        .catch(error => {
          console.error('Error al obtener los datos de la API:', error);
          // Mostrar mensaje de error en el HTML
          document.getElementById("address").textContent = "Error al obtener los datos.";
        });
    });
  </script>
</body>
</html>
