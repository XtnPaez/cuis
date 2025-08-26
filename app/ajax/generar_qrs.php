<?php
require_once '../config/config.php';
require_once '../lib/phpqrcode/qrlib.php'; // Ajusta la ruta según tu estructura

// Carpeta donde guardar los QR
$qrDir = '../qr/';

// Crear carpeta si no existe
if (!file_exists($qrDir)) {
    mkdir($qrDir, 0777, true);
}

$procesados = 0;
$errores = 0;

try {
    // Obtener aulas que no tienen QR generado
    $sql = "SELECT v.cui, v.categoria_local, v.tipo_local, v.local, v.construccion, v.planta 
            FROM cuis.v_cui_locales v
            LEFT JOIN cuis.aulas_qr aq ON v.cui = aq.cui
            WHERE aq.cui IS NULL OR aq.qr_generado = FALSE";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $aulas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($aulas as $aula) {
        try {
            $cui = $aula['cui'];
            $local = trim($aula['local']);
            
            // URL que quedará embebida en el QR
            $url = "http://10.97.243.147/cuis/app/views/aula.php?cui={$cui}&local={$local}"; // Ajusta tu URL local
            
            // Nombre del archivo PNG
            $filename = $qrDir . "{$cui}_{$local}.png";
            
            // Generar el QR
            QRcode::png($url, $filename, QR_ECLEVEL_L, 10);
            
            // Insertar o actualizar en la tabla de control
            $sqlInsert = "INSERT INTO cuis.aulas_qr (cui, qr_generado, fecha_generacion, ruta_archivo) 
                          VALUES (:cui, TRUE, NOW(), :ruta_archivo)
                          ON CONFLICT (cui) 
                          DO UPDATE SET qr_generado = TRUE, fecha_generacion = NOW(), ruta_archivo = EXCLUDED.ruta_archivo";
            
            $stmtInsert = $pdo->prepare($sqlInsert);
            $stmtInsert->execute([
                ':cui' => $cui,
                ':ruta_archivo' => "qr/{$cui}_{$local}.png"
            ]);
            
            $procesados++;
            
        } catch (Exception $e) {
            $errores++;
            error_log("Error generando QR para CUI {$cui}: " . $e->getMessage());
        }
    }

    // Generar HTML del modal
    if ($procesados > 0) {
        echo '<div class="modal fade" id="resultadoModal" tabindex="-1" aria-labelledby="resultadoModalLabel" aria-hidden="true">';
        echo '<div class="modal-dialog">';
        echo '<div class="modal-content">';
        echo '<div class="modal-header bg-success text-white">';
        echo '<h5 class="modal-title" id="resultadoModalLabel">✅ Generación Completada</h5>';
        echo '<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>';
        echo '</div>';
        echo '<div class="modal-body">';
        echo '<p class="mb-2"><strong>Creación de QRs finalizada exitosamente</strong></p>';
        echo '<p class="mb-2">📊 <strong>Registros procesados:</strong> ' . $procesados . '</p>';
        if ($errores > 0) {
            echo '<p class="mb-2 text-warning">⚠️ <strong>Errores:</strong> ' . $errores . '</p>';
        }
        echo '<p class="mb-0">Los archivos QR se guardaron en la carpeta <code>/qr/</code></p>';
        echo '</div>';
        echo '<div class="modal-footer">';
        echo '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    } else {
        echo '<div class="modal fade" id="resultadoModal" tabindex="-1" aria-labelledby="resultadoModalLabel" aria-hidden="true">';
        echo '<div class="modal-dialog">';
        echo '<div class="modal-content">';
        echo '<div class="modal-header bg-info text-white">';
        echo '<h5 class="modal-title" id="resultadoModalLabel">ℹ️ Sin QRs para generar</h5>';
        echo '<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>';
        echo '</div>';
        echo '<div class="modal-body">';
        echo '<p class="mb-2">Todos los QRs ya han sido generados previamente.</p>';
        echo '<p class="mb-0">No hay nuevas aulas para procesar.</p>';
        echo '</div>';
        echo '<div class="modal-footer">';
        echo '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }

} catch (PDOException $e) {
    // Modal de error
    echo '<div class="modal fade" id="resultadoModal" tabindex="-1" aria-labelledby="resultadoModalLabel" aria-hidden="true">';
    echo '<div class="modal-dialog">';
    echo '<div class="modal-content">';
    echo '<div class="modal-header bg-danger text-white">';
    echo '<h5 class="modal-title" id="resultadoModalLabel">❌ Error en la generación</h5>';
    echo '<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>';
    echo '</div>';
    echo '<div class="modal-body">';
    echo '<p class="mb-0">Ocurrió un error al generar los QRs. Por favor, intenta nuevamente.</p>';
    echo '</div>';
    echo '<div class="modal-footer">';
    echo '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    
    error_log("Error en generar_qrs.php: " . $e->getMessage());
}
?>