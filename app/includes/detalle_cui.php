<?php
  require_once '../config/config.php';
  require_once 'funciones_busqueda.php';
  // Inicializo las variables
  $resultado = null;
  $cueanexos = [];
  $direcciones = [];
  $renie = [];
  $error = null;
  $cui = $_GET['cui'] ?? '';
  if (!$cui) {
    echo "<div class='alert alert-warning'>CUI no especificado.</div>";
    exit;
  }
  $resultado = buscarCUI($pdo, $cui);
  if ($resultado) {
    $cueanexos = buscarCueAnexos($pdo, $cui);
    $direcciones = buscarDireccionesPorCUI($pdo, $cui);
    $renie = buscarReniePorCUI($pdo, $cui);
  } else {
    echo "<div class='alert alert-danger'>No se encontró ningún edificio con el CUI ingresado.</div>";
    exit;
  }
?>
  <!-- Pestañas -->
  <ul class="nav nav-tabs mb-3" id="cuiTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="edificio-tab" data-bs-toggle="tab" data-bs-target="#edificio" type="button" role="tab">Edificio</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="operativos-tab" data-bs-toggle="tab" data-bs-target="#operativos" type="button" role="tab">Operativos</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="parcela-tab" data-bs-toggle="tab" data-bs-target="#parcela" type="button" role="tab">Parcela</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="cueanexos-tab" data-bs-toggle="tab" data-bs-target="#cueanexos" type="button" role="tab">CUEANEXOS en el CUI</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="dires-tab" data-bs-toggle="tab" data-bs-target="#dires" type="button" role="tab">Direcciones Asociadas</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="renie-tab" data-bs-toggle="tab" data-bs-target="#renie" type="button" role="tab">Datos de RENIE</button>
    </li>
  </ul>
  <!-- Contenido de las pestañas -->
  <div class="tab-content" id="cuiTabsContent">
    <!-- Edificio -->
    <div class="tab-pane fade show active" id="edificio" role="tabpanel">
      <div class="card shadow-sm mb-3">
        <div class="card-body">
          <p><strong>CUI:</strong> <?= htmlspecialchars($resultado['cui']) ?></p>
          <p><strong>Dirección principal - Calle:</strong> <?= htmlspecialchars($resultado['calle']) ?></p>
          <p><strong>Dirección principal - Altura:</strong> <?= htmlspecialchars($resultado['altura']) ?></p>
          <p><strong>Estado:</strong> <?= htmlspecialchars($resultado['estado']) ?></p>
          <p><strong>Sector:</strong> <?= htmlspecialchars($resultado['sector']) ?></p>
          <p><strong>Institución:</strong> <?= htmlspecialchars($resultado['institucion']) ?></p>
          <p><strong>Gestionado:</strong> <?= htmlspecialchars($resultado['gestionado']) ?></p>
          <p><strong>Código de Predio:</strong> <?= htmlspecialchars($resultado['codpre']) ?></p>
          <p><strong>Nombre del Predio:</strong> <?= htmlspecialchars($resultado['predio']) ?></p>
          <p><strong>Comuna:</strong> <?= htmlspecialchars($resultado['comuna']) ?></p>
          <p><strong>Barrio:</strong> <?= htmlspecialchars($resultado['barrio']) ?></p>
          <p><strong>Radio Censal 2022:</strong> <?= htmlspecialchars($resultado['ffrr_2022']) ?></p>
          <p><strong>Código Postal:</strong> <?= htmlspecialchars($resultado['codigo_postal']) ?></p>
          <p><strong>Código Postal Argentino:</strong> <?= htmlspecialchars($resultado['codigo_postal_argentino']) ?></p>
          <p><strong>Distrito Escolar:</strong> <?= htmlspecialchars($resultado['distrito_escolar']) ?></p>
          <p><strong>Región Sanitaria:</strong> <?= htmlspecialchars($resultado['region_sanitaria']) ?></p>
          <p><strong>Área Hospitalaria:</strong> <?= htmlspecialchars($resultado['area_hospitalaria']) ?></p>
          <p><strong>Comisaría:</strong> <?= htmlspecialchars($resultado['comisaria']) ?></p>
          <p><strong>Comisaría Vecinal:</strong> <?= htmlspecialchars($resultado['comisaria_vecinal']) ?></p>
          <p><strong>Distrito Escolar:</strong> <?= htmlspecialchars($resultado['distrito_escolar']) ?></p>
          <p><strong>Coordenada XGK:</strong> <?= htmlspecialchars($resultado['x_gkba']) ?></p>
          <p><strong>Coordenada YGK:</strong> <?= htmlspecialchars($resultado['y_gkba']) ?></p>
          <p><strong>Coordenada XWGS84:</strong> <?= htmlspecialchars($resultado['x_wgs84']) ?></p>
          <p><strong>Coordenada YWGS84:</strong> <?= htmlspecialchars($resultado['y_wgs84']) ?></p>
        </div>
      </div>
    </div>
    <!-- Operativos -->
    <div class="tab-pane fade" id="operativos" role="tabpanel">
      <div class="card shadow-sm mb-3">
        <div class="card-body">
          <ul class="list-group list-group-flush">
            <li class="list-group-item"><strong>CUI:</strong> <?= htmlspecialchars($resultado['cui']) ?></p></li>
            <li class="list-group-item"><strong>RENIE:</strong> <?= htmlspecialchars($resultado['operativo_1']) ?></li>
            <li class="list-group-item"><strong>CENIE:</strong> <?= htmlspecialchars($resultado['operativo_2']) ?></li>
          </ul>
        </div>
      </div>
    </div>
    <!-- Parcela -->
    <div class="tab-pane fade" id="parcela" role="tabpanel">
      <div class="card shadow-sm mb-3">
        <div class="card-body">
          <ul class="list-group list-group-flush">
            <li class="list-group-item"><strong>CUI:</strong> <?= htmlspecialchars($resultado['cui']) ?></p></li>
            <li class="list-group-item"><strong>Sección - Manzana - Parcela:</strong> <?= htmlspecialchars($resultado['smp']) ?></li>
            <li class="list-group-item"><strong>Superficie Total:</strong> <?= htmlspecialchars($resultado['superficie_total']) ?></li>
            <li class="list-group-item"><strong>Superficie Cubierta:</strong> <?= htmlspecialchars($resultado['superficie_cubierta']) ?></li>
            <li class="list-group-item"><strong>Frente:</strong> <?= htmlspecialchars($resultado['frente']) ?></li>
            <li class="list-group-item"><strong>Fondo:</strong> <?= htmlspecialchars($resultado['fondo']) ?></li>
            <li class="list-group-item"><strong>Propiedad Horizontal:</strong> <?= htmlspecialchars($resultado['propiedad_horizontal']) ?></li>
            <li class="list-group-item"><strong>Pisos Bajo Rasante:</strong> <?= htmlspecialchars($resultado['pisos_bajo_rasante']) ?></li>
            <li class="list-group-item"><strong>Pisos Sobre Rasante:</strong> <?= htmlspecialchars($resultado['pisos_sobre_rasante']) ?></li>
            <br><p>Datos extraídos de la API de CABA</p>
          </ul>
        </div>
      </div>
    </div>
    <!-- CUEANEXOS -->
    <div class="tab-pane fade" id="cueanexos" role="tabpanel">
      <div class="card shadow-sm mb-3">
        <div class="card-body">
          <?php if (!empty($cueanexos)): ?>
          <div class="table-responsive">
            <table class="table table-sm table-striped">
              <thead class="table-dark">
                <tr>
                  <th>CUI</th>
                  <th>CUE</th>
                  <th>Anexo</th>
                  <th>Nombre</th>
                  <th>Jurisdiccional</th>
                  <th>Teléfono</th>
                  <th>Responsable</th>
                  <th>Email</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($cueanexos as $fila): ?>
                <tr>
                  <td><?= htmlspecialchars($resultado['cui']) ?></td>
                  <td><?= htmlspecialchars($fila['cue']) ?></td>
                  <td><?= htmlspecialchars($fila['anexo']) ?></td>
                  <td><?= htmlspecialchars($fila['nombre']) ?></td>
                  <td><?= htmlspecialchars($fila['codigo_jurisdiccional']) ?></td>
                  <td><?= htmlspecialchars($fila['telefono']) ?></td>
                  <td><?= htmlspecialchars($fila['apellidor'] . ', ' . $fila['nombrer']) ?></td>
                  <td><?= htmlspecialchars($fila['email']) ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php else: ?>
            <p class="text-muted">No se encontraron CUEANEXOS asociados a este CUI.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <!-- Direcciones Asociadas -->
    <div class="tab-pane fade" id="dires" role="tabpanel">
      <div class="card shadow-sm mb-3">
        <div class="card-body">
          <?php if (!empty($direcciones)): ?>
          <div class="table-responsive">
            <table class="table table-sm table-striped">
              <thead class="table-dark">
                <tr>
                  <th>CUI</th>
                  <th>Calle</th>
                  <th>Altura</th>
                  <th>Código Postal</th>
                  <th>Parcela</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($direcciones as $fila): ?>
                <tr>
                  <td><?= htmlspecialchars($resultado['cui']) ?></td>
                  <td><?= htmlspecialchars($fila['calle']) ?></td>
                  <td><?= htmlspecialchars($fila['altura']) ?></td>
                  <td><?= htmlspecialchars($fila['codigo_postal']) ?></td>
                  <td><?= htmlspecialchars($fila['smp']) ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
        <?php else: ?>
            <p class="text-muted">No se encontraron DIRECCIONES asociadas a este CUI.</p>
        <?php endif; ?>
      </div>
    </div>
    <!-- Datos de RENIE -->
    <div class="tab-pane fade" id="renie" role="tabpanel">
      <div class="card shadow-sm mb-3">
        <div class="card-body">
          <?php if (is_array($renie)): ?>
          <ul class="list-group list-group-flush">
            <li class="list-group-item"><strong>CUI:</strong> <?= htmlspecialchars($resultado['cui']) ?></li>
            <li class="list-group-item"><strong>Construcciones:</strong> <?= htmlspecialchars($renie['construcciones_validas']) ?></li>
            <li class="list-group-item"><strong>Áreas exteriores:</strong> <?= htmlspecialchars($renie['areas_exteriores_validas']) ?></li>
            <li class="list-group-item"><strong>Locales:</strong> <?= htmlspecialchars($renie['cantidad_locales']) ?></li>
            <li class="list-group-item"><strong>Escaleras:</strong> <?= htmlspecialchars($renie['cantidad_escaleras']) ?></li>
            <li class="list-group-item"><strong>Tableros:</strong> <?= htmlspecialchars($renie['cantidad_tableros']) ?></li>
          </ul>
          <br>
          <p class="text-muted">Datos de la base RENIE gestionada por UEICEE. Todos los valores expresan la cantidad de valores válidos (no borrados).</p>
          <?php else: ?>
            <p class="text-muted">No se encontraron datos de RENIE asociados a este CUI.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div> <!-- termina contenido de las pestañas -->