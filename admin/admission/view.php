<?php
session_start();
require_once "../../db.php";

if(!isset($_SESSION['admin'])){
    header("Location: ../login.php");
    exit();
}

/* ── Ensure approved_category and certificate_verified columns exist ── */
try {
    $pdo->exec("ALTER TABLE records ADD COLUMN IF NOT EXISTS approved_category VARCHAR(20) DEFAULT 'GENERAL'");
    $pdo->exec("ALTER TABLE records ADD COLUMN IF NOT EXISTS certificate_verified SMALLINT DEFAULT 0");
} catch (PDOException $e) {
    // Ignore if columns already exist
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if($id <= 0){
    die("Invalid Application ID.");
}

/* APPROVE CATEGORY */
if(isset($_POST['approve_category'])){
    $cat = trim($_POST['approve_category']);
    $stmt = $pdo->prepare("
        UPDATE records
        SET approved_category = ?
        WHERE id = ?
    ");
    $stmt->execute([$cat, $id]);
    header("Location: view.php?id=" . $id);
    exit;
}
if(isset($_POST['verify_cert'])){
    $update = $pdo->prepare("UPDATE records SET certificate_verified = 1 WHERE id = ?");
    $update->execute([$id]);
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

/* Fetch Application */
$stmt = $pdo->prepare("
    SELECT r.*, s.state_name 
    FROM records r
    LEFT JOIN states s ON r.state = s.id
    WHERE r.id=?
");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

/* SPECIAL CATEGORY APPROVAL */

if(isset($_POST['approve_concession'])){

$stmt = $pdo->prepare("
UPDATE records
SET concession_approved='yes'
WHERE id=?
");

$stmt->execute([$id]);

header("Location:view.php?id=".$id);
exit;


    die("Application not found.");
}

if(empty($data['status'])){
    $data['status'] = "Pending";
}

/* Read approved_category safely */
$approved_category = strtoupper($data['approved_category'] ?? 'GENERAL');


/* STATUS UPDATE */
if($_SERVER['REQUEST_METHOD'] === "POST"){

    $remark = trim($_POST['remark'] ?? '');
    $action = $_POST['action'] ?? '';
    $admin  = $_SESSION['admin'];

    if(empty($remark)){
        die("Remark is required.");
    }

    switch($action){

        case "approve":

            $status = "Approved";

            if(empty($data['medium'])){
                die("Please select medium before approving.");
            }

            /* ================= ENROLLMENT GENERATION ================= */

            if(empty($data['enrollment_no'])){

                $month = date("n");
                $period = ($month <= 6) ? "A" : "C";

                $year = date("y");
                $centerCode = !empty($data['lsc_code']) ? $data['lsc_code'] : "101";

                /* Course Table */
                if($data['course_type'] == "UG"){
                    $courseTable = "ug_courses";
                }
                elseif($data['course_type'] == "PG"){
                    $courseTable = "pg_courses";
                }
                elseif($data['course_type'] == "DIP"){
                    $courseTable = "diploma_courses";
                }
                else{
                    $courseTable = "certificate_courses";
                }

                /* Get Course Code */
                $getCourse = $pdo->prepare("
                    SELECT course_code
                    FROM $courseTable
                    WHERE LOWER(REPLACE(TRIM(programme_degree),'.','')) = LOWER(REPLACE(TRIM(?),'.',''))
                    AND LOWER(TRIM(main_subject)) = LOWER(TRIM(?))
                    LIMIT 1
                ");

                $getCourse->execute([
                    $data['programme_name'],
                    $data['main_subject']
                ]);
                $courseRow = $getCourse->fetch(PDO::FETCH_ASSOC);

                if(!$courseRow){
                    $err = "<strong>Course code not found.</strong><br><br>";
                    $err .= "The system could not find a course matching: <br>";
                    $err .= "Course Type: <b>" . htmlspecialchars($data['course_type']) . "</b><br>";
                    $err .= "Programme: <b>" . htmlspecialchars($data['programme_name']) . "</b><br>";
                    $err .= "Subject: <b>" . htmlspecialchars($data['main_subject']) . "</b><br><br>";
                    $err .= "This error occurs when:<br>";
                    $err .= "1. The programme name in the application does not match the database<br>";
                    $err .= "2. The main subject does not match the database (e.g., 'Select Subject')<br>";
                    $err .= "3. The wrong course table is being used (UG/PG/DIP/CERT)<br>";
                    $err .= "4. The course is missing in the database<br>";
                    die($err);
                }

                $courseCode = strtoupper(trim($courseRow['course_code']));

                /* PREFIX */
                $prefix = $period.$year.$centerCode.$courseCode;

               /* MEDIUM */
$medium = strtolower(trim($data['medium']));

if(strpos($medium, "english") !== false){
    $startNumber = 6001;
}
elseif(strpos($medium, "tamil") !== false){
    $startNumber = 1001;
}
else{
    die("Invalid medium: " . htmlspecialchars($data['medium']));
}

/* LOCK */
$lock = $pdo->query("SELECT pg_try_advisory_lock(hashtext('enroll_lock')) AS l")->fetch(PDO::FETCH_ASSOC);

if(!$lock['l']){
    die("System busy. Try again.");
}

/* GET LAST NUMBER (SEPARATE BY LSC/DIRECT AND MEDIUM) */
if(empty($data['lsc_code'])){
    $check = $pdo->prepare("
        SELECT MAX(CAST(RIGHT(enrollment_no, 4) AS INTEGER)) AS last_number
        FROM records
        WHERE LOWER(TRIM(medium)) = ? AND (lsc_code IS NULL OR lsc_code = '')
        AND CAST(RIGHT(enrollment_no, 4) AS INTEGER) >= ?
        AND CAST(RIGHT(enrollment_no, 4) AS INTEGER) NOT IN (1701, 1702)
    ");
    $check->execute([$medium, $startNumber]);
} else {
    $check = $pdo->prepare("
        SELECT MAX(CAST(SUBSTRING(enrollment_no, LENGTH(enrollment_no) - 3) AS INT)) AS last_number
        FROM records
        WHERE LOWER(TRIM(medium)) = ? AND lsc_code = ?
        AND CAST(SUBSTRING(enrollment_no, LENGTH(enrollment_no) - 3) AS INT) >= ?
        AND CAST(SUBSTRING(enrollment_no, LENGTH(enrollment_no) - 3) AS INT) NOT IN (1701, 1702)
    ");
    $check->execute([$medium, $data['lsc_code'], $startNumber]);
}

$res = $check->fetch(PDO::FETCH_ASSOC);

/* NEXT NUMBER */
if($res['last_number'] !== null){
    $newNumber = $res['last_number'] + 1;
} else {
    $newNumber = $startNumber;
}

/* SKIP BAD NUMBERS IF SEQUENCE REACHES THEM */
while(in_array($newNumber, [1701, 1702])){
    $newNumber++;
}

/* FORMAT */
$newNumber = str_pad($newNumber, 4, "0", STR_PAD_LEFT);

/* FINAL ENROLLMENT */
$enrollmentNo = $prefix . $newNumber;

/* SAVE */
$save = $pdo->prepare("
    UPDATE records SET enrollment_no=? WHERE id=?
");
$save->execute([$enrollmentNo, $id]);

/* RELEASE LOCK */
$pdo->query("SELECT pg_advisory_unlock(hashtext('enroll_lock'))");
            }

        break;

        case "reject":
            $status = "Rejected";
        break;

        case "pending":
            $status = "Pending";
        break;

        default:
            $status = $data['status'];
    }

    /* FINAL UPDATE */
    $update = $pdo->prepare("
        UPDATE records 
        SET status=?, staff_remark=?, processed_by=?, processed_at=NOW()
        WHERE id=?
    ");

    $update->execute([$status, $remark, $admin, $id]);

    header("Location: view.php?id=".$id);
    exit();
} /* PHOTO PATH */
/* ================= FILE & PHOTO PATH ================= */

/* SERVER STORAGE PATH */
$uploadPath = "C:/xampp/htdocs/admission/admission-form/uploads/" 
            . trim($data['application_no']) . "/";

/* BROWSER URL */
$uploadURL = "/admission/admission-form/uploads/" 
           . trim($data['application_no']) . "/";

/* PHOTO */
$photoFile = trim($data['photo'] ?? '');

$photoPath = $uploadPath . $photoFile;

$photoURL = $uploadURL . rawurlencode($photoFile);




$statusClass = "badge-pending";
if($data['status']=="Approved") $statusClass="badge-approved";
elseif($data['status']=="Rejected") $statusClass="badge-rejected";
?>
<!DOCTYPE html>
<html>
<head>
<title>University Admin Review</title>
<link rel="stylesheet" href="../assets/style.css">

<style>
.main-card{
    background:#fff;
    padding:25px;
    border-radius:8px;
    box-shadow:0 3px 12px rgba(0,0,0,0.1);
}

.header-flex{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
}

.photo-box img{
    width:150px;
    height:180px;
    object-fit:cover;
    border:2px solid #000000;
    border-radius:6px;
}

.badge-approved{background:green;color:#fff;padding:6px 14px;border-radius:20px;}
.badge-rejected{background:red;color:#fff;padding:6px 14px;border-radius:20px;}
.badge-pending{background:orange;color:#fff;padding:6px 14px;border-radius:20px;}

.section{
    margin-top:20px;
    border:1px solid #030303;
    border-radius:6px;
    padding:15px;
}

.section h3{
    margin-top:0;
    border-bottom:2px solid #0c0c0c;
    padding-bottom:6px;
    color:#0c0c0c;
}

.details-table{
    width:100%;
    border-collapse:collapse;
}

.details-table td{
    padding:6px 10px;
    border-bottom:1px solid #eee;
}

.details-table td:first-child{
    font-weight:bold;
    width:30%;
}

.doc-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:10px;
}

.doc-btn{
    display:block;
    background:#003366;
    color:#fff;
    padding:8px;
    text-align:center;
    border-radius:4px;
    text-decoration:none;
}

.approval-box{
    margin-top:30px;
    border:1px solid #ccc;
    padding:15px;
    border-radius:6px;
    background:#f9f9f9;
    
}

textarea{
    width:98%;
    padding:15px;
}

</style>
</head>

<body>

<div class="sidebar">
<h2>Distance Education</h2>
<a href="list.php">← Back</a>
<a href="../dashboard.php">Dashboard</a>
<a href="../logout.php">Logout</a>
</div>

<div class="main">
<div class="main-card">

<!-- HEADER -->
<!-- HEADER -->
<div class="header-flex">

<div>
<h2>
Application No: <?php echo htmlspecialchars($data['application_no']); ?>

<?php if(strtolower(trim($data['status'])) == "approved" 
      && !empty($data['enrollment_no'])): ?>
    <br>
    <span style="font-size:22px;color:#003366;">
        Enrollment No: 
        <strong>
            <?php echo htmlspecialchars($data['enrollment_no']); ?>
        </strong>
    </span>
<?php endif; ?>
</h2>

<!-- STATUS BADGE -->
<p>
<span class="<?php echo $statusClass; ?>">
<?php echo htmlspecialchars($data['status']); ?>
</span>
</p>

<p><strong>Processed By:</strong> <?php echo $data['processed_by'] ?? '-'; ?></p>
<p><strong>Processed At:</strong> <?php echo $data['processed_at'] ?? '-'; ?></p>

<!-- PRINT & EDIT BUTTONS -->
<div style="margin-top:15px;">
<a href="print.php?id=<?php echo $data['id']; ?>" 
   target="_blank" 
   class="doc-btn" 
   style="display:inline-block;width:auto;padding:8px 15px;">
   Print Application
</a>

<a href="edit.php?id=<?php echo $data['id']; ?>" 
   class="doc-btn" 
   style="display:inline-block;width:auto;padding:8px 15px;background:#444;margin-left:10px;">
   Edit Application
</a>
</div>

</div>

<!-- PHOTO -->
<!-- PHOTO -->
<div class="photo-box">

<?php if(!empty($photoFile) && file_exists($photoPath)): ?>

    <img src="<?php echo htmlspecialchars($photoURL); ?>" 
    style="
        width:150px;
        height:180px;
        object-fit:cover;
        border:2px solid #000;
        border-radius:6px;
    ">

<?php else: ?>

    <div style="
        width:150px;
        height:180px;
        border:1px solid #000;
        display:flex;
        align-items:center;
        justify-content:center;
    ">
        No Photo
    </div>
    

<?php endif; ?>
</div>
</div>
<!-- COURSE DETAILS -->
<div class="section">
<h3> COURSE DETAILS</h3>
<table class="details-table">
<tr><td>Course Type</td><td><?php echo $data['course_type']; ?></td></tr>
<tr><td>Programme</td><td><?php echo $data['programme_name']; ?></td></tr>
<tr><td>Main Subject</td><td><?php echo $data['main_subject']; ?></td></tr>
<tr><td>Foundation Language</td><td><?php echo $data['foundation_lang']; ?></td></tr>
<tr><td>Medium</td><td><?php echo $data['medium']; ?></td></tr>
<tr>
<td>Specially Challenged</td>
<td>
<?php echo !empty($data['differently_abled']) 
        ? htmlspecialchars($data['differently_abled']) 
        : 'No'; ?>
</td>
</tr>
</table>
</div>

<!-- PERSONAL DETAILS -->
<div class="section">
<h3> PERSONAL DETAILS</h3>

<div style="display:flex; gap:30px; align-items:flex-start;">

<!-- LEFT COLUMN -->
<table class="details-table" style="width:50%;">
<tr><td>Name</td><td><?php echo $data['name'] ?? '-'; ?></td></tr>
<tr><td>Name (Tamil)</td><td><?php echo $data['name_tamil'] ?? '-'; ?></td></tr>
<tr><td>DOB</td><td><?php echo $data['dob'] ?? '-'; ?></td></tr>
<tr><td>Age</td><td><?php echo $data['age'] ?? '-'; ?></td></tr>
<tr><td>Gender</td><td><?php echo $data['gender'] ?? '-'; ?></td></tr>
<tr><td>Mobile</td><td><?php echo $data['mobile'] ?? '-'; ?></td></tr>
<tr><td>Email</td><td><?php echo $data['email'] ?? '-'; ?></td></tr>
<tr><td>Nationality</td><td><?php echo $data['nationality'] ?? '-'; ?></td></tr>
<tr><td>Mother Tongue</td><td><?php echo $data['mother_tongue'] ?? '-'; ?></td></tr>
</table>

<!-- RIGHT COLUMN -->
<table class="details-table" style="width:50%;">
<tr><td>Father Name</td><td><?php echo $data['guardian_name'] ?? '-'; ?></td></tr>
<tr><td>Mother Name</td><td><?php echo $data['mother_name'] ?? '-'; ?></td></tr>
<tr><td>Aadhaar</td><td><?php echo $data['aadhaar'] ?? '-'; ?></td></tr>
<tr><td>Religion</td><td><?php echo $data['religion'] ?? '-'; ?></td></tr>
<tr><td>Community</td><td><?php echo $data['community'] ?? '-'; ?></td></tr>
<tr><td>Caste</td><td><?php echo $data['caste'] ?? '-'; ?></td></tr>
<tr><td>Employment Status</td>
<td>
<?php
if(!empty($data['employment_status'])){
    echo ucfirst($data['employment_status']);
}else{
    echo '-';
}
?>
</td>
</tr>

<tr><td>Employment Details</td>
<td>
<?php echo $data['employment_type'] ?? '-'; ?>
</td>
</tr>
</table>

</div>
</div>
<!-- ADDRESS -->
<div class="section">
<h3> ADDRESS FOR COMMUNICATION</h3>
<table class="details-table">
<tr><td>Street</td><td><?php echo $data['street']; ?></td></tr>
<tr><td>Town</td><td><?php echo $data['town']; ?></td></tr>
<tr><td>District</td><td><?php echo $data['district']; ?></td></tr>
<tr><td>State</td><td><?php echo $data['state_name']; ?></td></tr>
<tr><td>Pincode</td><td><?php echo $data['pincode']; ?></td></tr>
<tr><td>Phone</td><td><?php echo $data['phone']; ?></td></tr>
<tr>
</td>
</tr>
</table>
</div>
<!-- ADDITIONAL INFORMATION -->
<div class="section">
<h3> ADDITIONAL INFORMATION</h3>

<table class="details-table">

<tr>
<td>ABC Status</td>
<td>
<?php echo htmlspecialchars($data['abc_status'] ?? 'No'); ?>
</td>
</tr>

<tr>
<td>ABC ID</td>
<td>
<?php 
if(!empty($data['abc_id'])){
    echo chunk_split($data['abc_id'],4,' ');
}else{
    echo 'Not Available';
}
?>


</td>
</tr>

<tr>
<td>Undergoing Other Course</td>
<td>
<?php echo htmlspecialchars($data['other_course'] ?? 'No'); ?>
</td>
</tr>

<tr>
<td>Other Course Details</td>
<td>
<?php echo !empty($data['other_course_details']) 
        ? htmlspecialchars($data['other_course_details']) 
        : '-'; ?>
</td>
</tr>

<tr>
<td>Ward of Defence</td>
<td>
<?php
if(!empty($data['defence_personnel'])){
    echo "Defence Personnel";
}
elseif(!empty($data['ex_servicemen'])){
    echo "Ex-Servicemen";
}
else{
    echo "None";
}
?>
</td>
</tr>

</table>
</div>

<!-- DOCUMENTS -->
<div class="section">
<h3> UPLOADED DOCUMENT</h3>

<div class="doc-grid">
<?php
$files = [
'sslc_file'=>'SSLC',
'hsc_file'=>'HSC',
'ug_file'=>'UG',
'tc_file'=>'Transfer Certificate',
'migration_file'=>'Migration Certificate',
'undertaking_file'=>'Undertaking',
'disability_certificate'=>'Differently Abled Certificate'
];

foreach($files as $key => $label){

    if(!empty($data[$key])){

        $fileName = trim($data[$key]);

        /* REAL FILE PATH */
        $filePath = $uploadPath . $fileName;

        /* BROWSER URL */
        $fileURL = $uploadURL . rawurlencode($fileName);

        echo '
        <a class="doc-btn"
           target="_blank"
           href="'.htmlspecialchars($fileURL).'">
           '.$label.'
        </a>';
    }
}

?>
</div>
</div>

<div class="section">
<h3 style="font-weight:bold; letter-spacing:1px;">EXAMINATION DETAILS</h3>

<table class="exam-table">
<tr>
    <th>Exam</th>
    <th>Institution</th>
    <th>Board</th>
    <th>Year</th>
    <th>Reg No</th>
    <th>Grade</th>
    <th>Max Marks</th>
</tr>

<tr>
    <td>SSLC</td>
    <td><?php echo $data['sslc_school'] ?? '-'; ?></td>
    <td><?php echo $data['sslc_board'] ?? '-'; ?></td>
    <td><?php echo $data['sslc_year'] ?? '-'; ?></td>
    <td><?php echo $data['sslc_reg_no'] ?? '-'; ?></td>
    <td><?php echo $data['sslc_grade'] ?? '-'; ?></td>
    <td><?php echo $data['sslc_max_marks'] ?? '-'; ?></td>
</tr>

<tr>
    <td>HSC</td>
    <td><?php echo $data['hsc_school'] ?? '-'; ?></td>
    <td><?php echo $data['hsc_board'] ?? '-'; ?></td>
    <td><?php echo $data['hsc_year'] ?? '-'; ?></td>
    <td><?php echo $data['hsc_reg_no'] ?? '-'; ?></td>
    <td><?php echo $data['hsc_grade'] ?? '-'; ?></td>
    <td><?php echo $data['hsc_max_marks'] ?? '-'; ?></td>
</tr>

<tr>
    <td>DIP</td>
    <td><?php echo $data['dip_school'] ?? '-'; ?></td>
    <td><?php echo $data['dip_board'] ?? '-'; ?></td>
    <td><?php echo $data['dip_year'] ?? '-'; ?></td>
    <td><?php echo $data['dip_reg_no'] ?? '-'; ?></td>
    <td><?php echo $data['dip_grade'] ?? '-'; ?></td>
    <td><?php echo $data['dip_max_marks'] ?? '-'; ?></td>
</tr>

<tr>
    <td>UG</td>
    <td><?php echo $data['ug_school'] ?? '-'; ?></td>
    <td><?php echo $data['ug_board'] ?? '-'; ?></td>
    <td><?php echo $data['ug_year'] ?? '-'; ?></td>
    <td><?php echo $data['ug_reg_no'] ?? '-'; ?></td>
    <td><?php echo $data['ug_grade'] ?? '-'; ?></td>
    <td><?php echo $data['ug_max_marks'] ?? '-'; ?></td>
</tr>

</table>
</div>

<div class="section">
<h3>Application Status Details</h3>

<table class="details-table">
<tr>
<td>Status</td>
<td>
<span class="<?php echo $statusClass; ?>">
<?php echo htmlspecialchars($data['status']); ?>
</span>
</td>
</tr>

<tr>
<td>Approved / Rejected By</td>
<td><?php echo htmlspecialchars($data['processed_by'] ?? '-'); ?></td>
</tr>

<tr>
<td>Processed Date & Time</td>
<td><?php echo htmlspecialchars($data['processed_at'] ?? '-'); ?></td>
</tr>

<tr>
<td>Staff Remark</td>
<td><?php echo htmlspecialchars($data['staff_remark'] ?? 'Not Updated'); ?></td>
</tr>
</table>

</div>


<div style="margin-top:20px;background:#fff3cd;padding:12px;border-radius:6px;">
<b>Approve Fee Concession</b>

<form method="post" style="margin-top:10px;">

<div class="btn-group">

<button type="submit" name="approve_category" value="GENERAL"
class="btn btn-sm <?php if($approved_category=='GENERAL') echo 'btn-success'; else echo 'btn-outline-secondary'; ?>">
General
</button>

<button type="submit" name="approve_category" value="VC"
class="btn btn-sm <?php if($approved_category=='VC') echo 'btn-success'; else echo 'btn-outline-secondary'; ?>">
VC
</button>

<button type="submit" name="approve_category" value="DA"
class="btn btn-sm <?php if($approved_category=='DA') echo 'btn-success'; else echo 'btn-outline-secondary'; ?>">
DA
</button>

<button type="submit" name="approve_category" value="PRISONER"
class="btn btn-sm <?php if($approved_category=='PRISONER') echo 'btn-success'; else echo 'btn-outline-secondary'; ?>">
Prisoner
</button>

<button type="submit" name="approve_category" value="STAFF"
class="btn btn-sm <?php if($approved_category=='STAFF') echo 'btn-success'; else echo 'btn-outline-secondary'; ?>">
Staff
</button>

</div>

</form>
</div>

<form method="POST" style="margin-top:15px;">

    <?php if(($data['certificate_verified'] ?? 0) == 0): ?>

        <button type="submit" name="verify_cert"
        style="background:green; color:white; padding:8px 15px; border:none; border-radius:5px;">
            Verify Certificates
        </button>

    <?php else: ?>

        <button disabled
        style="background:gray; color:white; padding:8px 15px; border:none; border-radius:5px;">
            Already Verified
        </button>

    <?php endif; ?>

</form>
<p>
<b>Certificate Status :</b> 
<?php 
echo (($data['certificate_verified'] ?? 0) == 1) 
    ? '<span style="color:green;">Verified</span>' 
    : '<span style="color:red;">Not Verified</span>';
?>
</p>




<!-- APPROVAL -->
<div class="approval-box">
<h3>APPROVAL PANEL</h3>

<form method="POST">

<label><b>Select Remark</b></label><br><br>

<label>
    <input type="radio" name="remark" value="Documents Verified" required>
    Documents Verified
</label><br>

<label>
    <input type="radio" name="remark" value="Eligible for Admission">
    Eligible for Admission
</label><br>

<label>
    <input type="radio" name="remark" value="Incomplete Documents">
    Incomplete Documents
</label><br>

<label>
    <input type="radio" name="remark" value="Rejected due to mismatch">
    Rejected due to mismatch
</label><br><br>
<button type="submit" name="action" value="approve" class="btn approve">Approve</button>
<button type="submit" name="action" value="reject" class="btn reject">Reject</button>
<button type="submit" name="action" value="pending" class="btn view">Set Pending</button>

</form>

</div>

</div>
</div>

</body>
</html>