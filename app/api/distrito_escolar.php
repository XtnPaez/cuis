<?php
header('Content-Type: application/json');

require_once '../config/config.php'; 

$sql = "SELECT id, nombre, ST_AsGeoJSON(geom) AS geometry FROM capas.distrito_escolar";

$stmt = $pdo->query($sql);

$features = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $features[] = [
        "type" => "Feature",
        "geometry" => json_decode($row['geometry']),
        "properties" => [
            "id" => $row['id'],
            "nombre" => $row['nombre']
        ]
    ];
}

$geojson = [
    "type" => "FeatureCollection",
    "features" => $features
];

echo json_encode($geojson);
