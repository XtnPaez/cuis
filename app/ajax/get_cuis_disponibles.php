<?php
require_once '../config/config.php';

$sql = "SELECT id, cui FROM cuis.edificios ORDER BY cui";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$cuis = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($cuis);
