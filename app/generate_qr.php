<?php
$baseDir = __DIR__;
$qrDir = $baseDir . DIRECTORY_SEPARATOR . 'qr' . DIRECTORY_SEPARATOR;
$templateDir = $baseDir . DIRECTORY_SEPARATOR . 'qr_etiquetas' . DIRECTORY_SEPARATOR;
$outputDir = $templateDir; 

$templateFile = $templateDir . 'base_qr.png';
$fontFile = $baseDir . DIRECTORY_SEPARATOR . 'qr_etiquetas/fonts/arial.ttf';

if (!file_exists($templateFile) || !file_exists($fontFile)) {
    die("❌ Falta el template o la fuente");
}

// --- Parámetros ajustables ---
$qrScale = 0.8;           
$qrOffsetY = 40;          
$fontSize = 20;           
$textOffsetY1 = 50;       
$textOffsetY2 = 20;       

// --- Función para centrar texto ---
function centerText($img, $text, $fontSize, $y, $fontFile, $color) {
    $imgWidth = imagesx($img);
    $bbox = imagettfbbox($fontSize, 0, $fontFile, $text);
    $textWidth = $bbox[2] - $bbox[0];
    $x = ($imgWidth - $textWidth) / 2;
    imagettftext($img, $fontSize, 0, $x, $y, $color, $fontFile, $text);
}

// --- Color personalizado (#1f6161) ---
function getCustomColor($img) {
    return imagecolorallocate($img, 31, 97, 97);
}

// --- Leer todos los QR ---
$files = glob($qrDir . '*.png');
$total = count($files);

echo "Procesando $total QR...\n";

foreach ($files as $qrFile) {

    $qrName = pathinfo($qrFile, PATHINFO_FILENAME);
    list($cui, $local) = explode('_', $qrName);

    $template = imagecreatefrompng($templateFile);
    $qr = imagecreatefrompng($qrFile);

    // Reducir QR
    $qrWidth = imagesx($qr);
    $qrHeight = imagesy($qr);
    $newWidth = (int)($qrWidth * $qrScale);
    $newHeight = (int)($qrHeight * $qrScale);

    $resizedQR = imagecreatetruecolor($newWidth, $newHeight);
    imagealphablending($resizedQR, false);
    imagesavealpha($resizedQR, true);
    imagecopyresampled($resizedQR, $qr, 0, 0, 0, 0, $newWidth, $newHeight, $qrWidth, $qrHeight);

    // Pegar QR un poco más abajo
    $templateWidth = imagesx($template);
    $templateHeight = imagesy($template);

    $dstX = ($templateWidth - $newWidth) / 2;
    $dstY = ($templateHeight - $newHeight) / 2 + $qrOffsetY;

    imagecopy($template, $resizedQR, $dstX, $dstY, 0, 0, $newWidth, $newHeight);

    // Texto centrado arriba del QR
    $colorCustom = getCustomColor($template);

    $text1 = "CUI: $cui";
    $text2 = "Local: $local";

    $textY1 = $dstY - $textOffsetY1;
    $textY2 = $dstY - $textOffsetY2;

    centerText($template, $text1, $fontSize, $textY1, $fontFile, $colorCustom);
    centerText($template, $text2, $fontSize, $textY2, $fontFile, $colorCustom);

    // Guardar resultado
    $outputFile = $outputDir . 'e_' . $qrName . '.png';
    imagepng($template, $outputFile);

    // Limpiar memoria
    imagedestroy($template);
    imagedestroy($qr);
    imagedestroy($resizedQR);

    echo "✅ Generado: $outputFile\n";
}

echo "🎉 ¡Todos los QR procesados!";
