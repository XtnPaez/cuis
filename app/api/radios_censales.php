<?php
header('Content-Type: application/json');
require_once('../config/config.php');

$sql = "
    SELECT 
        id, 
        cod_indec, 
        ST_AsGeoJSON(geom) AS geometry, 
        jsonb_build_object(
            'id', id,
            'cod_indec', cod_indec
        ) AS properties
    FROM capas.radios_censales
    WHERE LEFT(COD_INDEC, 2) = '02'
";

$stmt = $pdo->prepare($sql);
$stmt->execute();

$features = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if ($row["geometry"]) {
        $features[] = [
            "type" => "Feature",
            "geometry" => json_decode($row["geometry"]), // <- clave
            "properties" => json_decode($row["properties"])
        ];
    }
}

$geojson = [
    "type" => "FeatureCollection",
    "features" => $features
];

echo json_encode($geojson);
?>
