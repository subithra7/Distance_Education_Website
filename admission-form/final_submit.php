<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['step1_data']) || !isset($_SESSION['step2_data'])) {
    header("Location: ap1.php");
    exit;
}

$s1 = $_SESSION['step1_data'];
$s2 = $_SESSION['step2_data'];

// The form is submitted from preview.php to finalize the application.
// $_SESSION['step1_data'] and $_SESSION['step2_data'] are already populated.

    $temp_session_id = session_id();
    $temp_dir = "uploads/temp_" . $temp_session_id . "/";

    // GENERATE APPLICATION NO
    $course = $s1['course_type'];
    $year = date("Y");
    $month = date("n");
    $period = ($month >= 1 && $month <= 6) ? "A" : "C";
    $prefix = $course . $period;
    
    $stmt = $pdo->prepare("SELECT application_no FROM records WHERE application_no LIKE :pattern ORDER BY id DESC LIMIT 1");
    $stmt->execute([':pattern' => $prefix . "-" . $year . "-%"]);
    $lastRecord = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $newNumber = $lastRecord ? ((int) substr($lastRecord['application_no'], -5)) + 1 : 1;
    $application_no = $prefix . "-" . $year . "-" . str_pad($newNumber, 5, "0", STR_PAD_LEFT);
    
    $final_dir = "uploads/" . $application_no . "/";
    if (!is_dir($final_dir)) {
        mkdir($final_dir, 0777, true);
    }
    
    // Move and rename photo & disability
    $photoName = null;
    if (!empty($s1['photo']) && file_exists($temp_dir . $s1['photo'])) {
        $ext = pathinfo($s1['photo'], PATHINFO_EXTENSION);
        $photoName = $application_no . "." . $ext;
        rename($temp_dir . $s1['photo'], $final_dir . $photoName);
    }
    
    $disabilityCrt = null;
    if (!empty($s1['disability_certificate']) && file_exists($temp_dir . $s1['disability_certificate'])) {
        $ext = pathinfo($s1['disability_certificate'], PATHINFO_EXTENSION);
        $disabilityCrt = "DIFFERENTLYABLED-" . $application_no . "." . $ext;
        rename($temp_dir . $s1['disability_certificate'], $final_dir . $disabilityCrt);
    }

    // Move step 2 files
    $docFields = ['sslc','hsc','ug','tc','migration','undertaking','signature'];
    $finalFiles = [];
    foreach ($docFields as $f) {
        $tempName = $s2[$f.'_file'] ?? null;
        if ($tempName && file_exists($temp_dir . $tempName)) {
            $ext = pathinfo($tempName, PATHINFO_EXTENSION);
            $newName = strtoupper($f) . "-" . $application_no . "." . $ext;
            rename($temp_dir . $tempName, $final_dir . $newName);
            $finalFiles[$f] = $newName;
        } else {
            $finalFiles[$f] = null;
        }
    }

    // Insert into DB
    $sql = "INSERT INTO records (
        application_no, course_type, foundation_lang, programme_name, main_subject, medium, differently_abled,
        photo, disability_certificate, name, street, town, state, district, pincode, phone, mobile,
        name_english, name_tamil, email, dob, age, gender, guardian_name, mother_name, aadhaar,
        nationality, religion, mother_tongue, blood_group, community, caste, employment_status, employment_type,
        lsc_code, other_course, other_course_details, defence_personnel, ex_servicemen, 
        sslc_school, sslc_board, sslc_pass_year, sslc_reg_no, sslc_grade, sslc_max_marks,
        hsc_school, hsc_board, hsc_pass_year, hsc_reg_no, hsc_grade, hsc_max_marks,
        dip_school, dip_board, dip_pass_year, dip_reg_no, dip_grade, dip_max_marks,
        ug_school, ug_board, ug_pass_year, ug_reg_no, ug_grade, ug_max_marks,
        abc_status, abc_id, deb_status, deb_id,
        sslc_file, hsc_file, ug_file, tc_file, migration_file, undertaking_file, signature_file, enclosures
    ) VALUES (
        :application_no, :course_type, :foundation_lang, :programme_name, :main_subject, :medium, :differently_abled,
        :photo, :disability_certificate, :name, :street, :town, :state, :district, :pincode, :phone, :mobile,
        :name_english, :name_tamil, :email, :dob, :age, :gender, :guardian_name, :mother_name, :aadhaar,
        :nationality, :religion, :mother_tongue, :blood_group, :community, :caste, :employment_status, :employment_type,
        :lsc_code, :other_course, :other_course_details, :defence_personnel, :ex_servicemen,
        :sslc_school, :sslc_board, :sslc_pass_year, :sslc_reg_no, :sslc_grade, :sslc_max_marks,
        :hsc_school, :hsc_board, :hsc_pass_year, :hsc_reg_no, :hsc_grade, :hsc_max_marks,
        :dip_school, :dip_board, :dip_pass_year, :dip_reg_no, :dip_grade, :dip_max_marks,
        :ug_school, :ug_board, :ug_pass_year, :ug_reg_no, :ug_grade, :ug_max_marks,
        :abc_status, :abc_id, :deb_status, :deb_id,
        :sslc_file, :hsc_file, :ug_file, :tc_file, :migration_file, :undertaking_file, :signature_file, :enclosures
    )";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':application_no' => $application_no,
        ':course_type' => $s1['course_type'] ?? null,
        ':foundation_lang' => $s1['foundation_lang'] ?? null,
        ':programme_name' => $s1['programme_name'] ?? null,
        ':main_subject' => $s1['main_subject'] ?? null,
        ':medium' => $s1['medium'] ?? null,
        ':differently_abled' => $s1['differently_abled'] ?? null,
        ':photo' => $photoName,
        ':disability_certificate' => $disabilityCrt,
        ':name' => $s1['name'] ?? null,
        ':street' => $s1['street'] ?? null,
        ':town' => $s1['town'] ?? null,
        ':state' => $s1['state'] ?? null,
        ':district' => $s1['district'] ?? null,
        ':pincode' => $s1['pincode'] ?? null,
        ':phone' => $s1['phone'] ?? null,
        ':mobile' => $s1['mobile'] ?? null,
        ':name_english' => $s1['name_english'] ?? null,
        ':name_tamil' => $s1['name_tamil'] ?? null,
        ':email' => $s1['email'] ?? null,
        ':dob' => $s1['dob'] ?? null,
        ':age' => $s1['age'] ?? null,
        ':gender' => $s1['gender'] ?? null,
        ':guardian_name' => $s1['guardian_name'] ?? null,
        ':mother_name' => $s1['mother_name'] ?? null,
        ':aadhaar' => $s1['aadhaar'] ?? null,
        ':nationality' => $s1['nationality'] ?? null,
        ':religion' => $s1['religion'] ?? null,
        ':mother_tongue' => $s1['mother_tongue'] ?? null,
        ':blood_group' => $s1['blood_group'] ?? null,
        ':community' => $s1['community'] ?? null,
        ':caste' => $s1['caste'] ?? null,
        ':employment_status' => $s1['employment_status'] ?? null,
        ':employment_type' => $s1['employment_type'] ?? null,
        ':lsc_code' => $s1['lsc_code'] ?? null,
        ':other_course' => $s2['other_course'] ?? null,
        ':other_course_details' => $s2['other_course_details'] ?? null,
        ':defence_personnel' => $s2['defence_personnel'] ?? 0,
        ':ex_servicemen' => $s2['ex_servicemen'] ?? 0,
        ':sslc_school' => $s2['sslc_school'] ?? null,
        ':sslc_board' => $s2['sslc_board'] ?? null,
        ':sslc_pass_year' => $s2['sslc_pass_year'] ?? null,
        ':sslc_reg_no' => $s2['sslc_reg_no'] ?? null,
        ':sslc_grade' => $s2['sslc_grade'] ?? null,
        ':sslc_max_marks' => $s2['sslc_max_marks'] ?? null,
        ':hsc_school' => $s2['hsc_school'] ?? null,
        ':hsc_board' => $s2['hsc_board'] ?? null,
        ':hsc_pass_year' => $s2['hsc_pass_year'] ?? null,
        ':hsc_reg_no' => $s2['hsc_reg_no'] ?? null,
        ':hsc_grade' => $s2['hsc_grade'] ?? null,
        ':hsc_max_marks' => $s2['hsc_max_marks'] ?? null,
        ':dip_school' => $s2['dip_school'] ?? null,
        ':dip_board' => $s2['dip_board'] ?? null,
        ':dip_pass_year' => $s2['dip_pass_year'] ?? null,
        ':dip_reg_no' => $s2['dip_reg_no'] ?? null,
        ':dip_grade' => $s2['dip_grade'] ?? null,
        ':dip_max_marks' => $s2['dip_max_marks'] ?? null,
        ':ug_school' => $s2['ug_school'] ?? null,
        ':ug_board' => $s2['ug_board'] ?? null,
        ':ug_pass_year' => $s2['ug_pass_year'] ?? null,
        ':ug_reg_no' => $s2['ug_reg_no'] ?? null,
        ':ug_grade' => $s2['ug_grade'] ?? null,
        ':ug_max_marks' => $s2['ug_max_marks'] ?? null,
        ':abc_status' => $s2['abc'] ?? 'No',
        ':abc_id' => $s2['abc_id_clean'] ?? null,
        ':deb_status' => $s2['deb'] ?? 'No',
        ':deb_id' => $s2['deb_id_clean'] ?? null,
        ':sslc_file' => $finalFiles['sslc'],
        ':hsc_file' => $finalFiles['hsc'],
        ':ug_file' => $finalFiles['ug'],
        ':tc_file' => $finalFiles['tc'],
        ':migration_file' => $finalFiles['migration'],
        ':undertaking_file' => $finalFiles['undertaking'],
        ':signature_file' => $finalFiles['signature'],
        ':enclosures' => $s2['enclosures'] ?? null
    ]);

    // Clear session & set application_no
    unset($_SESSION['step1_data']);
    unset($_SESSION['step2_data']);
    $_SESSION['application_no'] = $application_no;
    
    // Cleanup Temp Dir if empty
    @rmdir($temp_dir);

    header("Location: Print_application.php");
    exit;
?>
