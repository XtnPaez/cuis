<?php
    require_once('../config/config.php');
    if (!isset($_GET['term']) || strlen($_GET['term']) < 2) {
        echo json_encode([]);
        exit;
    }
    $term = '%' . $_GET['term'] . '%';
    $sql = "SELECT DISTINCT calle FROM cuis.v_direcciones_coordenadas WHERE calle ILIKE :term ORDER BY calle LIMIT 10";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':term' => $term]);
    $calles = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode($calles);
?>
