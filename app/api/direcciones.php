<?php
    header('Content-Type: application/json');
    require_once('../config/config.php');
    $sql = "select distinct dir.calle, dir.altura, ST_AsGeoJSON(coo.geom_wgs84) as geojson from cuis.direcciones dir left join cuis.coordenadas coo on dir.coordenadas_id = coo.id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $features = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $features[] = [
            "type" => "Feature",
            "geometry" => json_decode($row['geojson']),
            "properties" => [
                "calle" => $row['calle'],
                "altura" => $row['altura']
            ]
        ];
    }
    echo json_encode([
        "type" => "FeatureCollection",
        "features" => $features
    ]);
?>