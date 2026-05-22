<?php
require "db.php";

$type = $_GET['type'] ?? '';

switch ($type) {
    case "UG":  $table = "ug_courses"; break;
    case "PG":  $table = "pg_courses"; break;
    case "DIP": $table = "diploma_courses"; break;
    case "CERT":$table = "certificate_courses"; break;
    default: echo json_encode([]); exit;
}

try {
    $stmt = $pdo->query("
        SELECT programme_degree, main_subject
        FROM \"$table\"
    ");

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($data);

} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}