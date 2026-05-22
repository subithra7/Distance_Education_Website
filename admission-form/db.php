<?php

$host = "localhost";
$port = "5432";
$dbname = "admission_db";
$username = "postgres";
$password = "subi@2003"; // your actual password

try {

    $pdo = new PDO(
        "pgsql:host=localhost port=5432 dbname=admission_db",
        $username,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


} catch (PDOException $e) {

    die("Connection failed: " . $e->getMessage());

}
?>