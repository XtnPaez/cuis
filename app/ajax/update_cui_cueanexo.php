<?php
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idrel = $_POST['idrel'] ?? null;
    $cui = $_POST['cui'] ?? null;
    $cueanexo = $_POST['cueanexo'] ?? null;

    if ($idrel && $cui && $cueanexo) {
        // Buscar ID del edificio por CUI
        $sqlEdificio = "SELECT id FROM cuis.edificios WHERE cui = :cui";
        $stmtEdificio = $pdo->prepare($sqlEdificio);
        $stmtEdificio->execute([':cui' => $cui]);
        $edificio = $stmtEdificio->fetch(PDO::FETCH_ASSOC);

        if ($edificio) {
            $id = $edificio['id'];

            // Actualizar registro
            $sqlUpdate = "UPDATE cuis.cui_cueanexo SET id = :id, cui = :cui, cueanexo = :cueanexo WHERE idrel = :idrel";
            $stmtUpdate = $pdo->prepare($sqlUpdate);
            $success = $stmtUpdate->execute([
                ':id' => $id,
                ':cui' => $cui,
                ':cueanexo' => $cueanexo,
                ':idrel' => $idrel
            ]);

            echo $success ? 'OK' : 'Error al actualizar';
        } else {
            echo 'CUI no encontrado en edificios';
        }
    } else {
        echo 'Datos incompletos';
    }
} else {
    echo 'Método no permitido';
}
