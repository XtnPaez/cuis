<?php
require_once '../config/config.php';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="cui_cueanexo.xlsx"');
header('Cache-Control: max-age=0');

require '../vendor/autoload.php'; // Asegurate de tener PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Encabezados
$sheet->setCellValue('A1', 'CUI');
$sheet->setCellValue('B1', 'CUEANEXO');

// Datos
$sql = "SELECT cui, cueanexo FROM cuis.cui_cueanexo ORDER BY cui";
$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll();

$fila = 2;
foreach ($rows as $row) {
    $sheet->setCellValue('A' . $fila, $row['cui']);
    $sheet->setCellValue('B' . $fila, $row['cueanexo']);
    $fila++;
}

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
