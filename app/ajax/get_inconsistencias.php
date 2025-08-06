<?php
require_once '../config/config.php';
$sql = "SELECT idrel, id, cui, cueanexo FROM cuis.cui_cueanexo WHERE cui IS NULL OR cueanexo IS NULL ORDER BY id ASC, cui ASC, cueanexo ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo '<table class="table table-bordered">';
echo '<thead><tr>
        <th>ID CUI</th>
        <th>CUI</th>
        <th>CUEANEXO</th>
        <th>Acciones</th>
      </tr></thead><tbody>';

foreach ($registros as $row) {
    echo '<tr>';
    echo '<td>' . htmlspecialchars($row['idrel']) . '</td>';
    echo '<td>' . htmlspecialchars($row['cui']) . '</td>';
    echo '<td>' . htmlspecialchars($row['cueanexo']) . '</td>';
    
    echo '<td>';
    
    // Botón Editar
    echo '<button class="btn btn-sm btn-primary btnEditar" 
                data-idrel="' . htmlspecialchars($row['idrel']) . '" 
                data-cui="' . htmlspecialchars($row['cui']) . '" 
                data-cueanexo="' . htmlspecialchars($row['cueanexo']) . '">
                Editar
          </button> ';
    
    // Botón Alta CUI
    echo '<a href="buscarcuixcodigo.php" class="btn btn-sm btn-success">
            Dar de Alta CUI
          </a>';

    echo '</td>';
    echo '</tr>';
}

echo '</tbody></table>';