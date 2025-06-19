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
    // 1. Buscar coincidencia exacta en la base
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
        foreach ($resultados as $fila) {
            if (strcasecmp(trim($fila['calle']), trim($calle)) === 0 && intval($fila['altura']) === intval($altura)) {
                echo json_encode([
                    'status' => 'exacto',
                    'mensaje' => '✅ Dirección exacta encontrada.',
                    'resultados' => [$fila]
                ]);
                exit;
            }
        }

        echo json_encode([
            'status' => 'flexible',
            'mensaje' => '🔎 Dirección encontrada con coincidencia flexible.',
            'resultados' => $resultados
        ]);
        exit;
    }

    // 2. Buscar coordenadas en la API USIG
    $base = 'https://ws.usig.buenosaires.gob.ar';
    $normalizarUrl = "$base/rest/normalizar_direcciones?calle=" . urlencode($calle) . "&altura=$altura&desambiguar=1";

    $json1 = @file_get_contents($normalizarUrl);
    $data1 = json_decode($json1, true);

    if (!isset($data1['DireccionesCalleAltura']['direcciones'][0]['CodigoCalle'])) {
        echo json_encode([
            'status' => 'sin_resultados',
            'mensaje' => '❌ Dirección no encontrada en la API oficial.',
            'resultados' => []
        ]);
        exit;
    }

    $codCalle = $data1['DireccionesCalleAltura']['direcciones'][0]['CodigoCalle'];
    $urlXY = "$base/geocoder/2.2/geocoding?cod_calle=$codCalle&altura=$altura&metodo=puertas";

    $jsonp = @file_get_contents($urlXY);
    $json2 = json_decode(trim($jsonp, '();'), true); // Elimina paréntesis del JSONP

    if (!isset($json2['x'], $json2['y'])) {
        echo json_encode([
            'status' => 'sin_resultados',
            'mensaje' => '❌ La API no devolvió coordenadas válidas.',
            'resultados' => []
        ]);
        exit;
    }

    $x = $json2['x'];
    $y = $json2['y'];

    $urlConvertir = "$base/rest/convertir_coordenadas?x=$x&y=$y&output=lonlat";
    $json3 = @file_get_contents($urlConvertir);
    $data3 = json_decode($json3, true);

    if (!isset($data3['resultado']['x'], $data3['resultado']['y'])) {
        echo json_encode([
            'status' => 'sin_resultados',
            'mensaje' => '❌ No se pudo convertir a WGS84.',
            'resultados' => []
        ]);
        exit;
    }

    $lon = floatval($data3['resultado']['x']);
    $lat = floatval($data3['resultado']['y']);

    // 3. Buscar direcciones dentro de 100 metros del punto WGS84
    $stmt = $pdo->prepare("
        SELECT calle, altura, x_wgs84, y_wgs84, cui
        FROM cuis.v_direcciones_coordenadas
        WHERE ST_DWithin(
            ST_SetSRID(ST_MakePoint(x_wgs84, y_wgs84), 4326)::geography,
            ST_SetSRID(ST_MakePoint(:lon, :lat), 4326)::geography,
            100
        )
        ORDER BY ST_Distance(
            ST_SetSRID(ST_MakePoint(x_wgs84, y_wgs84), 4326)::geography,
            ST_SetSRID(ST_MakePoint(:lon, :lat), 4326)::geography
        )
    ");
    $stmt->execute([
        ':lon' => $lon,
        ':lat' => $lat
    ]);
    $cercanos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($cercanos)) {
        echo json_encode([
            'status' => 'aproximado',
            'mensaje' => "⚠️ Dirección no encontrada exactamente. Mostrando coincidencias a 100 metros de {$calle} {$altura}.",
            'resultados' => $cercanos,
            'punto_base' => ['x_wgs84' => $lon, 'y_wgs84' => $lat]
        ]);
        exit;
    } else {
        echo json_encode([
            'status' => 'sin_resultados',
            'mensaje' => '❌ No hay resultados a 100 metros de la dirección consultada.',
            'resultados' => []
        ]);
        exit;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error del servidor: ' . $e->getMessage(),
        'resultados' => []
    ]);
    exit;
}
