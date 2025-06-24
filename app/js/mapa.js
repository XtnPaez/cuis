// Planto el mapa
const map = L.map('map').setView([-34.62, -58.44], 12);

// Capas base
const cabaTiles = L.tileLayer('https://servicios.usig.buenosaires.gob.ar/mapcache/tms/1.0.0/amba_con_transporte_3857@GoogleMapsCompatible/{z}/{x}/{-y}.png', {
  attribution: '&copy; Tiles CABA | UEICEE - MAPA'
}).addTo(map);
const osmTiles = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '&copy; OpenStreetMap | UEICEE - MAPA'
});
const cabaFoto = L.tileLayer('http://servicios.usig.buenosaires.gob.ar/mapcache/tms/1.0.0/fotografias_aereas_2017_caba_3857@GoogleMapsCompatible/{z}/{x}/{-y}.png', {
  attribution: '&copy; Fotografía Aérea CABA | UEICEE - MAPA'
});

// Leyenda combinada
const leyenda = L.control({ position: 'bottomright' });
let capasVisibles = new Set();
function actualizarLeyenda() {
  let html = `<h7 class="mb-"><b>Referencias</b></h7><br>`;
  if (capasVisibles.has("CUIs")) {
    html += `<i style="background: #007bff; border-radius: 50%; width: 10px; height: 10px; display: inline-block; margin-right: 5px;"></i> CUIs<br>`;
  }
  if (capasVisibles.has("Direcciones")) {
    html += `<i style="background: #000000; border-radius: 50%; width: 10px; height: 10px; display: inline-block; margin-right: 5px;"></i> Direcciones<br>`;
  }
  if (capasVisibles.has("Radios Censales")) {
    html += `<i style="border: 1px solid #777; background: transparent; width: 14px; height: 14px; display: inline-block; margin-right: 5px;"></i> Radios Censales<br>`;
  }
  if (capasVisibles.has("Comunas")) {
  html += `<i style="border: 2px solid orange; background: transparent; width: 14px; height: 14px; display: inline-block; margin-right: 5px;"></i> Comunas<br>`;
  }
  if (capasVisibles.has("Barrios")) {
  html += `<i style="border: 1px solid blue; background: transparent; width: 14px; height: 14px; display: inline-block; margin-right: 5px;"></i> Barrios<br>`;
  }
  if (capasVisibles.has("Distrito Escolar")) {
  html += `<i style="border: 1px solid red; background: transparent; width: 14px; height: 14px; display: inline-block; margin-right: 5px;"></i> Distrito Escolar<br>`;
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
  "Fotografía Aérea CABA": cabaFoto
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

// Capa de Radios Censales 
let radiosLayer = null;
fetch('../api/radios_censales.php')
  .then(res => res.json())
  .then(data => {
    radiosLayer = L.geoJSON(data, {
      style: {
        color: '#777',
        weight: 1,
        fillOpacity: 0
      },
      onEachFeature: (feature, layer) => {
        const cod = feature.properties.cod_indec || 'Sin código';
        layer.bindPopup(`<b>Radio Censal:</b> ${cod}`);
      }
    }); 
    capasControl.addOverlay(radiosLayer, "Radios Censales");
  })
  .catch(err => console.error('Error cargando radios censales:', err));

// Capa de Comunas
let comunasLayer = null;
fetch('../api/comunas.php')
  .then(res => res.json())
  .then(data => {
    comunasLayer = L.geoJSON(data, {
      style: {
        color: 'orange',
        weight: 2,
        fillOpacity: 0
      },
      onEachFeature: (feature, layer) => {
        const nombre = feature.properties.comuna || 'Sin nombre';
        layer.bindPopup(`<b>Comuna:</b> ${nombre}`);
      }
    });
    capasControl.addOverlay(comunasLayer, "Comunas");
  })
  .catch(err => console.error('Error cargando comunas:', err));

// Capa de Barrios
let barriosLayer = null;
fetch('../api/barrios.php')
  .then(res => res.json())
  .then(data => {
    comunasLayer = L.geoJSON(data, {
      style: {
        color: 'blue',
        weight: 1,
        fillOpacity: 0
      },
      onEachFeature: (feature, layer) => {
        const nombre = feature.properties.barrio || 'Sin nombre';
        layer.bindPopup(`<b>Barrio:</b> ${nombre}`);
      }
    });
    capasControl.addOverlay(comunasLayer, "Barrios");
  })
  .catch(err => console.error('Error cargando barrios:', err));

// Capa de Distritos Escolares
let deLayer = null;
fetch('../api/distrito_escolar.php')
  .then(res => res.json())
  .then(data => {
    deLayer = L.geoJSON(data, {
      style: {
        color: 'red',
        weight: 1,
        fillOpacity: 0
      },
      onEachFeature: (feature, layer) => {
        const nombre = feature.properties.nombre || 'Sin nombre';
        layer.bindPopup(`<b>Nombre:</b> ${nombre}`);
      }
    });
    capasControl.addOverlay(deLayer, "Distrito Escolar");
  })
  .catch(err => console.error('Error cargando distrito escolar:', err));


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
