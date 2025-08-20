<?php
require_once "db.php"; // conexión PDO

// Tomar parámetros de la URL
$cui  = isset($_GET['cui']) ? $_GET['cui'] : null;
$aula = isset($_GET['aula']) ? $_GET['aula'] : null;

if (!$cui || !$aula) {
    die("Parámetros inválidos. Debe indicar cui y aula.");
}

try {
    // --- Consulta aula ---
    $stmtAula = $pdo->prepare('SELECT id, cui, aula FROM "aulas"."aula" WHERE cui = :cui AND aula = :aula');
    $stmtAula->execute([
        ':cui'  => $cui,
        ':aula' => $aula
    ]);
    $rowAula = $stmtAula->fetch(PDO::FETCH_ASSOC);

    if (!$rowAula) {
        die("No se encontró el aula con CUI $cui y código $aula.");
    }

    // --- Consulta edificio ---
    $stmtEdificio = $pdo->prepare('SELECT estado, sector FROM "cuis"."edificios" WHERE cui = :cui');
    $stmtEdificio->execute([':cui' => $cui]);
    $rowEdificio = $stmtEdificio->fetch(PDO::FETCH_ASSOC);

    // Valores por defecto si no hay registro de edificio
    $estado = $rowEdificio['estado'] ?? 'N/A';
    $sector = $rowEdificio['sector'] ?? 'N/A';

    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Información del Aula</title>
        <style>
            body { font-family: Arial, sans-serif; font-size: 40px; margin: 20px; }
            .card {
                border: 1px solid #ccc;
                border-radius: 10px;
                padding: 20px;
                max-width: 900px;
                box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            }
            .card h2 { margin-top: 0; }
        </style>
    </head>
    <body>
        <div class="card">
            <h2>Aula <?= htmlspecialchars($rowAula['aula']) ?></h2>
            <p><strong>ID Aula:</strong> <?= htmlspecialchars($rowAula['id']) ?></p>
            <p><strong>CUI:</strong> <?= htmlspecialchars($rowAula['cui']) ?></p>
            <p><strong>Código Aula:</strong> <?= htmlspecialchars($rowAula['aula']) ?></p>
            <hr>
            <h3>Información del Edificio</h3>
            <p><strong>Estado:</strong> <?= htmlspecialchars($estado) ?></p>
            <p><strong>Sector:</strong> <?= htmlspecialchars($sector) ?></p>
            <hr>
            <p>Esta información viene desde la base de datos</p>
        </div>
    </body>
    </html>
    <?php

} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}
