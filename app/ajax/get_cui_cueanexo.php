<?php
require_once '../config/config.php';
$sql = "SELECT * FROM cuis.cui_cueanexo ORDER BY cui LIMIT 12";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo '<table class="table table-bordered">';
echo '<thead><tr><th>CUI</th><th>CUEANEXO</th></tr></thead><tbody>';
foreach ($registros as $row) {
    echo '<tr>';
    echo '<td>' . htmlspecialchars($row['cui']) . '</td>';
    echo '<td>' . htmlspecialchars($row['cueanexo']) . '</td>';
    echo '</tr>';
}
echo '</tbody></table>';