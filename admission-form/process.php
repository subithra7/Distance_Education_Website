<?php
ob_start(); // buffer output so header() always works
session_start();
require_once "db.php";

$action = $_GET['action'] ?? '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF verification failed.");
    }

    if ($action === 'step3') {
        /* ===== DOB VALIDATION ===== */
        if (empty($_POST['dob'])) {
            die("Date of Birth required.");
        }
        $dobDate = new DateTime($_POST['dob']);
        $today   = new DateTime();
        $age     = $today->diff($dobDate)->y;
        if ($age < 17) {
            die("Applicant must be at least 17 years old.");
        }
        
        $temp_session_id = session_id();
        
        /* ===== PHOTO UPLOAD ===== */
        $photoName = $_SESSION['step1_data']['photo'] ?? null;

        if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
            $allowed = ['jpg','jpeg','png'];
            $photoExt = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));

            if (!in_array($photoExt, $allowed)) {
                die("Invalid photo format.");
            }
            if ($_FILES['photo']['size'] > 250 * 1024) {
                die("Photo size must be below 250KB.");
            }

            $uploadDir = "uploads/temp_" . $temp_session_id . "/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $photoName = "PHOTO_" . time() . "." . $photoExt;

            move_uploaded_file(
                $_FILES['photo']['tmp_name'],
                $uploadDir . $photoName
            );
        }

        /* ===== DIFFERENTLY ABLED CERTIFICATE ===== */
        $disability_certificate = $_SESSION['step1_data']['disability_certificate'] ?? null;

        if (!empty($_POST['special_status']) 
            && $_POST['special_status'] != "None"
            && !empty($_FILES['special_file']['name']) && $_FILES['special_file']['error'] == UPLOAD_ERR_OK) {

            $uploadDir = "uploads/temp_" . $temp_session_id . "/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $certExt = strtolower(pathinfo($_FILES['special_file']['name'], PATHINFO_EXTENSION));
            $specialFileName = "DIFFERENTLYABLED_" . time() . "." . $certExt;

            move_uploaded_file(
                $_FILES['special_file']['tmp_name'],
                $uploadDir . $specialFileName
            );
            $disability_certificate = $specialFileName;
        }

        if (($_POST['course_type'] ?? '') !== "UG") {
            $_POST['foundation_lang'] = null;
        }

        /* Removed early application_no generation to fix duplicate constraint race condition */

        /* ===== STORE IN SESSION ===== */
        $_SESSION['current_step'] = 3;
        $_SESSION['step1_data'] = array_merge($_POST, [
            'photo' => $photoName,
            'disability_certificate' => $disability_certificate,
            'age' => $age,
            'differently_abled' => $_POST['special_status'] ?? "None",
            'special_status' => $_POST['special_status'] ?? 'None',
            'lsc_code' => $_SESSION['lsc_code'] ?? NULL
        ]);

        header("Location: ap2.php");
        exit;
    }
    
    if ($action === 'step6') {
        
        if (!isset($_SESSION['step1_data'])) {
            header("Location: ap1.php");
            exit;
        }
        
        $s1 = $_SESSION['step1_data'];
        $temp_session_id = session_id();

        $course_type = $s1['course_type'] ?? '';

        $other_course = $_POST['other_course'] ?? null;
        $other_course_details = $_POST['other_course_details'] ?? null;
        
        $defence_personnel = 0;
        $ex_servicemen = 0;

        if(isset($_POST['defence_status'])){
            if($_POST['defence_status'] == "defence"){
                $defence_personnel = 1;
            }
            if($_POST['defence_status'] == "ex"){
                $ex_servicemen = 1;
            }
        }

        $abc_status = $_POST['abc'] ?? "No";
        $abc_id = $_POST['abc_id'] ?? null;
        $abc_id_clean = preg_replace('/\s+/', '', $abc_id);

        if ($abc_status === "Yes") {
            if (!preg_match('/^[0-9]{12}$/', $abc_id_clean)) {
                die("ABC ID must be exactly 12 digits.");
            }
            $check = $pdo->prepare("SELECT COUNT(*) FROM records WHERE abc_id = :abc");
            $check->execute([':abc' => $abc_id_clean]);
            if ($check->fetchColumn() > 0) {
                die("ABC ID already exists.");
            }
        }
     
        /* MANDOATORY DOCUMENTS VALIDATION */
        /* ===== CORRECT VALIDATION RULES ===== */

$errors = [];

// SSLC mandatory
if (empty($_FILES['sslc']['name']) && empty($_SESSION['step2_data']['sslc_file'])) {
    $errors[] = "SSLC certificate is mandatory";
}

// HSC mandatory
if (empty($_FILES['hsc']['name']) && empty($_SESSION['step2_data']['hsc_file'])) {
    $errors[] = "HSC certificate is mandatory";
}

// ✅ TC OR Migration (at least one)
$tcEmpty = empty($_FILES['tc']['name']) && empty($_SESSION['step2_data']['tc_file']);
$migrationEmpty = empty($_FILES['migration']['name']) && empty($_SESSION['step2_data']['migration_file']);

if ($tcEmpty && $migrationEmpty) {
    $errors[] = "Upload either TC OR Migration Certificate";
}

// ✅ UG OR Provisional (for PG)
if ($course_type === "PG") {
    $ugEmpty = empty($_FILES['ug']['name']) && empty($_SESSION['step2_data']['ug_file']);

    if ($ugEmpty) {
        $errors[] = "Upload UG OR Provisional Certificate";
    }
}

// ✅ Signature mandatory
if (empty($_FILES['signature']['name']) && empty($_SESSION['step2_data']['signature_file'])) {
    $errors[] = "Signature is mandatory";
}

// ❌ STOP if errors
if (!empty($errors)) {
    die(implode("<br>", $errors));
}

        $uploadDir = "uploads/temp_" . $temp_session_id . "/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $docFields = ['sslc','hsc','ug','tc','migration','undertaking','signature'];
        $allowedExt = ['pdf','jpg','jpeg','png'];
        $files = [];

        foreach ($docFields as $field) {
            $files[$field] = $_SESSION['step2_data'][$field."_file"] ?? null;

            if (isset($_FILES[$field]) && $_FILES[$field]['error'] == 0) {
                $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));

                if (!in_array($ext, $allowedExt)) {
                    die("Invalid file type for " . strtoupper($field));
                }
                if ($_FILES[$field]['size'] > 250 * 1024) {
                    die(strtoupper($field) . " exceeds 250KB");
                }

                $newFile = strtoupper($field) . "_" . time() . "_" . uniqid() . "." . $ext;

                if (move_uploaded_file($_FILES[$field]['tmp_name'], $uploadDir . $newFile)) {
                    $files[$field] = $newFile;
                }
            }
        }

        $enclosures = isset($_POST['enclosures']) ? implode(",", $_POST['enclosures']) : null;
        
        $s2 = array_merge($_POST, [
            'defence_personnel' => $defence_personnel,
            'ex_servicemen' => $ex_servicemen,
            'abc_id_clean' => $abc_id_clean,
            'sslc_file' => $files['sslc'],
            'hsc_file' => $files['hsc'],
            'ug_file' => $files['ug'],
            'tc_file' => $files['tc'],
            'migration_file' => $files['migration'],
            'undertaking_file' => $files['undertaking'],
            'signature_file' => $files['signature'],
            'enclosures' => $enclosures
        ]);
        
        $_SESSION['step2_data'] = $s2;
        
        // NOW INSERT INTO DATABASE FOR STEP 6
        $course = $s1['course_type'] ?? '';
        if (!in_array($course, ['UG','PG','DIP','CERT'])) {
            die("Invalid course type.");
        }

        $pdo->query("SELECT pg_advisory_lock(hashtext('app_no_lock'))")->fetchAll();

        $year = date("Y");
        $month = date("n");
        $period = ($month <= 6) ? "A" : "C";
        $prefix = $course . $period;
        
        $stmt = $pdo->prepare("SELECT application_no FROM records WHERE application_no LIKE :pattern ORDER BY id DESC LIMIT 1");
        $stmt->execute([':pattern' => $prefix . "-" . $year . "-%"]);
        $lastRecord = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $newNumber = $lastRecord ? ((int) substr($lastRecord['application_no'], -5)) + 1 : 1;
        $formattedNumber = str_pad($newNumber, 5, "0", STR_PAD_LEFT);
        $application_no = $prefix . "-" . $year . "-" . $formattedNumber;
        $_SESSION['application_no'] = $application_no;
        
        // Move temp files to final location
        $final_dir = "uploads/" . $application_no . "/";
        if (!is_dir($final_dir)) {
            mkdir($final_dir, 0777, true);
        }
        
        $photoName = null;
        if (!empty($s1['photo']) && file_exists($uploadDir . $s1['photo'])) {
            $ext = pathinfo($s1['photo'], PATHINFO_EXTENSION);
            $photoName = $application_no . "." . $ext;
            rename($uploadDir . $s1['photo'], $final_dir . $photoName);
        }
        
        $disabilityCrt = null;
        if (!empty($s1['disability_certificate']) && file_exists($uploadDir . $s1['disability_certificate'])) {
            $ext = pathinfo($s1['disability_certificate'], PATHINFO_EXTENSION);
            $disabilityCrt = "DIFFERENTLYABLED-" . $application_no . "." . $ext;
            rename($uploadDir . $s1['disability_certificate'], $final_dir . $disabilityCrt);
        }

        $finalFiles = [];
        foreach ($docFields as $f) {
            $tempName = $s2[$f.'_file'] ?? null;
            if ($tempName && file_exists($uploadDir . $tempName)) {
                $ext = pathinfo($tempName, PATHINFO_EXTENSION);
                $newName = strtoupper($f) . "-" . $application_no . "." . $ext;
                rename($uploadDir . $tempName, $final_dir . $newName);
                $finalFiles[$f] = $newName;
            } else {
                $finalFiles[$f] = null;
            }
        }

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
            abc_status, abc_id,
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
            :abc_status, :abc_id,
            :sslc_file, :hsc_file, :ug_file, :tc_file, :migration_file, :undertaking_file, :signature_file, :enclosures
        )";
        
        // Helper: convert empty string to NULL for integer columns
        $ni = fn($v) => (isset($v) && $v !== '') ? (int)$v : null;
        // Helper: convert empty string to NULL for text columns
        $ns = fn($v) => (isset($v) && $v !== '') ? $v : null;

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':application_no' => $application_no,
            ':course_type' => $ns($s1['course_type'] ?? null),
            ':foundation_lang' => $ns($s1['foundation_lang'] ?? null),
            ':programme_name' => $ns($s1['programme_name'] ?? null),
            ':main_subject' => $ns($s1['main_subject'] ?? null),
            ':medium' => $ns($s1['medium'] ?? null),
            ':differently_abled' => $ns($s1['differently_abled'] ?? null),
            ':photo' => $photoName,
            ':disability_certificate' => $disabilityCrt,
            ':name' => $ns($s1['name'] ?? null),
            ':street' => $ns($s1['street'] ?? null),
            ':town' => $ns($s1['town'] ?? null),
            ':state' => $ns($s1['state'] ?? null),
            ':district' => $ns($s1['district'] ?? null),
            ':pincode' => $ns($s1['pincode'] ?? null),
            ':phone' => $ns($s1['phone'] ?? null),
            ':mobile' => $ns($s1['mobile'] ?? null),
            ':name_english' => $ns($s1['name_english'] ?? null),
            ':name_tamil' => $ns($s1['name_tamil'] ?? null),
            ':email' => $ns($s1['email'] ?? null),
            ':dob' => $ns($s1['dob'] ?? null),
            ':age' => $ni($s1['age'] ?? null),
            ':gender' => $ns($s1['gender'] ?? null),
            ':guardian_name' => $ns($s1['guardian_name'] ?? null),
            ':mother_name' => $ns($s1['mother_name'] ?? null),
            ':aadhaar' => $ns($s1['aadhaar'] ?? null),
            ':nationality' => $ns($s1['nationality'] ?? null),
            ':religion' => $ns($s1['religion'] ?? null),
            ':mother_tongue' => $ns($s1['mother_tongue'] ?? null),
            ':blood_group' => $ns($s1['blood_group'] ?? null),
            ':community' => $s1['community'] ?? null,
            ':caste' => $s1['caste'] ?? null,
            ':employment_status' => $ns($s1['employment_status'] ?? null),
            ':employment_type' => $ns($s1['employment_type'] ?? null),
            ':lsc_code' => $ns($s1['lsc_code'] ?? null),
            ':other_course' => $ns($s2['other_course'] ?? null),
            ':other_course_details' => $ns($s2['other_course_details'] ?? null),
            ':defence_personnel' => $ni($s2['defence_personnel'] ?? 0),
            ':ex_servicemen' => $ni($s2['ex_servicemen'] ?? 0),
            ':sslc_school' => $ns($s2['sslc_school'] ?? null),
            ':sslc_board' => $ns($s2['sslc_board'] ?? null),
            ':sslc_pass_year' => $ns($s2['sslc_pass_year'] ?? null),
            ':sslc_reg_no' => $ns($s2['sslc_reg_no'] ?? null),
            ':sslc_grade' => $ns($s2['sslc_grade'] ?? null),
            ':sslc_max_marks' => $ni($s2['sslc_max_marks'] ?? null),
            ':hsc_school' => $ns($s2['hsc_school'] ?? null),
            ':hsc_board' => $ns($s2['hsc_board'] ?? null),
            ':hsc_pass_year' => $ns($s2['hsc_pass_year'] ?? null),
            ':hsc_reg_no' => $ns($s2['hsc_reg_no'] ?? null),
            ':hsc_grade' => $ns($s2['hsc_grade'] ?? null),
            ':hsc_max_marks' => $ni($s2['hsc_max_marks'] ?? null),
            ':dip_school' => $ns($s2['dip_school'] ?? null),
            ':dip_board' => $ns($s2['dip_board'] ?? null),
            ':dip_pass_year' => $ns($s2['dip_pass_year'] ?? null),
            ':dip_reg_no' => $ns($s2['dip_reg_no'] ?? null),
            ':dip_grade' => $ns($s2['dip_grade'] ?? null),
            ':dip_max_marks' => $ni($s2['dip_max_marks'] ?? null),
            ':ug_school' => $ns($s2['ug_school'] ?? null),
            ':ug_board' => $ns($s2['ug_board'] ?? null),
            ':ug_pass_year' => $ns($s2['ug_pass_year'] ?? null),
            ':ug_reg_no' => $ns($s2['ug_reg_no'] ?? null),
            ':ug_grade' => $ns($s2['ug_grade'] ?? null),
            ':ug_max_marks' => $ni($s2['ug_max_marks'] ?? null),
            ':abc_status' => $ns($s2['abc'] ?? 'No'),
            ':abc_id' => $ns($s2['abc_id_clean'] ?? null),
            ':sslc_file' => $finalFiles['sslc'],
            ':hsc_file' => $finalFiles['hsc'],
            ':ug_file' => $finalFiles['ug'],
            ':tc_file' => $finalFiles['tc'],
            ':migration_file' => $finalFiles['migration'],
            ':undertaking_file' => $finalFiles['undertaking'],
            ':signature_file' => $finalFiles['signature'],
            ':enclosures' => $ns($s2['enclosures'] ?? null)
        ]);

        $pdo->query("SELECT pg_advisory_unlock(hashtext('app_no_lock'))")->fetchAll();

        unset($_SESSION['step1_data']);
        unset($_SESSION['step2_data']);
        
        // Clean temp dir
        @rmdir($uploadDir);

        header("Location: Print_application.php");
        exit;
    }
}
?>
