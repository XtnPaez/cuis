<?php
    header('Content-Type: application/json');
    require_once('../config/config.php');
    $sql = "SELECT
                distinct dir.calle, dir.altura, ST_AsGeoJSON(coo.geom_wgs84) as geojson, edi.cui 
                from cuis.direcciones dir 
                left join cuis.coordenadas coo on dir.coordenadas_id = coo.id
                left join cuis.edificios_direcciones ed on dir.id = ed.direccion_id
                left join cuis.edificios edi on ed.edificio_id = edi.id
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $features = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $features[] = [
            "type" => "Feature",
            "geometry" => json_decode($row['geojson']),
            "properties" => [
                "calle" => $row['calle'],
                "altura" => $row['altura'],
                "cui" => $row['cui']                    
            ]
        ];
    }
    echo json_encode([
        "type" => "FeatureCollection",
        "features" => $features
    ]);
?>