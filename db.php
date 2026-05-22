<?php
$host = "localhost";
$port = "5432";
$dbname = "admission_db";
$username = "postgres";
$password = "subi@2003";

try {
    $conn = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Keep compatible variable name for old scripts that used $conn
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
