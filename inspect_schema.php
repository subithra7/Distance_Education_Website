<?php
// DB Scraper
$dsn_mysql_3307 = "mysql:host=127.0.0.1;port=3307;dbname=admission_db;charset=utf8mb4";
$dsn_mysql_3306 = "mysql:host=127.0.0.1;port=3306;dbname=admission_db;charset=utf8mb4";
try {
    $mysql = new PDO($dsn_mysql_3307, "root", "");
} catch (PDOException $e) {
    try {
        $mysql = new PDO($dsn_mysql_3306, "root", "");
    } catch (PDOException $e2) {
        die("MySQL Connection Failed");
    }
}

echo "<pre>";
foreach(['staff', 'staff_users'] as $t) {
    echo strtoupper($t) . ":\n";
    try {
        $stmt = $mysql->query("DESCRIBE `$t`");
        if($stmt){
            foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
                echo $r['Field'] . " (" . $r['Type'] . ")\n";
            }
        } else {
             echo "Doesn't exist.\n";
        }
    } catch(Exception $e) {
        echo "Doesn't exist.\n";
    }
    echo "\n";
}
echo "</pre>";
?>
