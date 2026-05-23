<?php
require_once "db.php";

$out = "Recent Applications:\n";
$q = $pdo->query("SELECT id, application_no, programme_name, main_subject, course_type, medium FROM records ORDER BY id DESC LIMIT 5");
while($r = $q->fetch(PDO::FETCH_ASSOC)){
    $out .= print_r($r, true);
}

$tables = ['ug_courses', 'pg_courses', 'diploma_courses', 'certificate_courses'];
foreach($tables as $t){
    $out .= "\nTABLE: $t\n";
    $q = $pdo->query("SELECT programme_degree, main_subject, course_code FROM $t LIMIT 20");
    if($q){
        while($r = $q->fetch(PDO::FETCH_ASSOC)){
            $out .= print_r($r, true);
        }
    } else {
        $out .= "Error: " . $pdo->error . "\n";
    }
}
file_put_contents('test_course_out.txt', $out);
?>
