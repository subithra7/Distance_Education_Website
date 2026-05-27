<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['application_no'])) {
    header("Location: new_application1.php");
    exit;
}

$appNo = $_SESSION['application_no'];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['final_submit'])) {
    $stmt = $pdo->prepare("UPDATE records SET status = 'submitted' WHERE application_no = :app");
    $stmt->execute([':app' => $appNo]);
    header("Location: print.php");
    exit;
}

try {
    $stmt = $pdo->prepare("
SELECT r.*, s.state_name
FROM records r
LEFT JOIN states s ON r.state = s.id
WHERE r.application_no = :appNo
");
    $stmt->execute([':appNo' => $appNo]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        die("Application not found.");
    }

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

function show($key, $data) {
    return htmlspecialchars($data[$key] ?? '');
}

if (!empty($data['dob'])) {
    $dobObj = DateTime::createFromFormat('Y-m-d', $data['dob']);
    if ($dobObj) {
        $data['dob'] = $dobObj->format('d/m/Y');
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Application Preview</title>
<style>
body{ font-family:"Times New Roman", serif; font-family: "Times New Roman", Times, serif;background:#f4f4f4; padding:20px; }
.container{ width:900px; margin:auto; background:#fff; padding:30px; border:2px solid #000; box-shadow:0 0 10px rgba(0,0,0,0.1); }
.header{ display:flex; align-items:center; font-family: "Times New Roman", Times, serif;justify-content:space-between; margin-bottom:10px; }
.header-left, .header-right{ width:150px;font-family: "Times New Roman", Times, serif; display:flex; justify-content:center; align-items:center; }
.header-center{ flex:1; text-align:center; padding:0 10px;font-family: "Times New Roman", Times, serif; }
.logo{ width:110px; }
.photo{ width:120px; height:140px; border:1px solid #000; object-fit:cover; }
h1{ margin:0; font-size:22px; font-family: "Times New Roman", Times, serif;}
h2{ margin:3px 0; font-size:18px; font-family: "Times New Roman", Times, serif;}
.sub-title{ font-weight:bold; margin-top:5px; font-family: "Times New Roman", Times, serif;}
hr{ border:1px solid #000; margin:15px 0; font-family: "Times New Roman", Times, serif;}
.section-title{ font-weight:bold; font-size:16px; margin-bottom:8px; border-bottom:1px solid #000;font-family: "Times New Roman", Times, serif; padding-bottom:4px; }
.two-column{ display:flex;font-family: "Times New Roman", Times, serif; justify-content:space-between; }
.col{ width:48%; font-family: "Times New Roman", Times, serif;}
.row{ margin-bottom:6px; font-family: "Times New Roman", Times, serif;}
.label{ font-weight:bold; }
table{ width:100%; border-collapse:collapse; margin-top:10px; font-size:13px; }
table th{ background:#eaeaea;font-family: "Times New Roman", Times, serif; font-weight:bold; }
table th, table td{ border:1px solid #000; padding:6px; text-align:center;font-family: "Times New Roman", Times, serif; }
.enclosure div{ margin-bottom:5px; font-family: "Times New Roman", Times, serif;}

.action-buttons {
    margin-top: 30px;
    display: flex;
    justify-content: center;
    gap: 20px;
    background: #eef2f5;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #c5d0e6;
}
.btn-edit {
    padding: 12px 25px;
    background: #6c757d;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    font-weight: bold;font-family: "Times New Roman", Times, serif;
}
.btn-submit {
    padding: 12px 25px;
    font-family: "Times New Roman", Times, serif;
    background: #28a745;
    color: white;
    border: none;
    border-radius: 4px;
    font-weight: bold;
    cursor: pointer;
    font-size: 16px;
}
.preview-banner {
    background: #ffecb5;
    color: #856404;
    padding: 15px;
    text-align: center;
    font-family: Arial;
    font-weight: bold;
    margin-bottom: 20px;
    border: 1px solid #ffeeba;
    font-family: "Times New Roman", Times, serif;
    border-radius: 4px;
}
</style>
</head>

<body>

<div class="container">
<div class="preview-banner">
    ⚠️ PREVIEW MODE: Review your application details carefully before final submission.
</div>

<!-- HEADER -->
<div class="header">
    <div class="header-left"><img src="image/Univ.png" class="logo"></div>
    <div class="header-center">
        <strong>சென்னை பல்கலைக்கழகம் – தொலைதூரக் கல்வி நிறுவனம்</strong><br>
        <h1>UNIVERSITY OF MADRAS</h1>
        <h2>Institute of Distance Education</h2>
        <div class="sub-title">Application Preview</div>
    </div>
 <?php
$photoFile = trim($data['photo'] ?? '');
$photoURL  = "uploads/" . $appNo . "/" . $photoFile;
?>
<div class="header-right">
<?php if(!empty($photoFile)): ?>
    <img src="<?php echo $photoURL; ?>" class="photo">
<?php else: ?>
    <div class="photo"></div>
<?php endif; ?>
</div>
</div>

<div><strong>APPLICATION NO:</strong> <?php echo $appNo; ?></div>
<hr>

<!-- COURSE DETAILS -->
<div class="section-title">COURSE DETAILS</div>
<div class="two-column">
    <div class="col">
        <div class="row"><span class="label">Course Type:</span> <?php echo show('course_type',$data); ?></div>
        <div class="row"><span class="label">Programme:</span> <?php echo show('programme_name',$data); ?></div>
        <div class="row"><span class="label">Foundation Language:</span> <?php echo show('foundation_lang',$data); ?></div>
    </div>
    <div class="col">
        <div class="row"><span class="label">Main Subject:</span> <?php echo show('main_subject',$data); ?></div>
        <div class="row"><span class="label">Medium:</span> <?php echo show('medium',$data); ?></div>
        <div class="row"><span class="label">Specially Challenged:</span> <?php echo show('differently_abled',$data); ?></div>    
    </div>
</div>
<hr>

<!-- PERSONAL DETAILS -->
<div class="section-title">PERSONAL DETAILS</div>
<div class="two-column">
    <div class="col">
        <div class="row"><span class="label">Name (English):</span> <?php echo show('name_english',$data); ?></div>
        <div class="row"><span class="label">ABC ID:</span> <?php echo show('abc_id',$data); ?></div>
        <div class="row"><span class="label">DEB ID:</span> <?php echo show('deb_id',$data); ?></div>
        <div class="row"><span class="label">Email:</span> <?php echo show('email',$data); ?></div>
        <div class="row"><span class="label">Father Name:</span> <?php echo show('guardian_name',$data); ?></div>
        <div class="row"><span class="label">Religion:</span> <?php echo show('religion',$data); ?></div>
        <div class="row"><span class="label">Gender:</span> <?php echo show('gender',$data); ?></div>
    </div>
    <div class="col">
        <div class="row"><span class="label">Name (Tamil):</span> <?php echo show('name_tamil',$data); ?></div>
        <div class="row"><span class="label">DOB:</span> <?php echo show('dob',$data); ?></div>
        <div class="row"><span class="label">Age:</span> <?php echo show('age',$data); ?></div>
        <div class="row"><span class="label">Aadhaar:</span> <?php echo show('aadhaar',$data); ?></div>
        <div class="row"><span class="label">Mother Name:</span> <?php echo show('mother_name',$data); ?></div>
        <div class="row"><span class="label">Community:</span> <?php echo show('community',$data); ?></div>
        <div class="row"><span class="label">Caste:</span> <?php echo show('caste',$data); ?></div>
        <div class="row"><span class="label">Urban/Rural:</span> <?php echo show('urban_rural',$data); ?></div>
    </div>
</div>
<hr>

<!-- ADDRESS -->
<div class="section-title">ADDRESS FOR COMMUNICATION </div>
<div class="two-column">
    <div class="col">
        <div class="row"><span class="label">Name:</span> <?php echo show('name',$data); ?></div>
        <div class="row"><span class="label">Street:</span> <?php echo show('street',$data); ?></div>
        <div class="row"><span class="label">Town:</span> <?php echo show('town',$data); ?></div>
        <div class="row"><span class="label">District:</span> <?php echo show('district',$data); ?></div>
        <div class="row"><span class="label">Pincode:</span> <?php echo show('pincode',$data); ?></div>
    </div>
    <div class="col">
        <div class="row"><span class="label">State:</span> <?php echo htmlspecialchars($data['state_name']); ?></div>
        <div class="row"><span class="label">Mobile:</span> <?php echo show('mobile',$data); ?></div>
        <div class="row"><span class="label">Phone:</span> <?php echo show('phone',$data); ?></div>
        <div class="row"><span class="label">Blood Group:</span> <?php echo show('blood_group',$data); ?></div>
        <div class="row"><span class="label">Employed?</span> <?php echo show('employment_status',$data); ?> (<?php echo htmlspecialchars($data['employment_type']); ?>)</div>
    </div>
</div>
<hr>

<!-- ADDITIONAL INFORMATION -->
<div class="section-title">ADDITIONAL INFORMATION</div>
<div class="two-column">
  <div class="col">
    <div class="row"><strong>Other Course:</strong> <?php echo show('other_course',$data); ?> <?php echo $data['other_course'] == 'Yes' ? '('.show('other_course_details',$data).')' : ''; ?></div>
    <div class="row"><strong>Defence Personnel:</strong> <?php echo $data['defence_personnel'] ? 'Yes' : ($data['ex_servicemen'] ? 'Ex-Servicemen' : 'No'); ?></div>
  </div>
  <div class="col">
    <div class="row"><strong>Certificate Return Mode:</strong> <?php echo show('cert_return_mode',$data); ?></div>
  </div>
</div>
<hr>

<!-- EXAMINATION DETAILS -->
<div class="section-title">EXAMINATION DETAILS</div>
<table>
<tr><th>Exam</th><th>Institution</th><th>Board</th><th>Year</th><th>Reg No</th><th>Grade</th><th>Max Marks</th></tr>
<tr>
<td>SSLC</td><td><?php echo show('sslc_school',$data); ?></td><td><?php echo show('sslc_board',$data); ?></td><td><?php echo show('sslc_pass_year',$data); ?></td><td><?php echo show('sslc_reg_no',$data); ?></td><td><?php echo show('sslc_grade',$data); ?></td><td><?php echo show('sslc_max_marks',$data); ?></td>
</tr>
<tr>
<td>HSC</td><td><?php echo show('hsc_school',$data); ?></td><td><?php echo show('hsc_board',$data); ?></td><td><?php echo show('hsc_pass_year',$data); ?></td><td><?php echo show('hsc_reg_no',$data); ?></td><td><?php echo show('hsc_grade',$data); ?></td><td><?php echo show('hsc_max_marks',$data); ?></td>
</tr>
<tr>
<td>DIP</td><td><?php echo show('dip_school',$data); ?></td><td><?php echo show('dip_board',$data); ?></td><td><?php echo show('dip_pass_year',$data); ?></td><td><?php echo show('dip_reg_no',$data); ?></td><td><?php echo show('dip_grade',$data); ?></td><td><?php echo show('dip_max_marks',$data); ?></td>
</tr>
<tr>
<td>UG</td><td><?php echo show('ug_school',$data); ?></td><td><?php echo show('ug_board',$data); ?></td><td><?php echo show('ug_pass_year',$data); ?></td><td><?php echo show('ug_reg_no',$data); ?></td><td><?php echo show('ug_grade',$data); ?></td><td><?php echo show('ug_max_marks',$data); ?></td>
</tr>
</table>
<hr>

<form method="POST">
    <div class="action-buttons">
        <a href="new_application1.php" class="btn-edit">✎ Modify Application</a>
        <button type="submit" name="final_submit" class="btn-submit">✅ Confirm & Final Submit</button>
    </div>
</form>

</div>
</body>
</html>
