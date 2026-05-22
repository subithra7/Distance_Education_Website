<?php
require 'admission-form/db.php';
$stmt = $pdo->query('SELECT * FROM ug_courses LIMIT 3');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
