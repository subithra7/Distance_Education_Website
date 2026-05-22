<?php
require_once "db.php";

$id = 87; // PGA-2026-00023, English, PG, Counselling psychology
$stmt = $conn->prepare("SELECT r.*, s.state_name FROM records r LEFT JOIN states s ON r.state = s.id WHERE r.id=?");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

$month = date("n");
$period = ($month <= 6) ? "A" : "C";
$year = date("y");
$centerCode = !empty($data['lsc_code']) ? $data['lsc_code'] : "101";
                
$getCourse = $conn->prepare("SELECT course_code FROM pg_courses WHERE LOWER(REPLACE(TRIM(programme_degree),'.','')) = LOWER(REPLACE(TRIM(?),'.','')) AND LOWER(TRIM(main_subject)) = LOWER(TRIM(?)) LIMIT 1");
$getCourse->execute([$data['programme_name'], $data['main_subject']]);
$courseRow = $getCourse->fetch(PDO::FETCH_ASSOC);
$courseCode = strtoupper(trim($courseRow['course_code']));

$prefix = $period.$year.$centerCode.$courseCode;
$medium = strtolower(trim($data['medium']));

if($medium == "english"){
    $startNumber = 6001;
} elseif($medium == "tamil"){
    $startNumber = 1001;
}

if(empty($data['lsc_code'])){
    $check = $conn->prepare("
        SELECT MAX(CAST(SUBSTRING(enrollment_no, -4) AS UNSIGNED)) AS last_number
        FROM records
        WHERE LOWER(TRIM(medium)) = ? AND (lsc_code IS NULL OR lsc_code = '')
        AND CAST(SUBSTRING(enrollment_no, -4) AS UNSIGNED) >= ?
        AND CAST(SUBSTRING(enrollment_no, -4) AS UNSIGNED) NOT IN (1701, 1702)
    ");
    $check->execute([$medium, $startNumber]);
} else {
    $check = $conn->prepare("
        SELECT MAX(CAST(SUBSTRING(enrollment_no, LENGTH(enrollment_no) - 3) AS INT)) AS last_number
        FROM records
        WHERE LOWER(TRIM(medium)) = ? AND lsc_code = ?
        AND CAST(SUBSTRING(enrollment_no, LENGTH(enrollment_no) - 3) AS INT) >= ?
        AND CAST(SUBSTRING(enrollment_no, LENGTH(enrollment_no) - 3) AS INT) NOT IN (1701, 1702)
    ");
    $check->execute([$medium, $data['lsc_code'], $startNumber]);
}
$res = $check->fetch(PDO::FETCH_ASSOC);

if($res['last_number'] !== null){
    $newNumber = $res['last_number'] + 1;
} else {
    $newNumber = $startNumber;
}

while(in_array($newNumber, [1701, 1702])){
    $newNumber++;
}

$newNumber = str_pad($newNumber, 4, "0", STR_PAD_LEFT);
$enrollmentNo = $prefix . $newNumber;

echo "Generated: " . $enrollmentNo . "\n";
?>
