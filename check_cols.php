<?php
require 'db.php';
$stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'students'");
print_r($stmt->fetchAll(PDO::FETCH_COLUMN));
?>
