<?php
session_start();
require_once('../config/config.php'); 

$registrosPorPagina = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($page < 1) $page = 1;
$offset = ($page - 1) * $registrosPorPagina;

$cuiFiltro = isset($_GET['cui']) && $_GET['cui'] !== '' ? trim($_GET['cui']) : null;

// Contar total de registros
if($cuiFiltro){
    $stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM cuis.v_cui_locales WHERE cui = :cui");
    $stmtTotal->bindValue(':cui', $cuiFiltro);
    $stmtTotal->execute();
    $totalRegistros = $stmtTotal->fetchColumn();
} else {
    $totalRegistros = $pdo->query("SELECT COUNT(*) FROM cuis.v_cui_locales")->fetchColumn();
}
$totalPaginas = ceil($totalRegistros / $registrosPorPagina);

// Traer datos
if($cuiFiltro){
    $sql = "SELECT cui, categoria_local, tipo_local, local, construccion, planta
            FROM cuis.v_cui_locales
            WHERE cui = :cui
            ORDER BY cui
            LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':cui', $cuiFiltro);
} else {
    $sql = "SELECT cui, categoria_local, tipo_local, local, construccion, planta
            FROM cuis.v_cui_locales
            ORDER BY cui
            LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
}

$stmt->bindValue(':limit', $registrosPorPagina, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Render tabla
if($rows){
    echo '<div class="card shadow-sm">';
    echo '<div class="card-body">';
    echo '<h5 class="card-title">Listado de QRs</h5>';
    echo '<div class="table-responsive"><table class="table table-striped table-bordered">';
    echo '<thead class="table-dark"><tr>
            <th>CUI</th><th>Categoría</th><th>Tipo</th><th>Local</th><th>Construcción</th><th>Planta</th>
          </tr></thead><tbody>';
    foreach($rows as $r){
        echo '<tr>
                <td>'.htmlspecialchars($r['cui']).'</td>
                <td>'.htmlspecialchars($r['categoria_local']).'</td>
                <td>'.htmlspecialchars($r['tipo_local']).'</td>
                <td>'.htmlspecialchars($r['local']).'</td>
                <td>'.htmlspecialchars($r['construccion']).'</td>
                <td>'.htmlspecialchars($r['planta']).'</td>
              </tr>';
    }
    echo '</tbody></table></div>';

    // Combo desplegable para paginado
    if($totalPaginas > 1){
        echo '<div class="d-flex justify-content-center align-items-center mt-3">';
        echo '<label for="selectPagina" class="me-2">Ir a página:</label>';
        echo '<select id="selectPagina" class="form-select w-auto" onchange="cargarPagina(this.value)">';
        for($i=1;$i<=$totalPaginas;$i++){
            $selected = ($i==$page) ? 'selected' : '';
            echo "<option value='$i' $selected>$i</option>";
        }
        echo '</select>';
        echo '<span class="ms-2">de '.$totalPaginas.'</span>';
        echo '</div>';
    }

    echo '</div></div>';
} else {
    echo '<div class="alert alert-warning">No se encontraron registros.</div>';
}
