<?php
session_start();
require "db.php";

/* ===== FETCH STATES ===== */
$states = $pdo->query("SELECT * FROM states ORDER BY state_name ASC")->fetchAll();

/* ===== FETCH DISTRICTS ===== */
$districts = $pdo->query("SELECT * FROM districts ORDER BY district_name ASC")->fetchAll();

$student_email = $_SESSION['student_email'] ?? '';
$auto_name = '';
$auto_mobile = '';
$auto_dob = '';

if ($student_email) {
    $stmt = $pdo->prepare("SELECT name, mobile, dob FROM students WHERE email = ?");
    $stmt->execute([$student_email]);
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $auto_name = $row['name'];
        $auto_mobile = $row['mobile'];
        $auto_dob = $row['dob'];
    }
}

$is_edit = false;
$edit_data = [];
if (isset($_SESSION['application_no'])) {
    $is_edit = true;
    $stmt = $pdo->prepare("SELECT * FROM records WHERE application_no = ?");
    $stmt->execute([$_SESSION['application_no']]);
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $edit_data = $row;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $gender = $_POST['gender'] ?? '';

if(empty($gender)){
    die("Gender is required.");
}
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
    
    /* ===== GENERATE APPLICATION NO ======================= */
    if (isset($_SESSION['application_no'])) {
        $application_no = $_SESSION['application_no'];
    } else {
        $course = $_POST['course_type'];   // UG / PG / DIP / CERT
        $year = date("Y");
        $month = date("n"); // 1 to 12
        /* Safety check */
        if (!in_array($course, ['UG','PG','DIP','CERT'])) {
            die("Invalid Course Type");
        }
        /* Decide Academic (A) or Calendar (C) */
        if ($month >= 1 && $month <= 6) {
            $period = "A";   // Academic Year (Jan–June)
        } else {
            $period = "C";   // Calendar Year (July–Dec)
        }
        /* Prefix like UGA / UGC / PGA / PGC */
        $prefix = $course . $period;
        /* Fetch last number for same prefix + year */
        $stmt = $pdo->prepare("
            SELECT application_no 
            FROM records 
            WHERE application_no LIKE :pattern
            ORDER BY id DESC
            LIMIT 1
        ");
        $pattern = $prefix . "-" . $year . "-%";
        $stmt->execute([':pattern' => $pattern]);
        $lastRecord = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($lastRecord) {
            $lastNumber = (int) substr($lastRecord['application_no'], -5);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        /* 5 digit formatting */
        $formattedNumber = str_pad($newNumber, 5, "0", STR_PAD_LEFT);
        $application_no = $prefix . "-" . $year . "-" . $formattedNumber;
    }
    
    /* ===== NEW DEB/ABC ID VALIDATION ===== */
    $abc_id = preg_replace('/\s+/', '', $_POST['abc_id'] ?? '');
    $deb_id = preg_replace('/\s+/', '', $_POST['deb_id'] ?? '');
    if (!preg_match('/^[0-9]{12}$/', $abc_id)) {
        die("ABC ID must be exactly 12 digits.");
    }
    if (empty($deb_id)) {
        die("DEB ID is mandatory.");
    }

    $aadhaar = preg_replace(
    '/\D/',
    '',
    $_POST['aadhaar'] ?? ''
);

if (strlen($aadhaar) !== 12) {
    die("Aadhaar must contain exactly 12 digits.");
}



    
    // Urban/Rural
    $urban_rural = $_POST['urban_rural'] ?? '';
/* ===== PHOTO UPLOAD ===== */

$photoName = null;
if (
    !isset($_FILES['photo']) ||
    $_FILES['photo']['error'] == UPLOAD_ERR_NO_FILE
) {
    die("Passport Photo is required.");
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $_FILES['photo']['tmp_name']);
finfo_close($finfo);

$allowedMime = [
    'image/jpeg',
    'image/png'
];

if (!in_array($mime, $allowedMime)) {
    die("Invalid image file.");
}

    if ($_FILES['photo']['size'] > 250 * 1024) {
        die("Photo size must be below 250KB.");
    }

    $uploadDir = "uploads/" . $application_no . "/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
$photoExt = strtolower(
    pathinfo(
        $_FILES['photo']['name'],
        PATHINFO_EXTENSION
    )
);
    $photoName = $application_no . "." . $photoExt;

    move_uploaded_file(
        $_FILES['photo']['tmp_name'],
        $uploadDir . $photoName
    );



/* ===== DIFFERENTLY ABLED CERTIFICATE ===== */

$disability_certificate = null;
$disability_certificate = null;

if (
    ($_POST['special_status'] ?? 'None') !== 'None'
) {

    if (empty($_FILES['special_file']['name'])) {
        die("Supporting Certificate is required.");
    }

    $uploadDir = "uploads/" . $application_no . "/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $certExt = strtolower(
        pathinfo(
            $_FILES['special_file']['name'],
            PATHINFO_EXTENSION
        )
    );

    $specialFileName =
        "DIFFERENTLYABLED-" .
        $application_no .
        "." .
        $certExt;

    move_uploaded_file(
        $_FILES['special_file']['tmp_name'],
        $uploadDir . $specialFileName
    );

    $disability_certificate = $specialFileName;
}

/* ===== FOUNDATION LANGUAGE SAFETY ===== */
if (($_POST['course_type'] ?? '') !== "UG") {
    $_POST['foundation_lang'] = null;
}

/* ===== SPECIAL STATUS VALUE ===== */

$differently_abled = $_POST['special_status'] ?? "None";

    if (isset($_SESSION['application_no'])) {
        // Build update
        $sql = "UPDATE records SET 
            course_type=:course_type, foundation_lang=:foundation_lang, programme_name=:programme_name,
            main_subject=:main_subject, medium=:medium, differently_abled=:differently_abled,
            name=:name, street=:street, town=:town, state=:state, district=:district, pincode=:pincode,
            phone=:phone, mobile=:mobile, name_english=:name_english, name_tamil=:name_tamil, email=:email, 
            dob=:dob, age=:age, gender=:gender, guardian_name=:guardian_name, mother_name=:mother_name, 
            aadhaar=:aadhaar, nationality=:nationality, religion=:religion, mother_tongue=:mother_tongue, 
            blood_group=:blood_group, community=:community, caste=:caste, employment_status=:employment_status, 
            employment_type=:employment_type, abc_id=:abc_id, deb_id=:deb_id, urban_rural=:urban_rural";

        if ($photoName) {
            $sql .= ", photo=:photo";
        }
        if ($disability_certificate) {
            $sql .= ", disability_certificate=:disability_certificate";
        }
        $sql .= " WHERE application_no = :application_no";

        $params = [
            ':application_no' => $application_no,
            ':course_type' => $_POST['course_type'],
            ':foundation_lang' => ($_POST['course_type'] === "UG") ? $_POST['foundation_lang'] : null,
            ':programme_name' => $_POST['programme_name'],
            ':main_subject' => $_POST['main_subject'],
            ':medium' => $_POST['medium'],
            ':differently_abled' => $differently_abled,
            ':name' => $_POST['name'],
            ':street' => $_POST['street'],
            ':town' => $_POST['town'],
            ':state' => $_POST['state'],
            ':district' => $_POST['district'],
            ':pincode' => $_POST['pincode'],
            ':phone' => $_POST['phone'],
            ':mobile' => $_POST['mobile'],
            ':name_english' => $_POST['name_english'],
            ':name_tamil' => $_POST['name_tamil'],
            ':email' => $_POST['email'],
            ':dob' => $_POST['dob'],
            ':age' => $age,
            ':gender' => $gender,
            ':guardian_name' => $_POST['guardian_name'],
            ':mother_name' => $_POST['mother_name'],
            ':aadhaar' => $_POST['aadhaar'],
            ':nationality' => $_POST['nationality'],
            ':religion' => $_POST['religion'],
            ':mother_tongue' => $_POST['mother_tongue'],
            ':blood_group' => $_POST['blood_group'],
            ':community' => $_POST['community'],
            ':caste' => $_POST['caste'],
            ':employment_status' => $_POST['employment_status'],
            ':employment_type' => $_POST['employment_type'],
            ':abc_id' => $abc_id,
            ':deb_id' => $deb_id,
            ':urban_rural' => $urban_rural
        ];

        if ($photoName) $params[':photo'] = $photoName;
        if ($disability_certificate) $params[':disability_certificate'] = $disability_certificate;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

    } else {
        // Insert
        // IMPORTANT: We fallback to photo defaults here
        // If not provided, it's null
        $lsc_code = $_SESSION['lsc_code'] ?? null;
        $sql = "INSERT INTO records (
            application_no, lsc_code, course_type, foundation_lang, programme_name,
            main_subject, medium, differently_abled, photo, disability_certificate,
            name, street, town, state, district, pincode, phone, mobile,
            name_english, name_tamil, email, dob, age, gender,
            guardian_name, mother_name, aadhaar, nationality, religion, mother_tongue, 
            blood_group, community, caste, employment_status, employment_type,
            abc_id, deb_id, urban_rural
        ) VALUES (
            :application_no, :lsc_code, :course_type, :foundation_lang, :programme_name,
            :main_subject, :medium, :differently_abled, :photo, :disability_certificate,
            :name, :street, :town, :state, :district, :pincode, :phone, :mobile,
            :name_english, :name_tamil, :email, :dob, :age, :gender,
            :guardian_name, :mother_name, :aadhaar, :nationality, :religion, :mother_tongue, 
            :blood_group, :community, :caste, :employment_status, :employment_type,
            :abc_id, :deb_id, :urban_rural
        )";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':application_no' => $application_no,
            ':lsc_code' => $lsc_code,
            ':course_type' => $_POST['course_type'],
            ':foundation_lang' => ($_POST['course_type'] === "UG") ? $_POST['foundation_lang'] : null,
            ':programme_name' => $_POST['programme_name'],
            ':main_subject' => $_POST['main_subject'],
            ':medium' => $_POST['medium'],
            ':differently_abled' => $differently_abled,
            ':photo' => $photoName,
            ':disability_certificate' => $disability_certificate,
            ':name' => $_POST['name'],
            ':street' => $_POST['street'],
            ':town' => $_POST['town'],
            ':state' => $_POST['state'],
            ':district' => $_POST['district'],
            ':pincode' => $_POST['pincode'],
            ':phone' => $_POST['phone'],
            ':mobile' => $_POST['mobile'],
            ':name_english' => $_POST['name_english'],
            ':name_tamil' => $_POST['name_tamil'],
            ':email' => $_POST['email'],
            ':dob' => $_POST['dob'],
            ':age' => $age,
            ':gender' => $gender,
            ':guardian_name' => $_POST['guardian_name'],
            ':mother_name' => $_POST['mother_name'],
            ':aadhaar' => $_POST['aadhaar'],
            ':nationality' => $_POST['nationality'],
            ':religion' => $_POST['religion'],
            ':mother_tongue' => $_POST['mother_tongue'],
            ':blood_group' => $_POST['blood_group'],
            ':community' => $_POST['community'],
            ':caste' => $_POST['caste'],
            ':employment_status' => $_POST['employment_status'],
            ':employment_type' => $_POST['employment_type'],
            ':abc_id' => $abc_id,
            ':deb_id' => $deb_id,
            ':urban_rural' => $urban_rural
        ]);
    }
    $_SESSION['application_no'] = $application_no;
    header("Location: new_application2.php");
    exit;
}

?>
    <!DOCTYPE html>
    <html>
    <head>
    <meta charset="UTF-8">
    <title>Admission Application - Step 1</title>
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


<!-- NAVIGATION -->

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



<div class="main">
    <section class="banner">
    <div class="container">
    <div class="form-header">
    <h1>ADMISSION APPLICATION FORM</h1>
  <p>Step 1 - Personal & Programme Details</p>
  </div>
    <form method="POST" enctype="multipart/form-data">
    <!-- PROGRAMME DETAILS -->
    <fieldset class="programme-fieldset">
    <legend>PROGRAMME DETAILS</legend>
    <div class="programme-content-wrapper">
    <div class="programme-left">
    <div class="form-row">
    <label><strong>Language Chosen for Foundation Course (UG Only)</strong></label>
    <div class="radio-group" id="foundationBox">
    <label class="radio-item"><input type="radio" name="foundation_lang" value="Tamil">Tamil</label>
    <label class="radio-item"><input type="radio" name="foundation_lang" value="Hindi">Hindi</label>
    <label class="radio-item"><input type="radio" name="foundation_lang" value="Telugu">Telugu</label>
    <label class="radio-item"><input type="radio" name="foundation_lang" value="Arabic">Arabic</label>
    <label class="radio-item"><input type="radio" name="foundation_lang" value="French">French</label>
    <label class="radio-item"><input type="radio" name="foundation_lang" value="Malayalam">Malayalam</label>
    <label class="radio-item"><input type="radio" name="foundation_lang" value="Urdu">Urdu</label>
    <label class="radio-item"><input type="radio" name="foundation_lang" value="Kannada">Kannada</label>
    <label class="radio-item"><input type="radio" name="foundation_lang" value="Sanskrit">Sanskrit</label>
    <label class="radio-item"><input type="radio" name="foundation_lang" value="Communicative English">Communicative English</label>
    </div>
    </div>
    <div class="form-row">
    <label>Course Type <span class="required-star">*</span></label>
    <select name="course_type"
        id="course_type"
        onchange="loadCourses(); toggleFoundation();"
        required>
    <option value="">Select Course Type</option>
    <option value="UG">Under Graduate</option>
    <option value="PG">Post Graduate</option>
    <option value="DIP">Diploma</option>
    <option value="CERT">Certificate</option>
    </select>
    </div>
    <div class="form-group">
    <label><b>Medium of Study<span class="required-star">*</span></b></label><br>

    <label>
        <input type="radio" name="medium" value="tamil" required>
        Tamil 
    </label>

    <label style="margin-left:20px;">
        <input type="radio" name="medium" value="english" required>
        English 
    </label>
</div><br>
    <div class="form-grid">
    <div class="form-row">
<label>Name of the Programme<span class="required-star">*</span></label>
<select name="programme_name" id="programme_name" required>
<option value="">Select Programme</option>
</select>
</div>
<div class="form-row">
<label>Main Subject<span class="required-star">*</span></label>
<select name="main_subject" id="main_subject" required>
<option value="">Select Subject</option>
</select>
</div>
</div>
    <div class="form-row">
  <label>Specially Challenged Status <span class="required-star">*</span></label><br>

  <div class="inline">

<label class="radio-item">
<input type="radio" name="special_status" value="Differently Abled" onchange="toggleSpecialFile()">
Differently Abled
</label>

<label class="radio-item">
<input type="radio" name="special_status" value="Visually Challenged" onchange="toggleSpecialFile()">
Visually Challenged
</label>

<label class="radio-item">
<input type="radio" name="special_status" value="Prisoner" onchange="toggleSpecialFile()">
Prisoner
</label>

<label class="radio-item">
<input type="radio" name="special_status" value="None" onchange="toggleSpecialFile()" checked>
None
</label>

</div>

  <!-- File Upload -->
  <div id="specialFileBox" style="margin-top:10px; display:none;">
    <label>Upload Supporting Certificate (PDF/JPG/PNG)</label>
    <input type="file" name="special_file" accept=".pdf,.jpg,.jpeg,.png">
  </div>
</div>
    </div>
    <div class="photo-section">

    <div class="photo-box" >

        <label><strong><b>Recent Passport Photo<span class="required-star">*</span></b></strong></label>
`
        <div class="upload-area"
             onclick="document.getElementById('photoInput').click();" >

            <img id="photoPreview">

            <span id="uploadText">Click to Upload</span>

        </div>

        <input type="file"
               name="photo"
               id="photoInput"
               accept="image/*"
               required
               hidden>

    </div>

    <div class="photo-info">

        Allowed format: JPG / JPEG<br>
        Max file size: 250 KB<br>
        Passport-size photo with<br>
        plain/white background

    </div>

</div>

    </div>
    </fieldset>
    <!-- ADDRESS FOR COMMUNICATION -->
    <fieldset>
<legend>ADDRESS FOR COMMUNICATION</legend>

<div class="form-grid">
    <div class="form-row">
        <label>ABC ID <span class="required-star">*</span></label>
        <input type="text" name="abc_id" required>
    </div>
    <div class="form-row">
        <label>DEB ID <span class="required-star">*</span></label>
        <input type="text" name="deb_id" required>
    </div>
</div>

<div class="form-grid">
    <div class="form-row">
        <label>Name <span class="required-star">*</span></label>
        <input type="text" name="name" required>
    </div>
    <div class="form-row">
        <label>Door No & Street <span class="required-star">*</span></label>
        <input type="text" name="street" required>
    </div>
</div>

<div class="form-grid">
    <div class="form-row">
        <label>Town/Village <span class="required-star">*</span></label>
        <input type="text" name="town" required>
    </div>
    <div class="form-row">
        <label>Pincode <span class="required-star">*</span></label>
        <input type="text" name="pincode" required>
    </div>
</div>

<div class="form-grid">
    <div class="form-row">
        <label>State <span class="required-star">*</span></label>
    <select
        name="state"
        id="state"
        required
    >
    <option value="">Select State</option>
</select>

    </div>
    <div class="form-row">
        <label>District <span class="required-star">*</span></label>
    <select
        name="district"
        id="district"
        required
    >
    <option value="">Select District</option>
    </select>

    </div>
</div>

<!-- ✅ NOW IT WILL 100% SHOW -->
<div class="form-row">
    <label>Urban / Rural <span class="required-star">*</span></label>
    <select name="urban_rural" required>
        <option value="">Select Option</option>
        <option value="Urban">Urban</option>
        <option value="Rural">Rural</option>
    </select>
</div>

<div class="form-grid">
    <div class="form-row">
        <label>Phone <span class="required-star">*</span></label>
        <input type="text" name="phone" required>
    </div>
    <div class="form-row">
        <label>Mobile <span class="required-star">*</span></label>
        <input type="text" name="mobile" required>
    </div>
</div>

<div class="form-row">
    <label>Email <span class="required-star">*</span></label>
    <input type="email" name="email" required>
</div>

</fieldset>
    <!-- APPLICANT DETAILS -->
    <fieldset>
    <legend>APPLICANT DETAILS</legend>
    <div class="form-grid">
    <div class="form-row">
    <label>1.(a)Name (English in CAPITAL LETTERS) <span class="required-star">*</span></label>
    <input type="text" name="name_english" id="name_english" required>
    </div>
    <div class="form-row">
    <label> (b)Name (Tamil) <span class="required-star">*</span></label>
    <input type="text" name="name_tamil" id="name_tamil" required>
    </div>
    </div>
    
    <div class="form-grid">
    <div class="form-row">
    <label>Date of Birth as Per T.C <span class="required-star">*</span></label>
    <input type="date" name="dob" required max="<?php echo date('Y-m-d', strtotime('-17 years')); ?>" required>
    </div>
    <div class="form-row">
    <label>Age <span class="required-star">*</span></label>
    <input type="text" name="age" readonly>
    </div>
    
    <div class="form-row">
    <label>Gender<span class="required-star">*</span></label>
    <div class="radio-group">
    <label class="radio-item"><input type="radio" name="gender" value="Male" required>Male</label>
    <label class="radio-item"><input type="radio" name="gender" value="Female">Female</label>
    <label class="radio-item"><input type="radio" name="gender" value="Transgender">Transgender</label>
    </div>
    </div>
   </div>
   <div class="form-grid">
    <div class="form-row">
    <label>2.Father's / Guardian Name <span class="required-star">*</span></label>
    <input type="text" name="guardian_name" required>
    </div>
    <div class="form-row">
    <label>Mother's Name <span class="required-star">*</span></label>
    <input type="text" name="mother_name" required>
    </div>
</div>
    <div class="form-row">
      <label>Aadhaar Number <span class="required-star">*</span></label>
        <input type="text"
               name="aadhaar"
               id="aadhaar"
               maxlength="14"
               placeholder="XXXX XXXX XXXX"
        required>
    </div>
    <div class="form-grid">
    <div class="form-row">
    <label>Nationality <span class="required-star">*</span></label>
    <input type="text" name="nationality" value="INDIAN" required>
    </div>
    <div class="form-row">
    <label>Religion <span class="required-star">*</span></label>
    <input type="text" name="religion" required>
    </div>
</div>
    <div class="form-grid">

  <div class="form-row">
    <label>Mother Tongue <span class="required-star">*</span></label>
    <input type="text" name="mother_tongue" required>
  </div>

  <div class="form-row">
    <label>Blood Group <span class="required-star">*</span></label>
    <select name="blood_group" required>
      <option value="">Select Blood Group</option>
      <option value="A+">A+</option>
      <option value="A-">A-</option>
      <option value="B+">B+</option>
      <option value="B-">B-</option>
      <option value="O+">O+</option>
      <option value="O-">O-</option>
      <option value="AB+">AB+</option>
      <option value="AB-">AB-</option>
    </select>
  </div>

</div>
    <div class="form-grid">
    <div class="form-row">
    <label>Community <span class="required-star">*</span></label>
    <div class="radio-group">
    <label class="radio-item"><input type="radio" name="community" value="OC" required>OC</label>
    <label class="radio-item"><input type="radio" name="community" value="BC">BC</label>
    <label class="radio-item"><input type="radio" name="community" value="MBC">MBC</label>
    <label class="radio-item"><input type="radio" name="community" value="SC">SC</label>
    <label class="radio-item"><input type="radio" name="community" value="ST">ST</label>
    </div>
    </div>
    <div class="form-row">
    <label>Caste <span class="required-star">*</span></label>
    <input type="text" name="caste" required>
    </div>
</div>
    </fieldset>
    <!-- ✅ 23. EMPLOYMENT ADDED -->
    <!-- EMPLOYMENT -->
    <div class="form-row">
      <label>
         23. Are you currently employed?
         <span class="required-star">*</span>
      </label>
    <div class="inline">
     <label>
        <input type="radio" name="employment_status" value="yes" required> Yes
     </label>
     <label>
       <input type="radio" name="employment_status" value="no"> No
     </label>
    </div>
</div>
</fieldset>
<!-- ORGANIZATION -->
 <div id="employmentOptions"
     style="display:none; margin-top:10px;">
    <label>Select Organization:</label>
    <div class="inline">
      <label>
         <input type="radio" name="employment_type" value="University of Madras"> University of Madras
      </label>
      <label>
         <input type="radio" name="employment_type" value="Others"> Others
      </label>
    </div>
  </div>
  
   <div class="form-buttons">
    <button type="submit" class="save-btn">Save and Continue</button>
    <button type="reset" class="reset-btn">Reset</button>
</div>
    </form>
    </div>
    </section>
    
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
    <script>
    document.querySelector("input[name='dob']").addEventListener("change", function(){
    let dob = new Date(this.value);
    let today = new Date();
    let age = today.getFullYear() - dob.getFullYear();
    document.querySelector("input[name='age']").value = age;
    });
    document.getElementById("photoInput").addEventListener("change", function(e){
    const file = e.target.files[0];
    if(file){
    const reader = new FileReader();
    reader.onload = function(event){
    const img = document.getElementById("photoPreview");
    img.src = event.target.result;
    img.style.display = "block";
    };
    reader.readAsDataURL(file);
    }
    });
    /* ===================================================
    EMPLOYMENT YES / NO TOGGLE
    =================================================== */
    document.addEventListener("DOMContentLoaded", function () {
    const employmentOptions =
        document.getElementById("employmentOptions");
    const employmentRadios =
        document.querySelectorAll(
            'input[name="employment_status"]'
        );
    if (employmentOptions && employmentRadios.length > 0) {
        employmentRadios.forEach(radio => {
            radio.addEventListener("change", function () {
                if (this.value === "yes") {
                    employmentOptions.style.display = "block";
                } else {
                    employmentOptions.style.display = "none";
                    /* Clear selection */
                    document.querySelectorAll(
                        'input[name="employment_type"]'
                    ).forEach(r => r.checked = false);
                }
            });
        });
    }
  });
 /* =========================================
   AADHAAR AUTO FORMAT + VALIDATION
========================================= */
const aadhaarInput =
  document.getElementById("aadhaar");
if (aadhaarInput) {
  /* FORMAT WHILE TYPING */
  aadhaarInput.addEventListener("input", function () {
    // Remove non-digits
    let digits =
      this.value.replace(/\D/g, "");
    // Limit to 12 digits
    digits = digits.substring(0, 12);
    // Add space every 4 digits
    digits =
      digits.replace(/(.{4})/g, "$1 ").trim();
    this.value = digits;
  });
  /* ALLOW ONLY NUMBERS (BUT KEEP CONTROL KEYS) */
  aadhaarInput.addEventListener("keydown", function (e) {
    const allowedKeys = [
      "Backspace",
      "Delete",
      "ArrowLeft",
      "ArrowRight",
      "Tab"
    ];
    if (
      allowedKeys.includes(e.key) ||
      /^[0-9]$/.test(e.key)
    ) {
      return; // allow
    }
    e.preventDefault(); // block others
  });
  /* PASTE VALIDATION */
  aadhaarInput.addEventListener("paste", function (e) {
    e.preventDefault();
    let pasteData =
      (e.clipboardData || window.clipboardData)
      .getData("text");
    pasteData =
      pasteData.replace(/\D/g, "")
               .substring(0, 12);
    pasteData =
      pasteData.replace(/(.{4})/g, "$1 ").trim();
    this.value = pasteData;
  });
 }
let courseData = []; // store all data
function loadCourses() {
    let type =
        document.getElementById("course_type").value;
    if (!type) return;
    fetch("fetch_courses.php?type=" + type)
    .then(res => res.json())
    .then(data => {
        courseData = data; // save globally
        let programme =
            document.getElementById("programme_name");
        programme.innerHTML =
            "<option value=''>Select Programme</option>";
        /* Get unique degrees */
        let degrees = [...new Set(
            data.map(row => row.programme_degree)
        )];
        degrees.forEach(deg => {
            programme.innerHTML +=
            `<option value="${deg}">
                ${deg}
             </option>`;
        });
        /* Clear subject */
        document.getElementById(
            "main_subject"
        ).innerHTML =
        "<option>Select Subject</option>";
    });
}
/* =========================================
   FILTER SUBJECT BASED ON DEGREE
========================================= */
document.getElementById("programme_name")
.addEventListener("change", function() {
    let selectedDegree = this.value;
    let subject =
        document.getElementById("main_subject");
    subject.innerHTML =
        "<option>Select Subject</option>";
    courseData
    .filter(row =>
        row.programme_degree === selectedDegree
    )
    .forEach(row => {
        subject.innerHTML +=
        `<option value="${row.main_subject}">
            ${row.main_subject}
         </option>`;
    });
});
document.addEventListener("DOMContentLoaded", function () {

  const stateSelect = document.getElementById("state");
  const districtSelect = document.getElementById("district");

  fetch("fetch_states.php")
    .then(res => res.json())
    .then(states => {
      states.forEach(s => {
        stateSelect.innerHTML += 
          `<option value="${s.id}">${s.state_name}</option>`;
      });
    });

  stateSelect.addEventListener("change", function () {

    districtSelect.innerHTML = `<option>Select District</option>`;

    if (!this.value) return;

    fetch("fetch_districts.php?state_id=" + this.value)
      .then(res => res.json())
      .then(districts => {
        districts.forEach(d => {
          districtSelect.innerHTML += 
            `<option value="${d}">${d}</option>`;
        });
      });

  });

});

    </script>
    <script src="https://inputtools.google.com/request?itc=ta-t-i0-und&num=5"></script>
<script>
const englishInput = document.getElementById("name_english");
const tamilInput = document.getElementById("name_tamil");
englishInput.addEventListener("input", async function () {
    const text = this.value;
    if (text.length === 0) {
        tamilInput.value = "";
        return;
    }
    const response = await fetch(
        "https://inputtools.google.com/request?text=" +
        encodeURIComponent(text) +
        "&itc=ta-t-i0-und&num=5"
    );
    const data = await response.json();
    if (data[0] === "SUCCESS") {
        tamilInput.value = data[1][0][1][0];
    }
});
/* =========================================
   FOUNDATION LANGUAGE TOGGLE
========================================= */
function toggleFoundation() {
    const courseType =
        document.getElementById("course_type").value;
    const foundationRadios =
        document.querySelectorAll(
            'input[name="foundation_lang"]'
        );
    if (courseType === "UG") {
        foundationRadios.forEach(radio => {
            radio.disabled = false;
            radio.required = true;   // make required for UG
        });
    } else {
        foundationRadios.forEach(radio => {
            radio.checked = false;
            radio.disabled = true;
            radio.required = false;  // remove required
        });
    }
}
/* Run on page load */
document.addEventListener("DOMContentLoaded", toggleFoundation);
</script>

<script>

function toggleSpecialFile() {

    const selectedRadio =
        document.querySelector(
            'input[name="special_status"]:checked'
        );

    const fileBox =
        document.getElementById("specialFileBox");

    if (!selectedRadio) {
        fileBox.style.display = "none";
        return;
    }

    if (selectedRadio.value === "None") {
        fileBox.style.display = "none";
    } else {
        fileBox.style.display = "block";
    }
}
</script>
<?php

$auto_name   = trim($_POST['auto_name'] ?? '');
$auto_mobile = trim($_POST['auto_mobile'] ?? '');
$auto_email  = trim($_POST['auto_email'] ?? '');
$auto_dob    = trim($_POST['auto_dob'] ?? '');

$formDataOptions = $is_edit ? $edit_data : [

    'name'   => $auto_name,

    'mobile' => $auto_mobile,

    'email'  => $auto_email,

    'dob'    => $auto_dob

];

$formDataJson = json_encode($formDataOptions);

?>

    <script>
    const formAutoData = <?= $formDataJson ?>;
    document.addEventListener("DOMContentLoaded", function () {
        if (!formAutoData) return;
        
        for (const key in formAutoData) {
            const val = formAutoData[key];
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
                if (key === 'dob') {
                    let evt = new Event('change');
                    inputs[0].dispatchEvent(evt);
                }
            }
        }
        
        const aadhaarInput1 = document.getElementById("abc_id_input");
        if (aadhaarInput1) {
          aadhaarInput1.addEventListener("input", function () {
            let digits = this.value.replace(/\D/g, "");
            digits = digits.substring(0, 12);
            digits = digits.replace(/(.{4})/g, "$1 ").trim();
            this.value = digits;
          });
        }
    });
    </script>

    </div>
    <script>
document.querySelector("form").addEventListener("submit", function(e){

    const photo = document.getElementById("photoInput");

    if(photo.files.length === 0){

        alert("Please upload a passport photo.");

        e.preventDefault();
    }

});
</script>
</body>
</html>