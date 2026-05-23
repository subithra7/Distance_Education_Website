<?php
require_once "db.php";

$columns = [
    'cc_serial_no' => 'VARCHAR(50) DEFAULT NULL',
    'cc_date_of_issue' => 'DATE DEFAULT NULL',
    'cc_bottom_serial_no' => 'VARCHAR(50) DEFAULT NULL'
];

foreach ($columns as $col => $def) {
    // check if exists
    $check = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name='records' AND column_name='$col'");
    if ($check && $check->rowCount() == 0) {
        $pdo->query("ALTER TABLE records ADD COLUMN $col $def");
        // Add UNIQUE constraint only for serial numbers
        if (strpos($col, 'serial') !== false) {
            $pdo->query("ALTER TABLE records ADD UNIQUE ($col)");
        }
        echo "Added $col\n";
    } else {
        echo "Column $col already exists\n";
    }
}
?>
