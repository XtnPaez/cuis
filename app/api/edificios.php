<?php
    header('Content-Type: application/json');
    require_once('../config/config.php');
    $sql = "SELECT 
                e.id, e.cui, e.estado, e.sector, ed.direccion_id, ed.es_principal, d.calle, d.altura, ST_AsGeoJSON(e.geom_wgs84) as geojson
                from cuis.edificios e
                left join cuis.edificios_direcciones ed on e.id = ed.edificio_id
                left join cuis.direcciones d on ed.direccion_id = d.id
                where ed.es_principal is true
            ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $features = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $features[] = [
            "type" => "Feature",
            "geometry" => json_decode($row['geojson']),
            "properties" => [
                "cui" => $row['cui'],
                "estado" => $row['estado'],
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