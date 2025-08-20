<?php
require_once "db.php";                           // conexión a la BD
require_once __DIR__ . "/lib/phpqrcode/qrlib.php"; // librería QR

// Carpeta donde guardar los QR
$qrDir = __DIR__ . "/qr/";

// Crear carpeta si no existe
if (!file_exists($qrDir)) {
    mkdir($qrDir, 0777, true);
}

try {
    // Leer el único registro de la tabla aulas.aula
    $stmt = $pdo->query("SELECT cui, aula FROM aulas.aula LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $cui  = $row['cui'];
        $aula = trim($row['aula']); // toma "L001" completo

        // URL que quedará embebida en el QR
        $url = "http://10.97.243.147/app/aula.php?cui={$cui}&aula={$aula}";

        // Nombre del archivo PNG
        $filename = $qrDir . "{$cui}_{$aula}.png";

        // Generar el QR
        QRcode::png($url, $filename, QR_ECLEVEL_L, 10);

        echo "QR generado con éxito: <br>";
        echo "<img src='qr/{$cui}_{$aula}.png' alt='QR del aula'>";
        echo "<br><br>URL en el QR: $url";
    } else {
        echo "No se encontraron registros en la tabla aulas.aula.";
    }
} catch (PDOException $e) {
    die("Error al generar QR: " . $e->getMessage());
}
