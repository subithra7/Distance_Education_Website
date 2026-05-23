<?php
// Secure Data Migration Script (MySQL to PostgreSQL)

$dsn_mysql_3307 = "mysql:host=127.0.0.1;port=3307;dbname=admission_db;charset=utf8mb4";
$dsn_mysql_3306 = "mysql:host=127.0.0.1;port=3306;dbname=admission_db;charset=utf8mb4";
try {
    $mysql = new PDO($dsn_mysql_3307, "root", "");
} catch (PDOException $e) {
    try {
        $mysql = new PDO($dsn_mysql_3306, "root", "");
    } catch (PDOException $e2) {
        die("MySQL Connection Failed on both 3307 and 3306. Check your XAMPP MySQL port.\n");
    }
}
$mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

require "db.php";
$pgsql = $pdo;

// Reset schema completely to avoid conflicts from the failed SQL dump
$pgsql->exec("DROP SCHEMA public CASCADE; CREATE SCHEMA public;");

// Create schema
$schema = file_get_contents("schema_only.sql");
$pgsql->exec($schema);
echo "New PostgreSQL Schema created.<br>\n";

$tables = [
  "admin_users", "approval_logs", "caste_master", "certificate_courses", 
  "diploma_courses", "districts", "lsc_users", "pg_courses", "course_fees", 
  "records", "states", "students", "ug_courses", "users", "staff", "staff_users"
];

foreach ($tables as $t) {
    try {
        $stmt = $mysql->query("SELECT * FROM `$t`");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($rows) == 0) {
            echo "Table $t has no records.\n";
            continue;
        }
        
        $cols = array_keys($rows[0]);
        $colArr = array_map(function($c){ return '"'.$c.'"'; }, $cols);
        $valArr = array_fill(0, count($cols), "?");
        $sql = "INSERT INTO \"$t\" (" . implode(",", $colArr) . ") VALUES (" . implode(",", $valArr) . ")";
        
        $pgStmt = $pgsql->prepare($sql);
        
        $pgsql->beginTransaction();
        foreach($rows as $row) {
            $pgStmt->execute(array_values($row));
        }
        $pgsql->commit();
        
        // Update sequence
        $pgsql->query("SELECT setval(pg_get_serial_sequence('\"$t\"', 'id'), coalesce(max(id),0) + 1, false) FROM \"$t\"");
        echo "Migrated " . count($rows) . " rows to $t successfully.<br>\n";
        
    } catch(Exception $e) {
        if($pgsql->inTransaction()) {
            $pgsql->rollBack();
        }
        echo "<span style='color:red'>Error on $t: " . $e->getMessage() . "</span><br>\n";
    }
}
echo "<h3>Migration Complete.</h3>\n";
?>
