<?php
  require_once '../config/config.php';
  require_once 'funciones_busqueda.php';
  $cui = $_GET['cui'] ?? '';
  if (!$cui) {
    echo "<div class='alert alert-warning'>CUI no especificado.</div>";
    exit;
  }
  $datos = buscarCUI($pdo, $cui);
  $resultado = $datos;
  $cueanexos = buscarCUEanexos($pdo, $cui);
  if (!$datos) {
    echo "<div class='alert alert-danger'>No se encontraron datos para el CUI ingresado.</div>";
    exit;
  }
?>
      <!-- Pestañas -->
      <ul class="nav nav-tabs mb-3" id="cuiTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#edificio" type="button" role="tab">Edificio</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="admin-tab" data-bs-toggle="tab" data-bs-target="#geolocalizacion" type="button" role="tab">Geolocalización</button>
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
              <p><strong>Estado:</strong> <?= htmlspecialchars($resultado['estado']) ?></p>
              <p><strong>Sector:</strong> <?= htmlspecialchars($resultado['sector']) ?></p>
              <p><strong>Institución:</strong> <?= htmlspecialchars($resultado['institucion']) ?></p>
              <p><strong>Gestionado:</strong> <?= htmlspecialchars($resultado['gestionado']) ?></p>
              <p><strong>Predio:</strong> <?= htmlspecialchars($resultado['codpre']) ?> - <?= htmlspecialchars($resultado['predio']) ?></p>
              <p><strong>Coordenada XGK:</strong> <?= htmlspecialchars($resultado['x_gkba']) ?></p>
              <p><strong>Coordenada YGK:</strong> <?= htmlspecialchars($resultado['y_gkba']) ?></p>
            </div>
          </div>
        </div>
        <!-- Geolocalización -->
        <div class="tab-pane fade" id="geolocalizacion" role="tabpanel">
          <div class="card shadow-sm mb-3">
            <div class="card-body">
              <ul class="list-group list-group-flush">
                <li class="list-group-item"><strong>Dirección principal - Calle:</strong> <?= htmlspecialchars($resultado['calle']) ?></li>
                <li class="list-group-item"><strong>Dirección principal - Altura:</strong> <?= htmlspecialchars($resultado['altura']) ?></li>
                <li class="list-group-item"><strong>Comuna:</strong> <?= htmlspecialchars($resultado['comuna']) ?></li>
                <li class="list-group-item"><strong>Barrio:</strong> <?= htmlspecialchars($resultado['barrio']) ?></li>
                <li class="list-group-item"><strong>Comisaría:</strong> <?= htmlspecialchars($resultado['comisaria']) ?></li>
                <li class="list-group-item"><strong>Comisaría Vecinal:</strong> <?= htmlspecialchars($resultado['comisaria_vecinal']) ?></li>
                <li class="list-group-item"><strong>Área Hospitalaria:</strong> <?= htmlspecialchars($resultado['area_hospitalaria']) ?></li>
                <li class="list-group-item"><strong>Región Sanitaria:</strong> <?= htmlspecialchars($resultado['region_sanitaria']) ?></li>
                <li class="list-group-item"><strong>Código Postal:</strong> <?= htmlspecialchars($resultado['codigo_postal']) ?></li>
                <li class="list-group-item"><strong>CPA:</strong> <?= htmlspecialchars($resultado['codigo_postal_argentino']) ?></li>
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
              <ul class="list-group list-group-flush">
                <li class="list-group-item"><strong>Calle:</strong> <?= htmlspecialchars($resultado['calle']) ?></li>
              </ul>
            </div>
          </div>
        </div>
        <!-- Datos de RENIE -->
        <div class="tab-pane fade" id="renie" role="tabpanel">
          <div class="card shadow-sm mb-3">
            <div class="card-body">
              <ul class="list-group list-group-flush">
                <li class="list-group-item"><strong>Calle:</strong> <?= htmlspecialchars($resultado['calle']) ?></li>
              </ul>
            </div>
          </div>
        </div>
      </div> <!-- termina contenido de las pestañas -->  
      
      
      