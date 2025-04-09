<?php
$host = 'localhost';
$db = 'sig';
$user = 'postgres';
$pass = 'Qatarairways';

$dbconn = pg_connect("host=$host dbname=$db user=$user password=$pass");

if (!$dbconn) {
    die("Error de conexión con PostgreSQL.");
}
?>