<?php

declare(strict_types=1);

$host = "localhost";
$port = "5432";
$dbname = "admission";
$username = "postgres";
$password = "root";

try {

    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

    $pdo = new PDO($dsn, $username, $password, [

        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

        PDO::ATTR_EMULATE_PREPARES => false,

    ]);

} catch (PDOException $e) {

    error_log($e->getMessage());

    die("Database Connection Failed");

}
?>