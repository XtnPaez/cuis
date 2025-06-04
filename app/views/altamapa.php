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
      <!-- Panel de datos -->
      <div class="mt-3">
        <p><strong>Coordenadas CUI (WGS84):</strong> <span id="latLng">Latitud: -, Longitud: -</span></p>
        <p><strong>Coordenadas CUI (GK):</strong> <span id="latgkLnggk">Latitud: -, Longitud: -</span></p>
        <p><strong>Puerta:</strong> <span id="address">No disponible</span></p>
        <p><strong>Calle:</strong> <span id="calle">No disponible</span></p>
        <p><strong>Altura:</strong> <span id="altura">No disponible</span></p>
        <p><strong>Parcela:</strong> <span id="parcela">No disponible</span></p>
        <p><strong>Puerta XGK:</strong> <span id="puerta_x">No disponible</span></p>
        <p><strong>Puerta YGK:</strong> <span id="puerta_y">No disponible</span></p>
        <p><strong>Puerta XWGS84:</strong> <span id="puerta_x">No disponible</span></p>
        <p><strong>Puerta YWGS84:</strong> <span id="puerta_y">No disponible</span></p>
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
      // Creo un popup con las coordenadas
      L.popup()
        .setLatLng(e.latlng)
        .setContent('Latitud: ' + lat.toFixed(6) + '<br>Longitud: ' + lng.toFixed(6))
        .openOn(map);
      // Esto está 2 veces; no se por qué, pero si lo sacás no anda
      map.on('click', function(e) {
        var lat = e.latlng.lat;
        var lng = e.latlng.lng; 
      // Mando el contenido de latLng al HTML
      document.getElementById("latLng").textContent = "Latitud: " + lat.toFixed(6) + ", Longitud: " + lng.toFixed(6);
      // Limpio todos los campos antes de consultar nuevas APIs
      const campos = ["address", "calle", "altura", "parcela", "puerta_x", "puerta_y", "calle_alturas", "comuna", "barrio", "comisaria", 
                      "area_hospitalaria", "region_sanitaria", "distrito_escolar", "comisaria_vecinal", "seccion_catastral", "codigo_postal"];
      campos.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.textContent = 'No disponible';
      });
      // Consultar API Reverse Geocoding
      fetch(`https://datosabiertos-usig-apis.buenosaires.gob.ar/geocoder/2.2/reversegeocoding?x=${lng}&y=${lat}`)
        .then(response => response.text())
        .then(data => {
          // Eliminar los paréntesis alrededor del JSON
          var jsonString = data.replace(/^[(]|[)]$/g, '');  // Elimina los paréntesis del principio y del final
          // Parsear el JSON limpio
          return JSON.parse(jsonString);
        })
        .then(jsonData => {
          if (!jsonData || !jsonData.puerta) {
            throw new Error("No se encontraron datos de dirección.");
         }
        // Mando el contenido de address al HTML
        document.getElementById("address").textContent = jsonData.puerta;
        // Llamo a la funcion para separar calle y altura
        const separados = separarCalleYAltura(jsonData.puerta);
        // Mando el contenido de calle al HTML
        document.getElementById("calle").textContent = separados.calle || 'No disponible';
        // Mando el contenido de altura al HTML
        document.getElementById("altura").textContent = separados.altura || 'No disponible';
        // Mando el contenido de parcela al HTML
        document.getElementById("parcela").textContent = jsonData.parcela || 'No disponible';
        // Mando el contenido de puerta_x al HTML
        document.getElementById("puerta_x").textContent = jsonData.puerta_x || 'No disponible';
        // Mando el contenido de puerta_y al HTML
        document.getElementById("puerta_y").textContent = jsonData.puerta_y || 'No disponible';
        // Mando el contenido de calle_alturas al HTML
        document.getElementById("calle_alturas").textContent = jsonData.calle_alturas || 'No disponible';
        })
        .catch(error => {
          console.error('Error al obtener datos:', error);
          document.getElementById("address").textContent = "No se pudo obtener dirección.";
        });
      });
      
      // consultar API para obtener la dirección
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
      
      // Nueva consulta a la API de normalización
      if (separados.calle && separados.altura) {
        const calleParam = encodeURIComponent(separados.calle);
        const alturaParam = encodeURIComponent(separados.altura);
      fetch(`https://ws.usig.buenosaires.gob.ar/rest/normalizar_direcciones?calle=${calleParam}&altura=${alturaParam}&desambiguar=1`)
        .then(response => response.json())
        .then(normalizado => {
        const resultado = normalizado?.DireccionesCalleAltura?.direcciones?.[0];
        if (resultado) {
          // Mostramos el resultado normalizado
          document.getElementById("normalizado").innerHTML = `
            <p><strong>Código Calle:</strong> ${resultado.CodigoCalle}</p>
            <p><strong>Calle Normalizada:</strong> ${resultado.Calle}</p>
            <p><strong>Altura Normalizada:</strong> ${resultado.Altura}</p>
          `;
        } else {
          document.getElementById("normalizado").innerHTML = `<p class="text-danger">No se pudo normalizar la dirección.</p>`;
        }
      })
      .catch(error => {
        console.error('Error al normalizar la dirección:', error);
        document.getElementById("normalizado").innerHTML = `<p class="text-danger">Error al contactar el servicio de normalización.</p>`;
      });
    }
    // Consultar la API de datos útiles con calle y altura
    if (separados.calle && separados.altura) {
      const calle = encodeURIComponent(separados.calle);
      const altura = encodeURIComponent(separados.altura);
      const urlDatosUtiles = `https://ws.usig.buenosaires.gob.ar/datos_utiles?calle=${calle}&altura=${altura}`;
      fetch(urlDatosUtiles)
        .then(response => response.json())
        .then(datos => {
          // Mostrar los datos útiles debajo de las coordenadas
          document.getElementById("comuna").textContent = datos.comuna || 'No disponible';
          document.getElementById("barrio").textContent = datos.barrio || 'No disponible';
          document.getElementById("comisaria").textContent = datos.comisaria || 'No disponible';
          document.getElementById("hospital").textContent = datos.area_hospitalaria || 'No disponible';
          document.getElementById("region").textContent = datos.region_sanitaria || 'No disponible';
          document.getElementById("postal").textContent = datos.codigo_postal || 'No disponible';
        })
        .catch(error => {
        console.error('Error al obtener los datos útiles:', error);
        });
      }
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