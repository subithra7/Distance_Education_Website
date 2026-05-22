<?php
session_start();
require "db.php";

/* ===== FETCH STATES ===== */
$states = $pdo->query("SELECT * FROM states ORDER BY state_name ASC")->fetchAll();

/* ===== FETCH DISTRICTS ===== */
$districts = $pdo->query("SELECT * FROM districts ORDER BY district_name ASC")->fetchAll();



$s1 = $_SESSION['step1_data'] ?? [];
if (isset($_SESSION['student_email'])) {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE email = ? LIMIT 1");
    $stmt->execute([$_SESSION['student_email']]);
    $studentData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($studentData) {
        $dob = $studentData['dob'] ?? '';
        $age = '';
        if ($dob) {
            try {
                $dobDate = new DateTime($dob);
                $today = new DateTime();
                $age = $today->diff($dobDate)->y;
            } catch (Exception $e) {}
        }
        
        $courseType = $studentData['level'] ?? '';
        if ($courseType === 'Diploma') $courseType = 'DIP';
        if ($courseType === 'Certificate') $courseType = 'CERT';

        $courseName = $studentData['course'] ?? '';
        $programmeDegree = $courseName;
        $mainSubject = '';

        if ($courseName && $courseType) {
            $table = '';
            switch ($courseType) {
                case "UG":  $table = "ug_courses"; break;
                case "PG":  $table = "pg_courses"; break;
                case "DIP": $table = "diploma_courses"; break;
                case "CERT":$table = "certificate_courses"; break;
            }
            if ($table) {
                $stmtCourse = $pdo->prepare("SELECT programme_degree, main_subject FROM \"$table\" WHERE course_name = ? LIMIT 1");
                $stmtCourse->execute([$courseName]);
                if ($cData = $stmtCourse->fetch(PDO::FETCH_ASSOC)) {
                    $programmeDegree = $cData['programme_degree'];
                    $mainSubject = $cData['main_subject'];
                }
            }
        }

        // Always sync these from DB to ensure recent data is used instead of old cached session data
        $s1['course_type'] = $courseType;
        $s1['programme_name'] = $programmeDegree;
        $s1['main_subject'] = $mainSubject;
        
        $s1['name'] = $studentData['name'] ?? '';
        // Only set name_english if it's empty, to preserve any manual edits or translations
        if (empty($s1['name_english'])) {
            $s1['name_english'] = $studentData['name'] ?? '';
        }
        
        $s1['email'] = $studentData['email'] ?? '';
        $s1['mobile'] = $studentData['mobile'] ?? '';
        $s1['dob'] = $dob;
        $s1['age'] = $age;

        $_SESSION['step1_data'] = $s1;
    }
}
// Always start at Step 1 — use GET step only for explicit back navigation
// Never read current_step from session here (avoids jumping to step 3 on re-login)
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admission Application - Step 1 | University of Madras</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    </head>
    <body>
    <header class="top-header">
  <div class="app">
    <div class="logo">
      <img src="image/Univ.png" alt="University of Madras Logo">
      <div class="logo-text">
        <div class="tamil-text">சென்னை பல்கலைக்கழகம் – தொலைதூரக் கல்வி நிறுவனம்</div>
        <div class="english-text">University of Madras – Institute of Distance Education</div>
      </div>
    </div>
    <nav class="nav">
      <a href="../index.php">Home</a>
      <a href="#">Contact</a>
      <a href="../logout.php">Logout</a>
    </nav>
  </div>
 </header>
    <section class="banner">
    <div class="container">
    <div class="form-header">
    <h1>Admission Application Form</h1>
    <p>Please fill all details carefully in CAPITAL LETTERS</p>
  </div>
  <!-- Step Progress Tracker -->
  <div class="step-header">
    <div class="step-item">
      <div class="step-circle active">1</div>
      <div class="step-label">Programme</div>
    </div>
    <div class="step-connector" id="conn1"></div>
    <div class="step-item">
      <div class="step-circle" id="sc2">2</div>
      <div class="step-label">Address</div>
    </div>
    <div class="step-connector" id="conn2"></div>
    <div class="step-item">
      <div class="step-circle" id="sc3">3</div>
      <div class="step-label">Applicant</div>
    </div>
    <div class="step-connector" id="conn3"></div>
    <div class="step-item">
      <div class="step-circle" id="sc4">4</div>
      <div class="step-label">Additional</div>
    </div>
    <div class="step-connector" id="conn4"></div>
    <div class="step-item">
      <div class="step-circle" id="sc5">5</div>
      <div class="step-label">Exams</div>
    </div>
    <div class="step-connector" id="conn5"></div>
    <div class="step-item">
      <div class="step-circle" id="sc6">6</div>
      <div class="step-label">Submit</div>
    </div>
  </div>
    <form action="process.php?action=step3" method="POST" enctype="multipart/form-data">
    <!-- STEP 1 -->
<div class="form-step">

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
    <div class="form-grid">
    <div class="form-row">
    <label>Course Type *</label>
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
    <div class="form-row">
    <label>Medium of Study *</label>
    <select name="medium" required>
        <option value="">Select Medium</option>
        <option value="tamil">Tamil</option>
        <option value="english">English</option>
        <option value="french">French</option>
    </select>
    </div>
    </div>
    <div class="form-grid">
    <div class="form-row">
<label>Name of the Programme</label>
<select name="programme_name" id="programme_name" required>
<option value="">Select Programme</option>
</select>
</div>
<div class="form-row">
<label>Main Subject</label>
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
    <div class="photo-box">
    <label><strong>Recent Passport Photo</strong></label>
    <div class="upload-area" onclick="document.getElementById('photoInput').click();">
    <img id="photoPreview">
    <span id="uploadText">Click to Upload</span>
    </div>
    <input type="file" name="photo" id="photoInput" accept="image/*" hidden>
    <div class="note-text" style="text-align:center; margin-top:5px; font-size:11px;">Passport size photo should be within 250KB.<br>Allowed formats: JPG, JPEG, PNG.</div>
    </div>
    </div>
    </fieldset>
    
    <button type="button" class="nextBtn">Next</button>
</div>

<!-- STEP 2 -->
<div class="form-step" style="display:none;">

     <!-- ADDRESS FOR COMMUNICATION -->
    <fieldset>
    <legend>ADDRESS FOR COMMUNICATION</legend>
    <div class="form-grid">
    <div class="form-row">
    <label>Name *</label>
    <input type="text" name="name" value="<?php echo htmlspecialchars($s1['name'] ?? ''); ?>"  required>
    </div>
    <div class="form-row">
    <label>Door No & Street *</label>
    <input type="text" name="street" value="<?php echo htmlspecialchars($s1['street'] ?? ''); ?>"  required>
    </div>
</div>
    <div class="form-grid">
   <div class="form-row">
      <label>Town/Village Post *</label>
      <input type="text" name="town" value="<?php echo htmlspecialchars($s1['town'] ?? ''); ?>" >
   </div>

   <div class="form-row">
      <label>Pin Code *</label>
      <input type="text" name="pincode" value="<?php echo htmlspecialchars($s1['pincode'] ?? ''); ?>" >
   </div>
</div>
<div class="form-grid">
    <div class="form-row">
    <label for="state">State </label>
    <select name="state" id="state" required>
        <option value="">Select State</option>
    </select>
</div>

<div class="form-row">
    <label for="district">District </label>
    <select name="district" id="district" required>
        <option value="">Select District</option>
    </select>
</div>
</div>

<!-- ✅ ADD THIS -->
<div class="form-row">
    <label>Urban / Rural *</label>
    <select name="urban_rural" required>
        <option value="">Select Option</option>
        <option value="Urban">Urban</option>
        <option value="Rural">Rural</option>
    </select>
</div>
    <div class="form-grid">
    <div class="form-row">
    <label>Phone No (Res/Off) *</label>
    <input type="text" name="phone" value="<?php echo htmlspecialchars($s1['phone'] ?? ''); ?>"  maxlength="10" required>
    </div>
    <div class="form-row">
    <label>Mobile Number *</label>
    <input type="text" name="mobile" value="<?php echo htmlspecialchars($s1['mobile'] ?? ''); ?>"  maxlength="10" required>
    </div>
    </div>
    <div class="form-row">
    <label>Email ID *</label>
    <input type="email" name="email" value="<?php echo htmlspecialchars($s1['email'] ?? ''); ?>"  required>
    </div>
    </fieldset>

    <button type="button" class="prevBtn">Back</button>
    <button type="button" class="nextBtn">Next</button>
</div>

<!-- STEP 3 -->
<div class="form-step" style="display:none;">

     <!-- APPLICANT DETAILS -->
    <fieldset>
    <legend>APPLICANT DETAILS</legend>
    <div class="form-grid">
    <div class="form-row">
    <label>1.(a)Name (English in CAPITAL LETTERS) *</label>
    <input type="text" name="name_english" value="<?php echo htmlspecialchars($s1['name_english'] ?? ''); ?>"  id="name_english" required>
    </div>
    <div class="form-row">
    <label> (b)Name (Tamil) *</label>
    <input type="text" name="name_tamil" value="<?php echo htmlspecialchars($s1['name_tamil'] ?? ''); ?>"  id="name_tamil" required>
    </div>
    </div>
    
    <div class="form-grid">
    <div class="form-row">
    <label>Date of Birth as Per T.C *</label>
    <input type="date" name="dob" value="<?php echo htmlspecialchars($s1['dob'] ?? ''); ?>"  required max="<?php echo date('Y-m-d', strtotime('-17 years')); ?>">
    </div>
    <div class="form-row">
    <label>Age *</label>
    <input type="text" name="age" value="<?php echo htmlspecialchars($s1['age'] ?? ''); ?>"  readonly>
    </div>
    <div class="form-row">
    <label>Gender</label>
    <div class="radio-group">
    <label class="radio-item"><input type="radio" name="gender" value="Male">Male</label>
    <label class="radio-item"><input type="radio" name="gender" value="Female">Female</label>
    <label class="radio-item"><input type="radio" name="gender" value="Transgender">Transgender</label>
    </div>
    </div>
   </div>
   <div class="form-grid">
    <div class="form-row">
    <label>2.Father's / Guardian Name *</label>
    <input type="text" name="guardian_name" value="<?php echo htmlspecialchars($s1['guardian_name'] ?? ''); ?>"  required>
    </div>
    <div class="form-row">
    <label>Mother's Name *</label>
    <input type="text" name="mother_name" value="<?php echo htmlspecialchars($s1['mother_name'] ?? ''); ?>"  required>
    </div>
</div>
    <div class="form-row">
      <label>Aadhaar Number <span class="required-star">*</span></label>
        <input type="text"
               name="aadhaar"
               id="aadhaar"
               value="<?php echo htmlspecialchars($s1['aadhaar'] ?? ''); ?>"
               maxlength="14"
               placeholder="XXXX XXXX XXXX"
        required>
    </div>
    <div class="form-grid">
    <div class="form-row">
    <label>Nationality *</label>
    <input type="text" name="nationality" value="<?php echo htmlspecialchars($s1['nationality'] ?? ''); ?>"  value="INDIAN" required>
    </div>
    <div class="form-row">
    <label>Religion *</label>
    <select name="religion" required>
        <option value="">Select Religion</option>
        <option value="Hindu">Hindu</option>
        <option value="Muslim">Muslim</option>
        <option value="Christian">Christian</option>
        <option value="Sikh">Sikh</option>
        <option value="Buddhist">Buddhist</option>
        <option value="Jain">Jain</option>
        <option value="Others">Others</option>
    </select>
    </div>
</div>
    <div class="form-grid">

  <div class="form-row">
    <label>Mother Tongue *</label>
    <input type="text" name="mother_tongue" value="<?php echo htmlspecialchars($s1['mother_tongue'] ?? ''); ?>"  required>
  </div>

  <div class="form-row">
    <label>Blood Group *</label>
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
    <label>Community *</label>
    <div class="radio-group">
    <label class="radio-item"><input type="radio" name="community" value="OC" required>OC</label>
    <label class="radio-item"><input type="radio" name="community" value="BC">BC</label>
    <label class="radio-item"><input type="radio" name="community" value="MBC">MBC</label>
    <label class="radio-item"><input type="radio" name="community" value="SC">SC</label>
    <label class="radio-item"><input type="radio" name="community" value="ST">ST</label>
    </div>
    </div>
    <div class="form-row">
    <label>Caste *</label>
    <input type="text" name="caste" value="<?php echo htmlspecialchars($s1['caste'] ?? ''); ?>"  required>
    </div>
</div>
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
    <button type="button" class="prevBtn">Back</button>
    <button type="button" id="finalNext">Next</button>

</div>
    </form>
    </div>
    </section>
    <footer>
    © 2026 University of Madras. All Rights Reserved.
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
    if(file.size > 250 * 1024){
        alert("Passport photo size must be 250KB or less.");
        this.value = "";
        document.getElementById("photoPreview").style.display = "none";
        return;
    }
    const reader = new FileReader();
    reader.onload = function(event){
    const img = document.getElementById("photoPreview");
    img.src = event.target.result;
    img.style.display = "block";
    };
    reader.readAsDataURL(file);
    }
    });

    document.querySelector("input[name='special_file']").addEventListener("change", function(e){
        const file = e.target.files[0];
        if(file && file.size > 250 * 1024){
            alert("File size must be 250KB or less.");
            this.value = "";
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
document.getElementById("name_english").addEventListener("input", function() {
    document.getElementById("name_tamil").value = this.value;
});

</script>
<script>
function toggleSpecialFile() {

  const selected =
    document.querySelector('input[name="special_status"]:checked').value;

  const fileBox =
    document.getElementById("specialFileBox");

  if(selected === "None"){
      fileBox.style.display = "none";
  }else{
      fileBox.style.display = "block";
  }

}
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {

  let steps = document.querySelectorAll(".form-step");
  let circles = document.querySelectorAll(".step-circle");
  let connectors = document.querySelectorAll(".step-connector");

  let currentStep = 0; // Always start at step 1 (index 0)

  function showStep(step) {
    steps.forEach((s, i) => {
      s.style.display = (i === step) ? "block" : "none";
      if (i === step) s.classList.add("active");
      else s.classList.remove("active");

      circles[i].classList.remove("active", "completed");

      if (i < step) {
        circles[i].classList.add("completed");
      } else if (i === step) {
        circles[i].classList.add("active");
      }
    });
    // Update connectors
    connectors.forEach((c, i) => {
      c.classList.toggle("done", i < step);
    });
    // Scroll to top of form
    document.querySelector(".container").scrollIntoView({ behavior: "smooth", block: "start" });
  }

  document.querySelectorAll(".nextBtn").forEach(btn => {
    btn.addEventListener("click", () => {
      if (currentStep < steps.length - 1) {
        currentStep++;
        showStep(currentStep);
      }
    });
  });

  document.querySelectorAll(".prevBtn").forEach(btn => {
    btn.addEventListener("click", () => {
      if (currentStep > 0) {
        currentStep--;
        showStep(currentStep);
      }
    });
  });

  showStep(currentStep);
});
</script>
<script>
// FINAL STEP SUBMIT (Step 3 → ap2.php)
document.getElementById("finalNext").addEventListener("click", function () {
    document.querySelector("form").submit();
});
</script>


<script>
document.addEventListener("DOMContentLoaded", function () {
    const s1 = <?php echo json_encode($s1); ?>;
    if (!s1 || Object.keys(s1).length === 0) return;

    // Radios
    const setRadio = (name, value) => {
        if (!value) return;
        const radios = document.querySelectorAll(`input[type='radio'][name='${name}']`);
        radios.forEach(r => { if (r.value === value) r.checked = true; });
    };
    
    setRadio('foundation_lang', s1.foundation_lang);
    setRadio('special_status', s1.special_status);
    setRadio('gender', s1.gender);
    setRadio('community', s1.community);
    setRadio('employment_status', s1.employment_status);
    setRadio('employment_type', s1.employment_type);

    if (s1.medium) {
        let medSelect = document.querySelector("select[name='medium']");
        if(medSelect) medSelect.value = s1.medium;
    }

    if (s1.religion) {
        let relSelect = document.querySelector("select[name='religion']");
        if(relSelect) relSelect.value = s1.religion;
    }

    if (s1.urban_rural) {
        let urSelect = document.querySelector("select[name='urban_rural']");
        if(urSelect) urSelect.value = s1.urban_rural;
    }

    if (s1.blood_group) {
        let bgSelect = document.querySelector("select[name='blood_group']");
        if(bgSelect) bgSelect.value = s1.blood_group;
    }

    // Call toggles based on radio states
    if (typeof toggleSpecialFile === 'function') toggleSpecialFile();
    if (s1.employment_status === 'no') {
        let employmentOptions = document.getElementById("employmentOptions");
        if (employmentOptions) employmentOptions.style.display = "none";
    }

    // Helper to safely trigger inline onchange or change events
    const triggerChange = (el) => {
        if (typeof el.onchange === 'function') {
            el.onchange();
        } else {
            el.dispatchEvent(new Event('change'));
        }
    };

    // Helper to select an option by text or value
    const selectOption = (el, val) => {
        if (!val) return false;
        for (let i = 0; i < el.options.length; i++) {
            if (el.options[i].value === val || el.options[i].text === val) {
                el.selectedIndex = i;
                return true;
            }
        }
        return false;
    };

    // ASYNC AUTO-FILLS
    if (s1.state) {
        let stateInterval = setInterval(() => {
            let stateEl = document.getElementById('state');
            if (stateEl && stateEl.options.length > 1) {
                if (selectOption(stateEl, s1.state)) {
                    triggerChange(stateEl);
                }
                clearInterval(stateInterval);
                
                if (s1.district) {
                    let distInterval = setInterval(() => {
                        let distEl = document.getElementById('district');
                        if (distEl && distEl.options.length > 1) {
                            selectOption(distEl, s1.district);
                            clearInterval(distInterval);
                        }
                    }, 100);
                }
            }
        }, 100);
    }

    if (s1.course_type) {
        let cType = document.getElementById("course_type");
        if (cType) {
            if (selectOption(cType, s1.course_type)) {
                triggerChange(cType);
                
                if (s1.programme_name) {
                    let progInterval = setInterval(() => {
                        let progEl = document.getElementById('programme_name');
                        if (progEl && progEl.options.length > 1) {
                            if (selectOption(progEl, s1.programme_name)) {
                                triggerChange(progEl);
                            }
                            clearInterval(progInterval);
                            
                            if (s1.main_subject) {
                                let subInterval = setInterval(() => {
                                    let subEl = document.getElementById('main_subject');
                                    if (subEl && subEl.options.length > 1) {
                                        selectOption(subEl, s1.main_subject);
                                        clearInterval(subInterval);
                                    }
                                }, 100);
                            }
                        }
                    }, 100);
                }
            }
        }
    }
});
</script>
</body>

</html>
    </body>
    </html>