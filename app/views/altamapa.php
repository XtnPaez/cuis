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
      <!-- SRID de GK 97434 -->
      <!-- Título encima del mapa -->
      <h6 class="text-center mb-4 mt-4">Ingrese en el mapa el punto que busca</h6>
      <!-- Mapa -->
      <div id="map" style="height: 400px;"></div>
      <!-- Panel de datos -->
      <div class="mt-3">
        <h2>Coordenadas del CUI</h2>
        <p><strong>Coordenadas CUI (WGS84):</strong> <span id="latLng">Latitud: -, Longitud: -</span></p>
        <p><strong>Coordenadas CUI (GK):</strong> <span id="latgkLnggk">Latitud: -, Longitud: -</span></p>
        <h2>Atributos de la dirección asociada</h2>
        <p><strong>Puerta:</strong> <span id="address">No disponible</span></p>
        <p><strong>Calle:</strong> <span id="calle">No disponible</span></p>
        <p><strong>Altura:</strong> <span id="altura">No disponible</span></p>
        <p><strong>Parcela:</strong> <span id="parcela">No disponible</span></p>
        <p><strong>Puerta XGK:</strong> <span id="puerta_x">No disponible</span></p>
        <p><strong>Puerta YGK:</strong> <span id="puerta_y">No disponible</span></p>
        <p><strong>Puerta XWGS84:</strong> <span id="puerta_xgk">No disponible</span></p>
        <p><strong>Puerta YWGS84:</strong> <span id="puerta_ygk">No disponible</span></p>
        <p><strong>Calle y Alturas:</strong> <span id="calle_alturas">No disponible</span></p>
        <p><strong>Comuna:</strong> <span id="comuna">No disponible</span></p>
        <p><strong>Barrio:</strong> <span id="barrio">No disponible</span></p>
        <p><strong>Comisaría:</strong> <span id="comisaria">No disponible</span></p>
        <p><strong>Hospital:</strong> <span id="hospital">No disponible</span></p>
        <p><strong>Región Sanitaria:</strong> <span id="region">No disponible</span></p>
        <p><strong>Código Postal:</strong> <span id="postal">No disponible</span></p>
      </div>
      <!-- Div para comentarios y observaciones -->
      <div class="mt-3 p-3 border border-warning rounded bg-light">
        <h6 class="text-warning">Pendientes:</h6>
        <ul class="mb-0">
          <li>Trae todos los datos para update de direccion? Cuando esto esté chequeado crear el formulario de update.</li>
          <li>Traer las coordenadas GK para el punto de CUI y WGS84 para la direccion. Despues elijamos cual registramos.</li>
          <li>El campo direccion deberia ser editable para cargar lo que padron nos mande. Poner Observaciones.</li>
        </ul>
      </div>
    </main>
    <?php include('../includes/footer.php'); ?>
    <script>
      // Inicialización del mapa con OpenStreetMap
      var map = L.map('map').setView([-34.6037, -58.3816], 13);
      // Tiles oficiales de CABA
      L.tileLayer('https://servicios.usig.buenosaires.gob.ar/mapcache/tms/1.0.0/amba_con_transporte_3857@GoogleMapsCompatible/{z}/{x}/{-y}.png ', {
        attribution: '&copy; CABA Tiles'
      }).addTo(map);
      // Funcion separarCalleYAltura para extraer calle y altura de una dirección
      function separarCalleYAltura(direccion) {
        // si no viene nada
        if (!direccion) return { 
          calle: null, 
          altura: null 
        };
        direccion = direccion.trim();
        // Si tiene " S/N" (sin número), tratamos como caso especial
        if (direccion.match(/\bS\/N\b/i)) {
          return {
            calle: direccion.replace(/\bS\/N\b/i, '').trim(),
            altura: 'S/N'
          };
        }
        // Buscar el último número que podría ser altura
        const partes = direccion.split(' ');
        for (let i = partes.length - 1; i >= 0; i--) {
          if (/^\d+$/.test(partes[i])) {
            const altura = partes[i];
            const calle = partes.slice(0, i).join(' ').trim();
            return {
              calle, 
              altura
            };
          }
        }
        // Si no se encontró número, devolver calle completa
        return {
          calle: direccion,
          altura: null
        };
      } // Termina separarCalleYAltura
      // Espero el onclick en el mapa
      map.on('click', function(e) {
        var lat = e.latlng.lat;
        var lng = e.latlng.lng;
        // Hago el popup con las coordenadas
        L.popup()
        .setLatLng(e.latlng)
        .setContent('Latitud: ' + lat.toFixed(6) + '<br>Longitud: ' + lng.toFixed(6))
        .openOn(map);
        // Mando el contenido de latLng al HTML
        document.getElementById("latLng").textContent = "Latitud: " + lat.toFixed(6) + ", Longitud: " + lng.toFixed(6);







        // Acá debería limpiar el contenido de los demás campos? Por ahora parece que funciona bien sin limpiar
    
        // Consulto la API reversegeocoding para obtener la dirección
        fetch(`https://datosabiertos-usig-apis.buenosaires.gob.ar/geocoder/2.2/reversegeocoding?x=${lng}&y=${lat}`)
        // Cambiar a .text() para manipular el string antes de parsear
        .then(response => response.text())  
        .then(data => {
          // Eliminar los paréntesis alrededor del JSON
          var jsonString = data.replace(/^[(]|[)]$/g, ''); 
          // Parsear el JSON limpio
          return JSON.parse(jsonString);
        })
        .then(jsonData => {
          // Mando el contenido de address (Puerta) al HTML
          document.getElementById("address").textContent = jsonData.puerta || 'No disponible';
          // Separar calle y altura
          const separados = separarCalleYAltura(jsonData.puerta);
          // Mando el contenido de calle (Calle) y altura (Altura) al HTML
          document.getElementById("calle").textContent = separados.calle || 'No disponible';
          document.getElementById("altura").textContent = separados.altura || 'No disponible';
          // Mando el contenido de parcela (Parcela) al HTML
          document.getElementById("parcela").textContent = jsonData.parcela || 'No disponible';
          // Mando el contenido de puerta_x (Puerta XGK) al HTML
          document.getElementById("puerta_x").textContent = jsonData.puerta_x || 'No disponible';
          // Mando el contenido de puerta_y (Puerta YGK) al HTML
          document.getElementById("puerta_y").textContent = jsonData.puerta_y || 'No disponible';
          // Mando el contenido de calle_alturas al HTML
          document.getElementById("calle_alturas").textContent = jsonData.calle_alturas || 'No disponible';
          // Si tengo valores para calle y altura consulto la API de datos útiles

// Mando las coordenadas GK de la dirección para traer las coordenadas WGS84
          // Modelo: API usig https://ws.usig.buenosaires.gob.ar/rest/convertir_coordenadas?x=108150.992445&y=101357.282955&output=lonlat

          fetch(`https://ws.usig.buenosaires.gob.ar/rest/convertir_coordenadas?x=${jsonData.puerta_x}&y=${jsonData.puerta_y}&output=lonlat`)
            .then(response => response.json())
            .then(respuesta => {
              // Mando el contenido de puerta_xgk al HTML
              document.getElementById("puerta_xgk").textContent = respuesta.resultado.x || 'No disponible';
              // Mando el contenido de puerta_ygk al HTML
              document.getElementById("puerta_ygk").textContent = respuesta.resultado.y || 'No disponible';
            })
            .catch(error => {
              console.error('Error al obtener datos útiles:', error);
              // Mostrar mensaje de error en el HTML
              document.getElementById("address").textContent = "Error al obtener los datos.";
            })

















          if (separados.calle && separados.altura) {
            const calle = encodeURIComponent(separados.calle);
            const altura = encodeURIComponent(separados.altura);
            const urlDatosUtiles = `https://ws.usig.buenosaires.gob.ar/datos_utiles?calle=${calle}&altura=${altura}`;
            fetch(urlDatosUtiles)
            .then(response => response.json())
            .then(datos => {
              // Mando el contenido de comuna (Comuna) al HTML
              document.getElementById("comuna").textContent = datos.comuna || 'No disponible';
              // Mando el contenido de barrio (Barrio) al HTML
              document.getElementById("barrio").textContent = datos.barrio || 'No disponible';
              // Mando el contenido de comisaria (Comisaría) al HTML
              document.getElementById("comisaria").textContent = datos.comisaria || 'No disponible';
              // Mando el contenido de area_hospitalaria (Hospital) al HTML
              document.getElementById("hospital").textContent = datos.area_hospitalaria || 'No disponible';
              // Mando el contenido de region_sanitaria (Región Sanitaria) al HTML
              document.getElementById("region").textContent = datos.region_sanitaria || 'No disponible';
              // Mando el contenido de codigo_postal (Código Postal) al HTML
              document.getElementById("postal").textContent = datos.codigo_postal || 'No disponible';
            })
            .catch(error => {
              console.error('Error al obtener datos útiles:', error);
              // Mostrar mensaje de error en el HTML
              document.getElementById("address").textContent = "Error al obtener los datos.";
            })
          }
            












        
          
        })
        .catch(error => {
          console.error('Error al obtener datos:', error);
          document.getElementById("address").textContent = "No se pudo obtener dirección.";
        })
      });      
    </script>
  </body>
</html>