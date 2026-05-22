<?php
require 'admission-form/db.php';
$stmt = $pdo->query('SELECT * FROM students LIMIT 1');
print_r($stmt->fetch(PDO::FETCH_ASSOC));
?>
