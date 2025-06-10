// Planto el mapa
const map = L.map('map').setView([-34.62, -58.44], 12);

// Capas base
const cabaTiles = L.tileLayer('https://servicios.usig.buenosaires.gob.ar/mapcache/tms/1.0.0/amba_con_transporte_3857@GoogleMapsCompatible/{z}/{x}/{-y}.png', {
  attribution: '&copy; CABA Tiles || UEICEE - MAPA'
}).addTo(map);
const osmTiles = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '&copy; OpenStreetMap || UEICEE - MAPA'
});
const cabaFoto = L.tileLayer('http://servicios.usig.buenosaires.gob.ar/mapcache/tms/1.0.0/fotografias_aereas_2017_caba_3857@GoogleMapsCompatible/{z}/{x}/{-y}.png', {
  attribution: '&copy; CABA Fotografías Aéreas || UEICEE - MAPA'
});
// Leyenda combinada
const leyenda = L.control({ position: 'bottomright' });
let capasVisibles = new Set();
function actualizarLeyenda() {
  let html = `<h6 class="mb-1">Referencias</h6>`;
  if (capasVisibles.has("CUIs")) {
    html += `<i style="background: #007bff; border-radius: 50%; width: 10px; height: 10px; display: inline-block; margin-right: 5px;"></i> CUIs<br>`;
  }
  if (capasVisibles.has("Direcciones")) {
    html += `<i style="background: #000000; border-radius: 50%; width: 10px; height: 10px; display: inline-block; margin-right: 5px;"></i> Direcciones<br>`;
  }
  if (!leyenda._div) {
    leyenda._div = L.DomUtil.create('div', 'info legend bg-light border rounded p-2 shadow-sm');
    leyenda.onAdd = () => leyenda._div;
  }
  leyenda._div.innerHTML = html;
  leyenda.addTo(map);
}

// Control de capas
const baseLayers = {
  "Mapa Base OpenStreetMap": osmTiles,
  "Mapa Base CABA": cabaTiles,
  "Fotografías Aéreas CABA": cabaFoto
};
const overlays = {};
const capasControl = L.control.layers(baseLayers, overlays, { position: 'topright' }).addTo(map);

// Capa de Edificios
let edificiosLayer = null;
fetch('../api/edificios.php')
  .then(res => res.json())
  .then(data => {
    edificiosLayer = L.geoJSON(data, {
      onEachFeature: (feature, layer) => {
        if (feature.properties) {
          layer.bindPopup(
            `<b>CUI:</b> ${feature.properties.cui}<br><b>Estado:</b> ${feature.properties.estado}`
          );
        }
      },
      pointToLayer: (feature, latlng) => L.circleMarker(latlng, {
        radius: 3,
        fillColor: "#007bff",
        color: "#000",
        weight: 1,
        opacity: 1,
        fillOpacity: 0.4
      })
    }).addTo(map);
    capasControl.addOverlay(edificiosLayer, "CUIs");
    capasVisibles.add("CUIs");
    actualizarLeyenda();
  })
  .catch(err => console.error('Error cargando CUIs:', err));

// Capa de Direcciones
let direccionesLayer = null;
fetch('../api/direcciones.php')
  .then(res => res.json())
  .then(data => {
    direccionesLayer = L.geoJSON(data, {
      onEachFeature: (feature, layer) => {
        if (feature.properties) {
          layer.bindPopup(
            `<b>Calle:</b> ${feature.properties.calle}<br><b>Altura:</b> ${feature.properties.altura}`
          );
        }
      },
      pointToLayer: (feature, latlng) => L.circleMarker(latlng, {
        radius: 2,
        fillColor: "#000000",
        color: "#000",
        weight: 1,
        opacity: 1,
        fillOpacity: 1
      })
    });

    capasControl.addOverlay(direccionesLayer, "Direcciones");
  })
  .catch(err => console.error('Error cargando direcciones:', err));

// Eventos para mostrar/ocultar leyenda combinada
map.on('overlayadd', function (e) {
  capasVisibles.add(e.name);
  actualizarLeyenda();
});
map.on('overlayremove', function (e) {
  capasVisibles.delete(e.name);
  if (capasVisibles.size === 0) {
    map.removeControl(leyenda);
  } else {
    actualizarLeyenda();
  }
});