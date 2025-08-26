<?php
require_once "../config/config.php"; // conexión PDO

// Tomar parámetros de la URL
$cui = isset($_GET['cui']) ? (int)$_GET['cui'] : null;
$local = isset($_GET['local']) ? $_GET['local'] : null;

if (!$cui || !$local) {
    die("Parámetros inválidos. Debe indicar cui y local.");
}

try {
    // --- Consulta del aula/local ---
    $stmtAula = $pdo->prepare('
        SELECT cui, categoria_local, tipo_local, local, construccion, planta 
        FROM cuis.v_cui_locales 
        WHERE cui = :cui AND local = :local
    ');
    $stmtAula->execute([
        ':cui' => $cui,
        ':local' => $local
    ]);
    $rowAula = $stmtAula->fetch(PDO::FETCH_ASSOC);

    if (!$rowAula) {
        die("No se encontró el aula con CUI $cui y código $local.");
    }

    // --- Consulta información adicional del edificio (si existe) ---
    // Puedes agregar más consultas aquí según tu estructura de datos
    // Por ejemplo, si tienes tabla de edificios, institución, etc.
    
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Aula <?= htmlspecialchars($rowAula['local']) ?> - CUI <?= htmlspecialchars($rowAula['cui']) ?></title>
        <link href="../css/bootstrap.min.css" rel="stylesheet">
        <link rel="apple-touch-icon" href="../images/apple-icon-180x180.png" sizes="180x180">
        <link rel="icon" href="../images/favicon-32x32.png" sizes="32x32" type="image/png">
        <link rel="icon" href="../images/favicon-16x16.png" sizes="16x16" type="image/png">
        <link rel="icon" href="../images/favicon.ico">
        <style>
            body { 
                font-size: 18px; 
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
            }
            .info-card {
                background: white;
                border-radius: 15px;
                padding: 30px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                margin: 20px auto;
                max-width: 800px;
            }
            .aula-header {
                background: linear-gradient(45deg, #28a745, #20c997);
                color: white;
                padding: 20px;
                border-radius: 10px;
                margin-bottom: 25px;
                text-align: center;
            }
            .aula-header h1 {
                margin: 0;
                font-size: 2.5rem;
                font-weight: bold;
            }
            .cui-badge {
                background: rgba(255,255,255,0.2);
                padding: 8px 16px;
                border-radius: 20px;
                font-size: 1.1rem;
                margin-top: 10px;
                display: inline-block;
            }
            .info-item {
                background: #f8f9fa;
                border-left: 4px solid #28a745;
                padding: 15px;
                margin-bottom: 15px;
                border-radius: 0 8px 8px 0;
            }
            .info-label {
                font-weight: bold;
                color: #495057;
                font-size: 0.9rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .info-value {
                font-size: 1.2rem;
                color: #2d3748;
                margin-top: 5px;
            }
            .footer-info {
                text-align: center;
                margin-top: 30px;
                padding-top: 20px;
                border-top: 2px solid #e9ecef;
                color: #6c757d;
                font-style: italic;
            }
        </style>
    </head>
    <body>
        <div class="container py-4">
            <div class="info-card">
                <!-- Header del Aula -->
                <div class="aula-header">
                    <h1><?= htmlspecialchars($rowAula['local']) ?></h1>
                    <div class="cui-badge">CUI: <?= htmlspecialchars($rowAula['cui']) ?></div>
                </div>

                <!-- Información del Aula -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label">Tipo de Local</div>
                            <div class="info-value"><?= htmlspecialchars($rowAula['tipo_local']) ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label">Categoría</div>
                            <div class="info-value"><?= htmlspecialchars($rowAula['categoria_local']) ?></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label">Construcción</div>
                            <div class="info-value"><?= htmlspecialchars($rowAula['construccion']) ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label">Planta</div>
                            <div class="info-value"><?= htmlspecialchars($rowAula['planta']) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Información Adicional -->
                <div class="info-item">
                    <div class="info-label">Código del Local</div>
                    <div class="info-value">
                        <span class="badge bg-primary fs-6"><?= htmlspecialchars($rowAula['local']) ?></span>
                    </div>
                </div>

                <!-- Footer -->
                <div class="footer-info">
                    <p><i class="fas fa-database"></i> Información obtenida desde la base de datos CUIS</p>
                    <p><small>Accedido el <?= date('d/m/Y H:i:s') ?></small></p>
                </div>
            </div>
        </div>

        <script src="../js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php

} catch (PDOException $e) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error - CUIS</title>
        <link href="../css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-danger text-white d-flex align-items-center min-vh-100">
        <div class="container text-center">
            <h1>❌ Error en la consulta</h1>
            <p>No se pudo obtener la información del aula.</p>
            <p><small>Detalles técnicos: <?= htmlspecialchars($e->getMessage()) ?></small></p>
        </div>
    </body>
    </html>
    <?php
}
?>