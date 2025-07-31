<?php
require_once '../config/config.php'; // ajustá el path según corresponda
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
$formato = $_GET['formato'] ?? 'csv';
$valido = in_array($formato, ['csv', 'xlsx']);

if (!$valido) {
    http_response_code(400);
    echo "Formato no válido.";
    exit;
}

// Consulta a la vista
$sql = "SELECT 
    cui, estado, sector, gestionado, predio, direccion_principal,
    comuna, barrio, codigo_postal, x_gkba, y_gkba, x_wgs84, y_wgs84
    FROM cuis.v_padron_cui";

$stmt = $pdo->query($sql);
$datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($formato === 'csv') {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="padron_cui.csv"');

    $salida = fopen('php://output', 'w');
    if (!empty($datos)) {
        fputcsv($salida, array_keys($datos[0]));
        foreach ($datos as $fila) {
            fputcsv($salida, $fila);
        }
    }
    fclose($salida);
    exit;
}

if ($formato === 'xlsx') {
    require '../vendor/autoload.php'; // Asegurate de tener PhpSpreadsheet instalado


    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Encabezados
    if (!empty($datos)) {
        $sheet->fromArray(array_keys($datos[0]), NULL, 'A1');
        $sheet->fromArray($datos, NULL, 'A2');
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="padron_cui.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}
