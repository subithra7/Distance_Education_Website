<?php
session_start();
require_once "db.php";
$PHOTO_UPLOAD_ENABLED = false;
$DOCUMENT_UPLOAD_ENABLED = false;
$SIGNATURE_UPLOAD_ENABLED = false;

if (!isset($_SESSION['application_no'])) {
    header("Location: ap1.php");
    exit;
}
$appNo = $_SESSION['application_no'];
/* =========================================
   GET COURSE TYPE (FROM STEP-1 RECORD)
========================================= */
$getCourse = $pdo->prepare("
  SELECT course_type
  FROM records
  WHERE application_no = :app
");
$getCourse->execute([
  ':app' => $appNo
]);
$course_type = $getCourse->fetchColumn();
$edit_data = [];
$checkStmt = $pdo->prepare("SELECT * FROM records WHERE application_no = :app");
$checkStmt->execute([':app' => $appNo]);
if ($row = $checkStmt->fetch(PDO::FETCH_ASSOC)) {
    $edit_data = $row;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
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
    
    $cert_return_mode = $_POST['cert_return_mode'] ?? '';

 /* =========================================
   DOCUMENT MANDATORY VALIDATION
========================================= */
/* Mandatory for ALL courses */
/*if($DOCUMENT_UPLOAD_ENABLED){ this use for termp hide */

if($DOCUMENT_UPLOAD_ENABLED){
$mandatoryDocs = [
  'sslc',
  'hsc'
];
foreach ($mandatoryDocs as $doc) {
  if (empty($_FILES[$doc]['name']) && empty($edit_data[$doc . '_file'])) {
    die(strtoupper($doc) . " certificate is mandatory.");
  }
}

// TC OR Migration Rule
if (empty($_FILES['tc']['name']) && empty($edit_data['tc_file']) && empty($_FILES['migration']['name']) && empty($edit_data['migration_file'])) {
    die("At least one certificate must be uploaded: Transfer Certificate OR Migration Certificate.");
}

/* UG / Provisional mandatory ONLY for PG */
if ($course_type === "PG") {
  if (empty($_FILES['ug']['name']) && empty($edit_data['ug_file'])) {
    die("UG / Provisional certificate is mandatory for PG courses.");
  }
}
    $uploadDir = "uploads/" . $appNo . "/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
 $docFields = ['sslc','hsc','ug','tc','migration','undertaking','signature'];
$allowedExt = ['pdf','jpg','jpeg','png'];
$files = [];
}
/* the top line 82 use } for termp hidde code */
foreach ($docFields as $field) {

    if (isset($_FILES[$field]) && $_FILES[$field]['error'] == 0) {

        $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExt)) {
            die("Invalid file type for " . strtoupper($field));
        }

        if ($_FILES[$field]['size'] > 2 * 1024 * 1024) {
            die(strtoupper($field) . " exceeds 2MB");
        }

        $newFile = strtoupper($field) . "-" . $appNo . "." . $ext;

        if (move_uploaded_file($_FILES[$field]['tmp_name'], $uploadDir . $newFile)) {
            $files[$field] = $newFile;
        }

    } else {
        $files[$field] = $edit_data[$field . '_file'] ?? null;
    }
}
    $enclosures = isset($_POST['enclosures'])
        ? implode(",", $_POST['enclosures'])
        : null;
    $sql = "UPDATE records SET
        other_course = :other_course,
        other_course_details = :other_course_details,
        defence_personnel = :defence_personnel,
        ex_servicemen = :ex_servicemen,
        sslc_school = :sslc_school,
        sslc_board = :sslc_board,
        sslc_pass_year = :sslc_pass_year,
        sslc_reg_no = :sslc_reg_no,
        sslc_grade = :sslc_grade,
        sslc_max_marks = :sslc_max_marks,
        hsc_school = :hsc_school,
        hsc_board = :hsc_board,
        hsc_pass_year = :hsc_pass_year,
        hsc_reg_no = :hsc_reg_no,
        hsc_grade = :hsc_grade,
        hsc_max_marks = :hsc_max_marks,
        dip_school = :dip_school,
        dip_board = :dip_board,
        dip_pass_year = :dip_pass_year,
        dip_reg_no = :dip_reg_no,
        dip_grade = :dip_grade,
        dip_max_marks = :dip_max_marks,
        ug_school = :ug_school,
        ug_board = :ug_board,
        ug_pass_year = :ug_pass_year,
        ug_reg_no = :ug_reg_no,
        ug_grade = :ug_grade,
        ug_max_marks = :ug_max_marks,
        cert_return_mode = :cert_return_mode,
        sslc_file = :sslc_file,
        hsc_file = :hsc_file,
        ug_file = :ug_file,
        tc_file = :tc_file,
        migration_file = :migration_file,
        undertaking_file = :undertaking_file,
        signature_file = :signature_file,
        enclosures = :enclosures
        WHERE application_no = :application_no";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':other_course' => $other_course,
        ':other_course_details' => $other_course_details,
        ':defence_personnel' => $defence_personnel,
        ':ex_servicemen' => $ex_servicemen,
        ':sslc_school' => $_POST['sslc_school'],
        ':sslc_board' => $_POST['sslc_board'],
        ':sslc_pass_year' => $_POST['sslc_pass_year'],
        ':sslc_reg_no' => $_POST['sslc_reg_no'],
        ':sslc_grade' => $_POST['sslc_grade'],
        ':sslc_max_marks' => $_POST['sslc_max_marks'],
        ':hsc_school' => $_POST['hsc_school'],
        ':hsc_board' => $_POST['hsc_board'],
        ':hsc_pass_year' => $_POST['hsc_pass_year'],
        ':hsc_reg_no' => $_POST['hsc_reg_no'],
        ':hsc_grade' => $_POST['hsc_grade'],
        ':hsc_max_marks' => $_POST['hsc_max_marks'],
        ':dip_school' => $_POST['dip_school'],
        ':dip_board' => $_POST['dip_board'],
        ':dip_pass_year' => $_POST['dip_pass_year'],
        ':dip_reg_no' => $_POST['dip_reg_no'],
        ':dip_grade' => $_POST['dip_grade'],
        ':dip_max_marks' => $_POST['dip_max_marks'],
        ':ug_school' => $_POST['ug_school'],
        ':ug_board' => $_POST['ug_board'],
        ':ug_pass_year' => $_POST['ug_pass_year'],
        ':ug_reg_no' => $_POST['ug_reg_no'],
        ':ug_grade' => $_POST['ug_grade'],
        ':ug_max_marks' => $_POST['ug_max_marks'],
        ':cert_return_mode' => $cert_return_mode,
        ':sslc_file' => $files['sslc'] ?? null,
        ':hsc_file' => $files['hsc'] ?? null,
        ':ug_file' => $files['ug'] ?? null,
        ':tc_file' => $files['tc'] ?? null,
        ':migration_file' => $files['migration'] ?? null,
        ':undertaking_file' => $files['undertaking'] ?? null,
        ':signature_file' => $files['signature'] ?? null,
        ':enclosures' => $enclosures,
        ':application_no' => $appNo
    ]);
    header("Location: preview.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admission Application</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  
<!-- HEADER -->

<header class="top-header">

    <div class="head-container">

        <div class="header-top">
        <div class="logo-section">

<img
    src="image/Univ.png"
    alt="University Logo"
    loading="lazy"
>

</div>

<div class="title-section">

<div class="tamil-text">
சென்னை பல்கலைக்கழகம் – தொலைதூரக் கல்வி நிறுவனம்
</div>

<div class="english-text">
University of Madras – Institute of Distance Education
</div>

<div class="sub-text">
Affiliated to University of Madras | NAAC Accredited with Grade “A++”
<br>
A Premier Distance Education Institution
<br>
Chepauk Campus, Chennai – 600 005
</div>
        </div>

    </div>

</header>


<nav class="navbar">

<div class="nav-container">

<div
    class="menu-toggle"
    id="menuToggle"
    aria-label="Toggle navigation"
    role="button"
    tabindex="0"
>
☰
</div>

<?php

$currentPage = basename($_SERVER['PHP_SELF']);

?>


<div class="nav-links" id="navLinks">

<a href="dashboard.php"
class="<?php echo ($currentPage == 'dashboard.php') ? 'active' : ''; ?>">

<i class="fa fa-home"></i>
Dashboard

</a>
|
<a href="new_application1.php"
class="<?php echo ($currentPage == 'new_application1.php') ? 'active' : ''; ?>">

<i class="fa fa-user-plus"></i>
New Admission

</a>
|
<a href="reintimation.php"
class="<?php echo ($currentPage == 'reintimation.php') ? 'active' : ''; ?>">

<i class="fa fa-file-alt"></i>
Re-Intimation

</a>
|
<a href="payment.php"
class="<?php echo ($currentPage == 'payment.php') ? 'active' : ''; ?>">

<i class="fa fa-credit-card"></i>
Fee Payment

</a>
|
<a href="list.php"
class="<?php echo ($currentPage == 'list.php') ? 'active' : ''; ?>">

<i class="fa fa-list"></i>
Applications

</a>
|
<a href="logout.php">

<i class="fa fa-sign-out-alt"></i>
Logout

</a>

</div>

</div>
</nav>


</div>

</header>



</div>

</header>



<!-- BANNER --> 
 
<section class="banner">
 <main class="container">
    <header class="form-header">
        <h1>ADMISSION APPLICATION FORM</h1>
        <p>STEP-2
        <p>Please fill all details in CAPITAL LETTERS</p>
    </header>
    <form method="POST" enctype="multipart/form-data" autocomplete="off">
      <!-- ===================== -->
<!-- ADDITIONAL DETAILS -->
<!-- ===================== -->
<fieldset>
  <legend>ADDITIONAL INFORMATION</legend>
  <!-- Q9 -->
  <!-- ===================== -->
<!-- Q24 OTHER COURSE -->
<!-- ===================== -->
<div class="form-row">
  <label>
    24. Are you undergoing any other course in a College / University?
    <span class="required-star">*</span>
  </label>
  <!-- YES / NO -->
  <div class="radio-group">
    <label class="radio-item">
      <input type="radio" name="other_course" value="Yes" required> Yes
    </label>
    <label class="radio-item">
      <input type="radio" name="other_course" value="No"> No
    </label>
  </div>
  <!-- TEXTBOX (Hidden by default) -->
  <div id="otherCourseBox"
       style="display:none; margin-top:10px;">
    <input type="text" name="other_course_details" placeholder="If yes, specify course / college">
  </div>
</div>
  <!-- Q10 -->
  <div class="form-row">
    <label>25. Ward of Defence Personnel / Ex-Servicemen <span class="required-star">*</span></label>
    <div class="inline">
     <div class="radio-group">

<label class="radio-item">
<input type="radio" name="defence_status" value="defence" required>
Defence Personnel
</label>

<label class="radio-item">
<input type="radio" name="defence_status" value="ex">
Ex-Servicemen
</label>

<label class="radio-item">
<input type="radio" name="defence_status" value="none">
None
</label>

</div>
    </div>
  </div>
</fieldset>
<!-- ===================== -->
<!-- EXAMINATION DETAILS -->
<!-- ===================== -->
<fieldset>
  <legend>26. DETAILS OF EXAMINATION PASSED <span class="required-star">*</span></legend>
  <div class="table-wrapper">
  <table class="exam-table">
    <thead>
      <tr>
        <th>Examination Passed</th>
        <th>Name of School / College</th>
        <th>Name of Board / University</th>
        <th>Month & Year of Passing</th>
        <th>Registration No</th>
        <th>Class with Grade / Marks</th>
        <th>Maximum Marks</th>
      </tr>
    </thead>
<tbody>
<tr>
  <td>S.S.L.C / 10th Std</td>
  <td><input type="text" name="sslc_school"></td>
  <td><input type="text" name="sslc_board"></td>
  <td><input type="text" name="sslc_pass_year" id="sslc_pass_year" placeholder="MM/YYYY"></td>
  <td><input type="text" name="sslc_reg_no"></td>
  <td><input type="text" name="sslc_grade"></td>
  <td><input type="text" name="sslc_max_marks"></td>
</tr>
<tr>
  <td>H.S.C / Higher Secondary</td>
  <td><input type="text" name="hsc_school"></td>
  <td><input type="text" name="hsc_board"></td>
  <td><input type="text" name="hsc_pass_year" id="hsc_pass_year" placeholder="MM/YYYY"></td>
  <td><input type="text" name="hsc_reg_no"></td>
  <td><input type="text" name="hsc_grade"></td>
  <td><input type="text" name="hsc_max_marks"></td>
</tr>
</tbody>
      <tr>
        <td>Diploma Course</td>
        <td><input type="text" name="dip_school"></td>
        <td><input type="text" name="dip_board"></td>
        <td><input type="text" name="dip_pass_year" id="dip_pass_year" placeholder="MM/YYYY"></td>
        <td><input type="text" name="dip_reg_no"></td>
        <td><input type="text" name="dip_grade"></td>
        <td><input type="text" name="dip_max_marks"></td>
      </tr>
      <tr>
        <td>Under Graduate</td>
        <td><input type="text" name="ug_school"></td>
        <td><input type="text" name="ug_board"></td>
        <td><input type="text" name="ug_pass_year" id="ug_pass_year" placeholder="MM/YYYY"></td>
        <td><input type="text" name="ug_reg_no"></td>
        <td><input type="text" name="ug_grade"></td>
        <td><input type="text" name="ug_max_marks"></td>
      </tr>
    </tbody>
  </table>
</div>
      </tbody>
    </table>
  </div>
</fieldset>
<!-- ABC ID removed from Step 2 as it was moved to Step 1 -->
<!-- ===================== -->
<!-- ENCLOSURES SECTION -->
<!-- ===================== -->
<!-- ===================== -->
<!-- DOCUMENT UPLOAD -->
<!-- ===================== -->
<?php if($DOCUMENT_UPLOAD_ENABLED): ?>
<fieldset>
  <legend>DOCUMENT UPLOAD (MAX 2MB EACH)</legend>
  <!-- SSLC -->
  <div class="upload-row">
    <label>SSLC Statement <span class="required-star">*</span></label>
    <input type="file"
           name="sslc"
           id="file_sslc"
           accept=".pdf,.jpg,.jpeg,.png">
    <div class="error-text" id="error_sslc"></div>
  </div>
  <!-- HSC -->
  <div class="upload-row">
    <label>HSC / Diploma Statement <span class="required-star">*</span></label>
    <input type="file" name="hsc" id="file_hsc" accept=".pdf,.jpg,.jpeg,.png">
    <div class="error-text" id="error_hsc"></div>
  </div>
  <!-- UG -->
  <div class="upload-row">
    <label>UG / Provisional Statement (PG Only)</label>
    <input type="file" name="ug" id="file_ug" accept=".pdf,.jpg,.jpeg,.png">
    <div class="error-text" id="error_ug"></div>
  </div>
  <!-- TC -->
  <div class="upload-row">
    <label>Transfer Certificate<span class="required-star">*</span></label>
    <input type="file" name="tc" id="file_tc" accept=".pdf,.jpg,.jpeg,.png">
    <div class="error-text" id="error_tc"></div>
  </div>
  <!-- Migration -->
  <div class="upload-row">
    <label>Migration Certificate</label>
    <input type="file" name="migration" id="file_migration" accept=".pdf,.jpg,.jpeg,.png">
    <div class="error-text" id="error_migration"></div>
  </div>
  <!-- Undertaking -->
  <div class="upload-row">
    <label>Undertaking</label>
    <input type="file" name="undertaking" id="file_undertaking" accept=".pdf,.jpg,.jpeg,.png">
    <div class="error-text" id="error_undertaking"></div>
  </div>
</fieldset>
<fieldset>
  <legend>28.ENCLOSURES</legend>
  <div class="form-row">
    <div class="inline">
      <label>
        <input type="checkbox" id="enc_sslc" name="enclosures[]" value="SSLC" disabled> S.S.L.C Statement of Marks
      </label>
    </div>
  </div>
  <div class="form-row">
    <div class="inline">
      <label>
        <input type="checkbox" id="enc_hsc" name="enclosures[]" value="HSC" disabled> HSC Statement of Marks / Diploma Statement of Marks
      </label>
    </div>
  </div>
  <div class="form-row">
    <div class="inline">
      <label>
        <input type="checkbox" id="enc_ug" name="enclosures[]" value="UG" disabled> UG Statement of Marks / Provisional / Degree
      </label>
    </div>
  </div>
  <div class="form-row">
    <div class="inline">
      <label>
        <input type="checkbox" id="enc_tc" name="enclosures[]" value="Transfer Certificate" disabled> Transfer Certificate / Course Completion Certificate
      </label>
    </div>
  </div>
  <div class="form-row">
    <div class="inline">
      <label>
        <input type="checkbox" id="enc_migration" name="enclosures[]" value="Migration" disabled> Migration Certificate (Other State Students only)
      </label>
    </div>
  </div>
  <div class="form-row">
    <div class="inline">
      <label>
        <input type="checkbox" id="enc_undertaking" name="enclosures[]" value="Undertaking" disabled> Undertaking (if any – Certificates due)
      </label>
    </div>
  </div>
  <div class="form-row">
    <label>Certificate Return Mode <span class="required-star">*</span></label>
    <div class="radio-group">
      <label class="radio-item"><input type="radio" name="cert_return_mode" value="CC Returned" required> CC Returned</label>
      <label class="radio-item"><input type="radio" name="cert_return_mode" value="Postal Return" required> Postal Return</label>
    </div>
  </div>
</fieldset>

<?php endif; ?>
<!-- ===================== -->
<!-- DECLARATION SECTION -->
<!-- ===================== -->
<fieldset>
  <legend> DECLARATION</legend>
  <p style="margin-bottom:15px; font-size:14px; line-height:1.6;">
    <input type="checkbox" id="declaration" name="declaration" value="1" required>
    I hereby declare that all the particulars given above are correct and 
    I agree to abide by all the Rules and Regulations of the University 
    that are in force from time to time.
  </p>
  <div class="form-grid">
    <div>
      <label>Station :</label>   
    </div><br>
    <div>
      <label>Date :</label>
    </div> <br><br><br>
  <div class="sign">
<label><strong>Signature of the Applicant<span class="required-star">*</span></strong></label><br><br>
<!--
<input type="file"
       name="signature"
       id="file_signature"
       accept=".jpg,.jpeg,.png"
       required>

<div class="error-text" id="error_signature"></div>

-->
</div>
   </div> 
</fieldset>
      <div class="actions">
        <button type="submit" class="btn btn-outline">SUBMIT</button>
        <button type="reset" class="secondary">RESET</button>
        <button type="submit" name="back" value="1" class="btn btn-outline">  BACK
        </button>
      </div>
</form>
</main>
</section>


<!-- FOOTER -->

<footer>

<div class="about-ide">

<h2>About the Institute of Distance Education</h2>

<p>
The Institute of Correspondence Education (ICE), now called the
Institute of Distance Education (IDE), was established in 1981.
</p>

<p>
Having completed 43 years, IDE today is a mega institute with more than
one lakh learners.
</p>

<p>
IDE offers <strong>73 Programmes</strong> including UG, PG, Diploma and Certificate courses.
</p>

<p>
Admissions are open throughout the year in both Academic Year (July–June)
and Calendar Year (January–December).
</p>

<p>
69 Learner Support Centres have been established and online admission
facility has been introduced.
</p>

<p>
© 2025 University of Madras. All Rights Reserved.
</p>

</div>

</footer>


<script src="script.js"></script>

<?php
$formDataJson = json_encode($edit_data);
?>
<script>
const formEditData = <?= $formDataJson ?>;
document.addEventListener("DOMContentLoaded", function () {
    if (!formEditData) return;
    
    for (const key in formEditData) {
        const val = formEditData[key];
        if (val === null || val === undefined || val === '') continue;

        const inputs = document.querySelectorAll(`[name="${key}"]`);
        if (inputs.length === 0) continue;

        if (inputs[0].type === 'radio') {
            inputs.forEach(r => {
                if (r.value === val) r.checked = true;
            });
        } else if (inputs[0].type === 'checkbox') {
            if (val == 1 || val == 'Yes' || val == 'yes') {
                inputs[0].checked = true;
            }
        } else {
            inputs[0].value = val;
        }
    }
});
</script>

</body>
</html>