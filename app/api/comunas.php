<?php
header('Content-Type: application/json');

require_once '../config/config.php'; // $pdo debe estar definido ahí

$sql = "SELECT id, comuna, ST_AsGeoJSON(geom) AS geometry FROM capas.comunas";
$stmt = $pdo->query($sql);

$features = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $features[] = [
        "type" => "Feature",
        "geometry" => json_decode($row['geometry']),
        "properties" => [
            "id" => $row['id'],
            "comuna" => $row['comuna']
        ]
    ];
}

$geojson = [
    "type" => "FeatureCollection",
    "features" => $features
];

echo json_encode($geojson);
