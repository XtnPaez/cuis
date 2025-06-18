document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("form-busqueda");
  const detalleDiv = document.getElementById("detalle-cui");
  const mensajeDiv = document.getElementById("mensaje");

  let mapa = L.map("map").setView([-34.6, -58.4], 12);
  let capa = L.layerGroup().addTo(mapa);

  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution: "&copy; OpenStreetMap contributors",
  }).addTo(mapa);

  function mostrarMensaje(tipo, texto) {
    mensajeDiv.className = `alert alert-${tipo}`;
    mensajeDiv.textContent = texto;
    mensajeDiv.classList.remove("d-none");
  }

  function ocultarMensaje() {
    mensajeDiv.className = "alert d-none";
    mensajeDiv.textContent = "";
  }

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const calle = document.getElementById("calle").value.trim();
    const altura = document.getElementById("altura").value.trim();

    capa.clearLayers();
    detalleDiv.innerHTML = "";
    ocultarMensaje();

    try {
      const res = await fetch(`../includes/api/buscar_direccion.php?calle=${encodeURIComponent(calle)}&altura=${encodeURIComponent(altura)}`);
      if (!res.ok) {
        throw new Error(`Respuesta HTTP no OK: ${res.status}`);
      }

      let data;
      try {
        data = await res.json();
      } catch (jsonError) {
        const textoCrudo = await res.text();
        console.error("Error al parsear JSON. Respuesta cruda:", textoCrudo);
        throw new Error("Respuesta no es un JSON válido.");
      }

      if (!data || typeof data !== "object" || !Array.isArray(data.resultados)) {
        throw new Error("Estructura de respuesta inválida.");
      }

      const resultados = data.resultados;

      // Mostrar mensaje si hay
      if (data.status === "exacto") {
        mostrarMensaje("success", data.mensaje);
      } else if (data.status === "aproximado") {
        mostrarMensaje("warning", data.mensaje);
      } else if (data.status === "sin_resultados") {
        mostrarMensaje("danger", data.mensaje);
        return;
      } else {
        mostrarMensaje("info", "Resultado no clasificado.");
      }

      resultados.forEach((item) => {
        const marker = L.circleMarker([item.y_wgs84, item.x_wgs84], {
          radius: 8,
          color: "orange",
          fillColor: "yellow",
          fillOpacity: 0.8,
        }).addTo(capa);

        marker.bindPopup(`<strong>${item.calle} ${item.altura}</strong><br>CUI: <a href="#" class="cui-link" data-cui="${item.cui}">${item.cui}</a>`);
      });

      const bounds = L.latLngBounds([]);
      capa.eachLayer(layer => {
        if (layer.getLatLng) {
          bounds.extend(layer.getLatLng());
        }
      });
      if (bounds.isValid()) {
        mapa.fitBounds(bounds);
      }

    } catch (error) {
      console.error("Error al procesar la búsqueda:", error);
      mostrarMensaje("danger", "Ocurrió un error al procesar la búsqueda.");
    }
  });

  // Cargar detalle del CUI
  document.addEventListener("click", async (e) => {
    if (e.target.classList.contains("cui-link")) {
      e.preventDefault();
      const cui = e.target.dataset.cui;

      try {
        const res = await fetch(`../includes/detalle_cui.php?cui=${cui}`);
        if (!res.ok) {
          mostrarMensaje("danger", "No se pudo cargar el detalle del CUI.");
          return;
        }

        const html = await res.text();

        if (!html || html.trim().length < 10) {
          mostrarMensaje("warning", "No se encontraron detalles para el CUI.");
          return;
        }

        detalleDiv.innerHTML = html;
        ocultarMensaje();
      } catch (error) {
        console.error("Error al cargar detalle:", error);
        mostrarMensaje("danger", "Error al procesar el detalle del CUI.");
      }
    }
  });
});
