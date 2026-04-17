<?php
$servername = "186.64.120.228:3306";
$username = "mcdp_user";
$password = "mcdp_password";
$dbname = "req_ti";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Configuración Global de Hora
date_default_timezone_set('America/Santiago');
?>
