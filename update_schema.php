<?php
require 'db.php';

try {
    $conn->exec("ALTER TABLE students ADD COLUMN IF NOT EXISTS dob DATE");
    echo "Added dob to students.\n";
} catch (PDOException $e) {
    echo "Error adding dob: " . $e->getMessage() . "\n";
}

try {
    $conn->exec("ALTER TABLE students ADD COLUMN IF NOT EXISTS abc_status VARCHAR(50)");
    echo "Added abc_status to students.\n";
} catch (PDOException $e) {
    echo "Error adding abc_status: " . $e->getMessage() . "\n";
}

try {
    $conn->exec("ALTER TABLE students ADD COLUMN IF NOT EXISTS abc_id VARCHAR(50)");
    echo "Added abc_id to students.\n";
} catch (PDOException $e) {
    echo "Error adding abc_id: " . $e->getMessage() . "\n";
}

try {
    $conn->exec("ALTER TABLE students ADD COLUMN IF NOT EXISTS deb_status VARCHAR(50)");
    echo "Added deb_status to students.\n";
} catch (PDOException $e) {
    echo "Error adding deb_status: " . $e->getMessage() . "\n";
}

try {
    $conn->exec("ALTER TABLE students ADD COLUMN IF NOT EXISTS deb_id VARCHAR(50)");
    echo "Added deb_id to students.\n";
} catch (PDOException $e) {
    echo "Error adding deb_id: " . $e->getMessage() . "\n";
}

try {
    $conn->exec("ALTER TABLE records ADD COLUMN IF NOT EXISTS deb_id VARCHAR(50)");
    echo "Added deb_id to records.\n";
} catch (PDOException $e) {
    echo "Error adding deb_id: " . $e->getMessage() . "\n";
}

try {
    $conn->exec("ALTER TABLE records ADD COLUMN IF NOT EXISTS deb_status VARCHAR(50)");
    echo "Added deb_status to records.\n";
} catch (PDOException $e) {
    echo "Error adding deb_status: " . $e->getMessage() . "\n";
}

try {
    $conn->exec("ALTER TABLE records ADD COLUMN IF NOT EXISTS urban_rural VARCHAR(50)");
    echo "Added urban_rural to records.\n";
} catch (PDOException $e) {
    echo "Error adding urban_rural: " . $e->getMessage() . "\n";
}

try {
    $conn->exec("ALTER TABLE records ADD COLUMN IF NOT EXISTS cert_return_mode VARCHAR(50)");
    echo "Added cert_return_mode to records.\n";
} catch (PDOException $e) {
    echo "Error adding cert_return_mode: " . $e->getMessage() . "\n";
}

try {
    $conn->exec("ALTER TABLE records ADD COLUMN IF NOT EXISTS status VARCHAR(50) DEFAULT 'draft'");
    echo "Added status to records.\n";
} catch (PDOException $e) {
    echo "Error adding status: " . $e->getMessage() . "\n";
}

try {
    $conn->exec("ALTER TABLE records ADD COLUMN IF NOT EXISTS provisional_file VARCHAR(255)");
    echo "Added provisional_file to records.\n";
} catch (PDOException $e) {
    echo "Error adding provisional_file: " . $e->getMessage() . "\n";
}

echo "Schema update completed.\n";
