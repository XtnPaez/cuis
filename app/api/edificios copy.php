<?php
    header('Content-Type: application/json');
    require_once('../config/config.php');
    $sql = "SELECT cui, estado, ST_AsGeoJSON(geom_wgs84) as geojson FROM cuis.edificios";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $features = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $features[] = [
            "type" => "Feature",
            "geometry" => json_decode($row['geojson']),
            "properties" => [
                "cui" => $row['cui'],
                "estado" => $row['estado']
            ]
        ];
    }
    echo json_encode([
        "type" => "FeatureCollection",
        "features" => $features
    ]);
?>