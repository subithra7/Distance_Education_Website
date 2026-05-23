<?php
require "db.php";

$cols = [
    "state", "district", "town", "pincode", "phone",
    "community", "caste", "nationality", "religion",
    "mother_tongue", "blood_group", "guardian_name",
    "mother_name", "name_english", "name_tamil", "dob",
    "email", "mobile", "name", "street", "medium",
    "programme_name", "main_subject", "course_type"
];

foreach ($cols as $col) {
    try {
        $pdo->exec("ALTER TABLE \"records\" ALTER COLUMN \"$col\" DROP NOT NULL");
        echo "✅ Dropped NOT NULL on: $col<br>";
    } catch (PDOException $e) {
        echo "ℹ️ $col: " . $e->getMessage() . "<br>";
    }
}
echo "<hr><strong>Done.</strong>";
?>
