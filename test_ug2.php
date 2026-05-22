<?php
require 'admission-form/db.php';
$stmt = $pdo->query('SELECT DISTINCT programme_degree FROM ug_courses');
print_r($stmt->fetchAll(PDO::FETCH_COLUMN));
?>
