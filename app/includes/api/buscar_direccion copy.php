<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once '../../config/config.php';

$calle = trim($_GET['calle'] ?? '');
$altura = trim($_GET['altura'] ?? '');

if ($calle === '' || $altura === '' || !is_numeric($altura)) {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Debe ingresar una calle y una altura válida.',
        'resultados' => []
    ]);
    exit;
}

try {
    // 1. Buscar coincidencia exacta
    $stmt = $pdo->prepare("
        SELECT calle, altura, x_wgs84, y_wgs84, cui
        FROM cuis.v_direcciones_coordenadas
        WHERE calle ILIKE :calle AND altura = :altura
    ");
    $stmt->execute([
        ':calle' => $calle,
        ':altura' => $altura
    ]);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($resultados)) {
        echo json_encode([
            'status' => 'exacto',
            'mensaje' => '✅ Dirección exacta encontrada.',
            'resultados' => $resultados
        ]);
        exit;
    }

    // 2. No exacto: buscar punto base con altura cercana (±10)
    $alturaMin = max(0, $altura - 10); // Altura no negativa
    $alturaMax = $altura + 10;

    $stmt = $pdo->prepare("
        SELECT x_wgs84, y_wgs84
        FROM cuis.v_direcciones_coordenadas
        WHERE calle ILIKE :calle AND altura BETWEEN :alturaMin AND :alturaMax
        ORDER BY ABS(CAST(altura AS INTEGER) - :altura)
        LIMIT 1
    ");
    $stmt->execute([
        ':calle' => $calle,
        ':alturaMin' => $alturaMin,
        ':alturaMax' => $alturaMax,
        ':altura' => $altura
    ]);
    $puntoBase = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$puntoBase) {
        echo json_encode([
            'status' => 'sin_resultados',
            'mensaje' => '❌ No se encontró ninguna dirección aproximada en la calle con altura cercana.',
            'resultados' => []
        ]);
        exit;
    }

    // 3. Buscar direcciones a 100 metros alrededor del punto base
    $stmt = $pdo->prepare("
        SELECT calle, altura, x_wgs84, y_wgs84, cui
        FROM cuis.v_direcciones_coordenadas
        WHERE ST_DWithin(
            ST_SetSRID(ST_MakePoint(x_wgs84, y_wgs84), 4326)::geography,
            ST_SetSRID(ST_MakePoint(:x, :y), 4326)::geography,
            100
        )
        ORDER BY ST_Distance(
            ST_SetSRID(ST_MakePoint(x_wgs84, y_wgs84), 4326)::geography,
            ST_SetSRID(ST_MakePoint(:x, :y), 4326)::geography
        )
    ");
    $stmt->execute([
        ':x' => $puntoBase['x_wgs84'],
        ':y' => $puntoBase['y_wgs84']
    ]);
    $cercanos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($cercanos)) {
        echo json_encode([
            'status' => 'aproximado',
            'mensaje' => "⚠️ No se encontró la dirección exacta, pero se muestran direcciones dentro de 100 metros de $calle $altura.",
            'resultados' => $cercanos,
            'punto_base' => $puntoBase
        ]);
    } else {
        echo json_encode([
            'status' => 'sin_resultados',
            'mensaje' => '❌ No hay resultados a 100 metros de la dirección que ingresó.',
            'resultados' => []
        ]);
    }

  

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error en el servidor: ' . $e->getMessage(),
        'resultados' => []
    ]);


}
