<?php
include "db.php";

$programme = $_GET['programme'];
$id = $_GET['id'];

switch ($programme) {
    case 'UG': $table = 'ug_courses'; break;
    case 'PG': $table = 'pg_courses'; break;
    case 'Diploma': $table = 'diploma_courses'; break;
    case 'Certificate': $table = 'certificate_courses'; break;
    default: exit;
}

$stmt = $pdo->prepare("SELECT eligibility FROM $table WHERE id=?");
$stmt->execute([$id]);
$d = $stmt->fetch(PDO::FETCH_ASSOC);

echo $d['eligibility'];
