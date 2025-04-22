<?php
require_once('../config/config.php');
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo "ID inválido.";
    exit;
}

$id = (int) $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM descargas.catalogo WHERE id = :id AND visible = true");
$stmt->execute(['id' => $id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    http_response_code(404);
    echo "Recurso no encontrado.";
    exit;
}

$tipo = $item['tipo_origen'];
$formato = strtolower($item['formato']);
$nombre = $item['nombre_origen'];
$esquema = $item['esquema_origen'];
$mimetype = $item['mimetype'] ?? 'application/octet-stream';

if ($tipo === 'archivo') {
    // Ruta correcta para archivos físicos
    $ruta = realpath(__DIR__ . '/../' . $item['ruta_archivo'] . '/' . $item['nombre_origen']);
    if (!file_exists($ruta)) {
        http_response_code(404);
        echo "Archivo no encontrado.";
        exit;
    }
    // Forzar la descarga del archivo
    header("Content-Type: $mimetype");
    header("Content-Disposition: attachment; filename=\"$nombre\"");
    header("Content-Length: " . filesize($ruta));
    readfile($ruta);
    exit;
}

// Aquí dejamos la parte de exportación a Excel, CSV, GeoJSON y DOCX tal cual está

if (!in_array($tipo, ['tabla', 'vista'])) {
    echo "Tipo de recurso no reconocido.";
    exit;
}

$tabla = "$esquema.$nombre";
$stmt = $pdo->query("SELECT * FROM $tabla");
$datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$datos) {
    http_response_code(204);
    echo "No hay datos para descargar.";
    exit;
}

switch ($formato) {
    case 'xlsx':
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $columnas = array_keys($datos[0]);

        // Encabezados
        foreach ($columnas as $i => $col) {
            $colLetra = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue($colLetra . '1', $col);
        }

        // Filas de datos
        foreach ($datos as $filaIndex => $fila) {
            foreach (array_values($fila) as $colIndex => $valor) {
                $colLetra = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
                $sheet->setCellValue($colLetra . ($filaIndex + 2), $valor);
            }
        }

        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment; filename=\"$nombre.xlsx\"");
        header("Cache-Control: max-age=0");

        $writer = new Xlsx($spreadsheet);
        $writer->save("php://output");
        exit;

    case 'csv':
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=\"$nombre.csv\"");
        $out = fopen("php://output", "w");

        fputcsv($out, array_keys($datos[0]));
        foreach ($datos as $fila) {
            fputcsv($out, $fila);
        }
        fclose($out);
        exit;

    case 'geojson':
        $geojson = [
            'type' => 'FeatureCollection',
            'features' => []
        ];

        foreach ($datos as $fila) {
            if (!isset($fila['geom'])) continue;
            $geom = json_decode($fila['geom'], true);
            unset($fila['geom']);
            $geojson['features'][] = [
                'type' => 'Feature',
                'geometry' => $geom,
                'properties' => $fila
            ];
        }

        header("Content-Type: application/geo+json");
        header("Content-Disposition: attachment; filename=\"$nombre.geojson\"");
        echo json_encode($geojson, JSON_UNESCAPED_UNICODE);
        exit;

    case 'docx':
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        $table = $section->addTable();

        // Encabezado
        $columnas = array_keys($datos[0]);
        $table->addRow();
        foreach ($columnas as $col) {
            $table->addCell()->addText($col);
        }

        // Filas de datos
        foreach ($datos as $fila) {
            $table->addRow();
            foreach ($columnas as $col) {
                $table->addCell()->addText((string)$fila[$col]);
            }
        }

        header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
        header("Content-Disposition: attachment; filename=\"$nombre.docx\"");
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save("php://output");
        exit;

    default:
        echo "Formato no soportado: $formato";
        exit;
}
