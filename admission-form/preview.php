<?php
session_start();
require_once "db.php";

// Generate CSRF token if it does not exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF verification failed.");
    }

    // Preserve previously uploaded files if any
    $existing_files = [];
    $docFields = ['sslc','hsc','ug','tc','migration','undertaking','signature'];
    if (isset($_SESSION['step2_data'])) {
        foreach ($docFields as $f) {
            if (isset($_SESSION['step2_data'][$f.'_file'])) {
                $existing_files[$f.'_file'] = $_SESSION['step2_data'][$f.'_file'];
            }
        }
    }

    // Process POST Data
    $post_data = $_POST;
    
    if (isset($post_data['enclosures']) && is_array($post_data['enclosures'])) {
        $post_data['enclosures'] = implode(",", $post_data['enclosures']);
    }

    if (isset($post_data['abc_id'])) {
        $post_data['abc_id_clean'] = preg_replace('/\s+/', '', $post_data['abc_id']);
    }

    if (isset($post_data['deb_id'])) {
        $post_data['deb_id_clean'] = preg_replace('/\s+/', '', $post_data['deb_id']);
    }

    if (isset($post_data['defence_status'])) {
        $post_data['defence_personnel'] = ($post_data['defence_status'] === 'defence') ? 1 : 0;
        $post_data['ex_servicemen'] = ($post_data['defence_status'] === 'ex') ? 1 : 0;
    } else {
        $post_data['defence_personnel'] = 0;
        $post_data['ex_servicemen'] = 0;
    }

    // Store Step 2 Data
    $_SESSION['step2_data'] = $post_data;
    
    // Restore existing files
    foreach ($existing_files as $key => $val) {
        $_SESSION['step2_data'][$key] = $val;
    }

    // Handle new file uploads
    $temp_session_id = session_id();
    $uploadDir = "uploads/temp_" . $temp_session_id . "/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $allowedExt = ['pdf','jpg','jpeg','png'];

    foreach ($docFields as $field) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] == UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowedExt) && $_FILES[$field]['size'] <= 250 * 1024) {
                $newFile = strtoupper($field) . "_" . time() . "_" . uniqid() . "." . $ext;
                if (move_uploaded_file($_FILES[$field]['tmp_name'], $uploadDir . $newFile)) {
                    $_SESSION['step2_data'][$field.'_file'] = $newFile;
                }
            }
        }
    }

    // Redirect to self to clear POST data and show preview safely
    header("Location: preview.php");
    exit;
}

if (
    !isset($_SESSION['step1_data']) ||
    !isset($_SESSION['step2_data'])
) {
    header("Location: ap1.php");
    exit;
}

$s1 = $_SESSION['step1_data'];
$s2 = $_SESSION['step2_data'];

// Fetch state name if state ID exists
$state_name = '';
if (!empty($s1['state'])) {
    try {
        $stmt = $pdo->prepare("SELECT state_name FROM states WHERE id = :id");
        $stmt->execute([':id' => $s1['state']]);
        $state_row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($state_row) {
            $state_name = $state_row['state_name'];
        }
    } catch (Exception $e) {
        $state_name = $s1['state'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Application Preview</title>

<style>

body{
    font-family: "Times New Roman", Times, serif;

    background:#f4f6f9;
    margin:0;
    padding:20px;
}

.container{
    font-family: "Times New Roman", Times, serif;
    max-width:1000px;
    margin:auto;
    background:#fff;
    padding:30px;
    border-radius:10px;
    box-shadow:0 0 10px rgba(0,0,0,0.1);
}

h1{
    font-family: "Times New Roman", Times, serif;
    text-align:center;
    margin-bottom:30px;
    color:#333;
}

.section{
    font-family: "Times New Roman", Times, serif;
    border:1px solid #ddd;
    border-radius:8px;
    margin-bottom:25px;
    overflow:hidden;
}

.section h2{
    font-family: "Times New Roman", Times, serif;
    background:#007bff;
    color:white;
    margin:0;
    padding:12px 15px;
    font-size:20px;
}

.table{
    font-family: "Times New Roman", Times, serif;
    width:100%;
    border-collapse:collapse;
}

.table tr:nth-child(even){
    background:#f9f9f9;
    font-family: "Times New Roman", Times, serif;
}

.table th, .table td{
    font-family: "Times New Roman", Times, serif;
    padding:12px 15px;
    border-bottom:1px solid #ddd;
    text-align: left;
}

.table th{
    font-family: "Times New Roman", Times, serif;
    background: #eaeaea;
    font-weight: bold;
    border-bottom: 2px solid #ccc;
}

.label{
    font-family: "Times New Roman", Times, serif;
    font-weight:bold;
    width:35%;
    color:#333;
}

.value{
    font-family: "Times New Roman", Times, serif;
    color:#555;
}

.button-group{
    font-family: "Times New Roman", Times, serif;
    text-align:center;
    margin-top:30px;
    display:flex;
    justify-content:center;
    gap: 15px;
}

.btn{
    padding:12px 25px;
    font-family: "Times New Roman", Times, serif;
    border:none;
    border-radius:5px;
    font-size:16px;
    cursor:pointer;
    text-decoration:none;
    display:inline-block;
}

.edit-btn{
    background:#ffc107;
    font-family: "Times New Roman", Times, serif;
    color:#000;
}

.submit-btn{
    font-family: "Times New Roman", Times, serif;
    background:#28a745;
    color:white;
}

.edit-btn:hover{
    font-family: "Times New Roman", Times, serif;
    background:#e0a800;
}

.submit-btn:hover{
    background:#218838;
    font-family: "Times New Roman", Times, serif;
}

.two-col-table {
    width: 100%;
    display: flex;
}

.two-col-table .col {
    width: 50%;
}

.two-col-table .col table {
    width: 100%;
    border-collapse: collapse;
}

.two-col-table .col table td {
    padding: 8px 15px;
    border-bottom: 1px solid #ddd;
}

.two-col-table .col:first-child table {
    border-right: 1px solid #ddd;
}

@media (max-width: 768px) {
    .two-col-table {
        flex-direction: column;
    }
    .two-col-table .col {
        width: 100%;
    }
    .two-col-table .col:first-child table {
        border-right: none;
    }
}

.enclosures-list {
    padding: 15px;
}

.enclosures-list div {
    margin-bottom: 8px;
}

</style>
</head>

<body>

<div class="container">

<h1>Application Preview</h1>

<!-- COURSE DETAILS -->
<div class="section">
    <h2>Course Details</h2>
    <div class="two-col-table">
        <div class="col">
            <table>
                <tr><td class="label">Course Type</td><td class="value"><?php echo htmlspecialchars($s1['course_type'] ?? ''); ?></td></tr>
                <tr><td class="label">Programme</td><td class="value"><?php echo htmlspecialchars($s1['programme_name'] ?? ''); ?></td></tr>
                <tr><td class="label">Foundation Language</td><td class="value"><?php echo htmlspecialchars($s1['foundation_lang'] ?? ''); ?></td></tr>
            </table>
        </div>
        <div class="col">
            <table>
                <tr><td class="label">Main Subject</td><td class="value"><?php echo htmlspecialchars($s1['main_subject'] ?? ''); ?></td></tr>
                <tr><td class="label">Medium</td><td class="value"><?php echo htmlspecialchars($s1['medium'] ?? ''); ?></td></tr>
                <tr><td class="label">Specially Challenged</td><td class="value">
                    <?php 
                    $status = $s1['special_status'] ?? '';
                    echo ($status && $status != 'None') ? htmlspecialchars($status) : 'Not Applicable';
                    ?>
                </td></tr>
            </table>
        </div>
    </div>
</div>

<!-- PERSONAL DETAILS -->
<div class="section">
    <h2>Personal Details</h2>
    <div class="two-col-table">
        <div class="col">
            <table>
                <tr><td class="label">Name (English)</td><td class="value"><?php echo htmlspecialchars($s1['name_english'] ?? ''); ?></td></tr>
                <tr><td class="label">Email</td><td class="value"><?php echo htmlspecialchars($s1['email'] ?? ''); ?></td></tr>
                <tr><td class="label">Age</td><td class="value"><?php echo htmlspecialchars($s1['age'] ?? ''); ?></td></tr>
                <tr><td class="label">Father Name</td><td class="value"><?php echo htmlspecialchars($s1['guardian_name'] ?? ''); ?></td></tr>
                <tr><td class="label">Religion</td><td class="value"><?php echo htmlspecialchars($s1['religion'] ?? ''); ?></td></tr>
                <tr><td class="label">Gender</td><td class="value"><?php echo htmlspecialchars($s1['gender'] ?? ''); ?></td></tr>
            </table>
        </div>
        <div class="col">
            <table>
                <tr><td class="label">Name (Tamil)</td><td class="value"><?php echo htmlspecialchars($s1['name_tamil'] ?? ''); ?></td></tr>
                <tr><td class="label">DOB</td><td class="value"><?php echo !empty($s1['dob']) ? date("d/m/Y", strtotime($s1['dob'])) : ''; ?></td></tr>
                <tr><td class="label">Aadhaar</td><td class="value"><?php echo htmlspecialchars($s1['aadhaar'] ?? ''); ?></td></tr>
                <tr><td class="label">Mother Name</td><td class="value"><?php echo htmlspecialchars($s1['mother_name'] ?? ''); ?></td></tr>
                <tr><td class="label">Community</td><td class="value"><?php echo htmlspecialchars($s1['community'] ?? ''); ?></td></tr>
                <tr><td class="label">Caste</td><td class="value"><?php echo htmlspecialchars($s1['caste'] ?? ''); ?></td></tr>
            </table>
        </div>
    </div>
</div>

<!-- ADDRESS FOR COMMUNICATION -->
<div class="section">
    <h2>Address For Communication</h2>
    <div class="two-col-table">
        <div class="col">
            <table>
                <tr><td class="label">Name</td><td class="value"><?php echo htmlspecialchars($s1['name'] ?? ''); ?></td></tr>
                <tr><td class="label">Street</td><td class="value"><?php echo htmlspecialchars($s1['street'] ?? ''); ?></td></tr>
                <tr><td class="label">Town</td><td class="value"><?php echo htmlspecialchars($s1['town'] ?? ''); ?></td></tr>
                <tr><td class="label">District</td><td class="value"><?php echo htmlspecialchars($s1['district'] ?? ''); ?></td></tr>
                <tr><td class="label">Pincode</td><td class="value"><?php echo htmlspecialchars($s1['pincode'] ?? ''); ?></td></tr>
                <tr><td class="label">Urban / Rural</td><td class="value"><?php echo htmlspecialchars($s1['urban_rural'] ?? ''); ?></td></tr>
            </table>
        </div>
        <div class="col">
            <table>
                <tr><td class="label">State</td><td class="value"><?php echo htmlspecialchars($state_name); ?></td></tr>
                <tr><td class="label">Mobile</td><td class="value"><?php echo htmlspecialchars($s1['mobile'] ?? ''); ?></td></tr>
                <tr><td class="label">Phone</td><td class="value"><?php echo htmlspecialchars($s1['phone'] ?? ''); ?></td></tr>
                <tr><td class="label">Blood Group</td><td class="value"><?php echo htmlspecialchars($s1['blood_group'] ?? ''); ?></td></tr>
                <tr><td class="label">Employment Status</td><td class="value"><?php echo htmlspecialchars($s1['employment_status'] ?? ''); ?></td></tr>
                <tr><td class="label">Employment Type</td><td class="value"><?php echo htmlspecialchars($s1['employment_type'] ?? ''); ?></td></tr>
            </table>
        </div>
    </div>
</div>

<!-- ADDITIONAL INFORMATION -->
<div class="section">
    <h2>Additional Information</h2>
    <table class="table">
        <tr>
            <td class="label">Academic Bank of Credit (ABC)</td>
            <td class="value">
                <?php echo htmlspecialchars($s2['abc'] ?? 'Not Provided'); ?>
                <?php if(($s2['abc'] ?? '') == "Yes" && !empty($s2['abc_id'])): ?>
                    <br><strong>ABC ID:</strong> <?php echo htmlspecialchars(trim(chunk_split($s2['abc_id'], 4, ' '))); ?>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td class="label">Distance Education Bureau (DEB)</td>
            <td class="value">
                <?php echo htmlspecialchars($s2['deb'] ?? 'Not Provided'); ?>
                <?php if(($s2['deb'] ?? '') == "Yes" && !empty($s2['deb_id'])): ?>
                    <br><strong>DEB ID:</strong> <?php echo htmlspecialchars(trim(chunk_split($s2['deb_id'], 4, ' '))); ?>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td class="label">Other Course in College/University?</td>
            <td class="value">
                <?php echo htmlspecialchars($s2['other_course'] ?? 'No'); ?>
                <?php if(($s2['other_course'] ?? '') == "Yes" && !empty($s2['other_course_details'])): ?>
                    <br><strong>Details:</strong> <?php echo htmlspecialchars($s2['other_course_details']); ?>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td class="label">Ward of Defence / Ex-Servicemen</td>
            <td class="value">
                <?php
                if(($s2['defence_status'] ?? '') == 'defence'){
                    echo "Defence Personnel";
                }elseif(($s2['defence_status'] ?? '') == 'ex'){
                    echo "Ex-Servicemen";
                }else{
                    echo "None";
                }
                ?>
            </td>
        </tr>
    </table>
</div>

<!-- EXAMINATION DETAILS -->
<div class="section">
    <h2>Examination Details</h2>
    <div style="overflow-x:auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>Exam</th>
                    <th>Institution</th>
                    <th>Board</th>
                    <th>Month & Year</th>
                    <th>Reg No</th>
                    <th>Grade</th>
                    <th>Max Marks</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>SSLC</td>
                    <td><?php echo htmlspecialchars($s2['sslc_school'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($s2['sslc_board'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($s2['sslc_pass_year'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($s2['sslc_reg_no'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($s2['sslc_grade'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($s2['sslc_max_marks'] ?? ''); ?></td>
                </tr>
                <tr>
                    <td>HSC</td>
                    <td><?php echo htmlspecialchars($s2['hsc_school'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($s2['hsc_board'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($s2['hsc_pass_year'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($s2['hsc_reg_no'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($s2['hsc_grade'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($s2['hsc_max_marks'] ?? ''); ?></td>
                </tr>
                <tr>
                    <td>Diploma</td>
                    <td><?php echo htmlspecialchars($s2['dip_school'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($s2['dip_board'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($s2['dip_pass_year'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($s2['dip_reg_no'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($s2['dip_grade'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($s2['dip_max_marks'] ?? ''); ?></td>
                </tr>
                <tr>
                    <td>UG</td>
                    <td><?php echo htmlspecialchars($s2['ug_school'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($s2['ug_board'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($s2['ug_pass_year'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($s2['ug_reg_no'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($s2['ug_grade'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($s2['ug_max_marks'] ?? ''); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ENCLOSURES SUBMITTED -->
<div class="section">
    <h2>Enclosures Submitted</h2>
    <div class="enclosures-list">
        <?php
        $enclosures_map = [
            'SSLC' => 'S.S.L.C Statement of Marks',
            'HSC' => 'HSC / Diploma Statement of Marks',
            'UG' => 'UG Marks / Provisional / Degree',
            'Transfer Certificate' => 'Transfer Certificate / Course Completion Certificate',
            'Migration' => 'Migration Certificate',
            'Undertaking' => 'Undertaking'
        ];
        
        $enclosures = $s2['enclosures'] ?? [];
        if (is_string($enclosures)) {
            $enclosures = explode(',', $enclosures);
        }
        
        foreach ($enclosures_map as $val => $label) {
            $checked = in_array($val, (array)$enclosures) ? "☑" : "☐";
            echo "<div>$checked $label</div>";
        }
        ?>
    </div>
</div>

<!-- UPLOADED DOCUMENTS -->
<div class="section">
    <h2>Uploaded Documents</h2>

    <table class="table">
        <tr>
            <td class="label">SSLC Certificate</td>
            <td class="value"><?php echo htmlspecialchars($s2['sslc_file'] ?? 'Not Uploaded'); ?></td>
        </tr>
        <tr>
            <td class="label">HSC Certificate</td>
            <td class="value"><?php echo htmlspecialchars($s2['hsc_file'] ?? 'Not Uploaded'); ?></td>
        </tr>
        <tr>
            <td class="label">Transfer Certificate</td>
            <td class="value"><?php echo htmlspecialchars($s2['tc_file'] ?? 'Not Uploaded'); ?></td>
        </tr>
        <tr>
            <td class="label">Migration Certificate</td>
            <td class="value"><?php echo htmlspecialchars($s2['migration_file'] ?? 'Not Uploaded'); ?></td>
        </tr>
        <tr>
            <td class="label">UG Certificate</td>
            <td class="value"><?php echo htmlspecialchars($s2['ug_file'] ?? 'Not Uploaded'); ?></td>
        </tr>
        <tr>
            <td class="label">Undertaking</td>
            <td class="value"><?php echo htmlspecialchars($s2['undertaking_file'] ?? 'Not Uploaded'); ?></td>
        </tr>
        <tr>
            <td class="label">Signature</td>
            <td class="value"><?php echo htmlspecialchars($s2['signature_file'] ?? 'Not Uploaded'); ?></td>
        </tr>
    </table>
</div>

<!-- Buttons -->
<div class="button-group">

    <!-- Edit Button -->
    <a href="ap1.php" class="btn edit-btn">
        Edit Application
    </a>

    <!-- Final Submit -->
    <form action="final_submit.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <button type="submit" class="btn submit-btn">
            Final Submit
        </button>
    </form>

</div>

</div>

</body>
</html>