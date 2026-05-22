<?php
require 'admission-form/db.php';
$stmt = $pdo->query("SELECT * FROM ug_courses WHERE course_name = 'B.B.A' OR programme_degree = 'B.B.A'");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
