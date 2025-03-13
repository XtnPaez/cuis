<?php
$host = 'localhost';
$db = 'sig';
$user = 'postgres';
$pass = 'Qatarairways'; // Si tienes contraseña en tu DB, cambia esto por la contraseña correcta
$dsn = "pgsql:host=$host;dbname=$db";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Establecer la codificación a UTF-8
    $pdo->exec("SET NAMES 'UTF8'");
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>