<?php
$host = '127.0.0.1';
$port = '5432'; // PostgreSQL default port
$db   = 'admission_db';
$user = 'postgres'; // PostgreSQL username
$pass = 'subi@2003'; // PostgreSQL password

$dsn = "pgsql:host=$host;port=$port;dbname=$db";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>