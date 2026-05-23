<?php
session_start();
require_once "db.php";

// Generate CSRF token if it does not exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!isset($_SESSION['step1_data'])) {
    header("Location: ap1.php");
    exit;
}
$temp_session_id = session_id();

$course_type = $_SESSION['step1_data']['course_type'] ?? '';

$s2 = $_SESSION['step2_data'] ?? [];

if (isset($_SESSION['student_email'])) {
    $stmt = $pdo->prepare("SELECT abc_status, abc_id FROM students WHERE email = ? LIMIT 1");
    $stmt->execute([$_SESSION['student_email']]);
    $studentData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($studentData) {
        if (empty($s2['abc']) && !empty($studentData['abc_status'])) {
            $s2['abc'] = $studentData['abc_status'];
        }
        if (empty($s2['abc_id']) && !empty($studentData['abc_id'])) {
            $s2['abc_id'] = $studentData['abc_id'];
            $s2['abc_id_clean'] = preg_replace('/\s+/', '', $s2['abc_id']);
        }
        if (empty($s2['deb']) && !empty($studentData['deb_status'])) {
            $s2['deb'] = $studentData['deb_status'];
        }
        if (empty($s2['deb_id']) && !empty($studentData['deb_id'])) {
            $s2['deb_id'] = $studentData['deb_id'];
            $s2['deb_id_clean'] = preg_replace('/\s+/', '', $s2['deb_id']);
        }
        $_SESSION['step2_data'] = $s2;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admission Application - Steps 4–6 | University of Madras</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body>
<!-- HEADER -->
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
      <a class="active" href="../index.php">Home</a>
      <a href="#">About Us</a>
      <a href="#">Contact Us</a>
      <a href="../logout.php">Logout</a>
    </nav>
  </div>
</header>
<!-- BANNER --> 
 
<section class="banner">
 <main class="container">
    <div class="form-header">
        <h1>Admission Application Form</h1>
        <p>Please fill all details carefully in CAPITAL LETTERS</p>
    </div>
    <!-- Step Progress Tracker -->
    <div class="step-header">
      <div class="step-item">
        <div class="step-circle completed">&#10003;</div>
        <div class="step-label">Programme</div>
      </div>
      <div class="step-connector done" id="conn1"></div>
      <div class="step-item">
        <div class="step-circle completed">&#10003;</div>
        <div class="step-label">Address</div>
      </div>
      <div class="step-connector done" id="conn2"></div>
      <div class="step-item">
        <div class="step-circle completed">&#10003;</div>
        <div class="step-label">Applicant</div>
      </div>
      <div class="step-connector done" id="conn3"></div>
      <div class="step-item">
        <div class="step-circle active" id="sc4">4</div>
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
<form action="preview.php" method="POST" enctype="multipart/form-data">
<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">

     <div class="form-step">
      <!-- ===================== -->
<!-- ADDITIONAL DETAILS -->
<!-- ===================== -->
<fieldset>
  <legend>ADDITIONAL INFORMATION</legend>
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
    <input type="text" name="other_course_details" value="<?php echo htmlspecialchars($s2['other_course_details'] ?? ''); ?>"  placeholder="If yes, specify course / college">
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
<!-- ABC ID -->
<!-- ===================== -->
<fieldset>
  <legend>27. ACADEMIC BANK OF CREDIT (ABC)</legend>
  <div class="form-row">
    <label>Do you have Academic Bank of Credit (ABC ID)?</label>
    <div class="radio-group">
      <label class="radio-item">
        <input type="radio" name="abc" value="Yes"> Yes
      </label>
      <label class="radio-item">
        <input type="radio" name="abc" value="No"> No
      </label>
    </div>
  </div>
  <!-- ABC TEXTBOX (Hidden by default) -->
 <div id="abcBox" style="display:none; margin-top:10px;">
  <input type="text"
         name="abc_id"
         id="abc_id"
         value="<?php echo htmlspecialchars($s2['abc_id_clean'] ?? $s2['abc_id'] ?? ''); ?>"
         maxlength="14"
         placeholder="XXXX XXXX XXXX">
 </div>
</fieldset>

<!-- ===================== -->
<!-- DEB ID -->
<!-- ===================== -->
<fieldset>
  <legend>28. DISTANCE EDUCATION BUREAU (DEB)</legend>
  <div class="form-row">
    <label>Do you have a Distance Education Bureau (DEB) ID?</label>
    <div class="radio-group">
      <label class="radio-item">
        <input type="radio" name="deb" value="Yes"> Yes
      </label>
      <label class="radio-item">
        <input type="radio" name="deb" value="No"> No
      </label>
    </div>
  </div>
  <!-- DEB TEXTBOX (Hidden by default) -->
 <div id="debBox" style="display:none; margin-top:10px;">
  <input type="text"
         name="deb_id"
         id="deb_id"
         value="<?php echo htmlspecialchars($s2['deb_id_clean'] ?? $s2['deb_id'] ?? ''); ?>"
         maxlength="14"
         placeholder="XXXX XXXX XXXX">
 </div>
</fieldset>
<button type="button" onclick="window.location.href='ap1.php?step=3'">Back</button>
  <button type="button" class="nextBtn">Next</button>
</div>
<!-- ===================== -->
<!-- EXAMINATION DETAILS -->
<!-- ===================== -->
<div class="form-step" style="display:none;">
<fieldset>
  <legend>26. DETAILS OF EXAMINATION PASSED <span class="required-star">*</span></legend>
  <div class="table-wrapper">
  <table class="exam-table">
  <thead>
    <tr>
      <th>Examination Passed</th>
      <th>Name of School / College</th>
      <th>Name of Board / University</th>
      <th>Month & Year</th>
      <th>Reg No</th>
      <th>Grade</th>
      <th>Max Marks</th>
    </tr>
  </thead>

  <tbody>
    <tr>
      <td>SSLC</td>
      <td><input type="text" name="sslc_school" value="<?php echo htmlspecialchars($s2['sslc_school'] ?? ''); ?>" ></td>
      <td><input type="text" name="sslc_board" value="<?php echo htmlspecialchars($s2['sslc_board'] ?? ''); ?>" ></td>
      <td><input type="text" name="sslc_pass_year" value="<?php echo htmlspecialchars($s2['sslc_pass_year'] ?? ''); ?>" ></td>
      <td><input type="text" name="sslc_reg_no" value="<?php echo htmlspecialchars($s2['sslc_reg_no'] ?? ''); ?>" ></td>
      <td><input type="text" name="sslc_grade" value="<?php echo htmlspecialchars($s2['sslc_grade'] ?? ''); ?>" ></td>
      <td><input type="text" name="sslc_max_marks" value="<?php echo htmlspecialchars($s2['sslc_max_marks'] ?? ''); ?>" ></td>
    </tr>

    <tr>
      <td>HSC</td>
      <td><input type="text" name="hsc_school" value="<?php echo htmlspecialchars($s2['hsc_school'] ?? ''); ?>" ></td>
      <td><input type="text" name="hsc_board" value="<?php echo htmlspecialchars($s2['hsc_board'] ?? ''); ?>" ></td>
      <td><input type="text" name="hsc_pass_year" value="<?php echo htmlspecialchars($s2['hsc_pass_year'] ?? ''); ?>" ></td>
      <td><input type="text" name="hsc_reg_no" value="<?php echo htmlspecialchars($s2['hsc_reg_no'] ?? ''); ?>" ></td>
      <td><input type="text" name="hsc_grade" value="<?php echo htmlspecialchars($s2['hsc_grade'] ?? ''); ?>" ></td>
      <td><input type="text" name="hsc_max_marks" value="<?php echo htmlspecialchars($s2['hsc_max_marks'] ?? ''); ?>" ></td>
    </tr>

    <tr>
      <td>Diploma</td>
      <td><input type="text" name="dip_school" value="<?php echo htmlspecialchars($s2['dip_school'] ?? ''); ?>" ></td>
      <td><input type="text" name="dip_board" value="<?php echo htmlspecialchars($s2['dip_board'] ?? ''); ?>" ></td>
      <td><input type="text" name="dip_pass_year" value="<?php echo htmlspecialchars($s2['dip_pass_year'] ?? ''); ?>" ></td>
      <td><input type="text" name="dip_reg_no" value="<?php echo htmlspecialchars($s2['dip_reg_no'] ?? ''); ?>" ></td>
      <td><input type="text" name="dip_grade" value="<?php echo htmlspecialchars($s2['dip_grade'] ?? ''); ?>" ></td>
      <td><input type="text" name="dip_max_marks" value="<?php echo htmlspecialchars($s2['dip_max_marks'] ?? ''); ?>" ></td>
    </tr>

    <tr>
      <td>UG</td>
      <td><input type="text" name="ug_school" value="<?php echo htmlspecialchars($s2['ug_school'] ?? ''); ?>" ></td>
      <td><input type="text" name="ug_board" value="<?php echo htmlspecialchars($s2['ug_board'] ?? ''); ?>" ></td>
      <td><input type="text" name="ug_pass_year" value="<?php echo htmlspecialchars($s2['ug_pass_year'] ?? ''); ?>" ></td>
      <td><input type="text" name="ug_reg_no" value="<?php echo htmlspecialchars($s2['ug_reg_no'] ?? ''); ?>" ></td>
      <td><input type="text" name="ug_grade" value="<?php echo htmlspecialchars($s2['ug_grade'] ?? ''); ?>" ></td>
      <td><input type="text" name="ug_max_marks" value="<?php echo htmlspecialchars($s2['ug_max_marks'] ?? ''); ?>" ></td>
    </tr>
  </tbody>
</table>
</div>
</fieldset>
<!-- ===================== -->
<!-- DOCUMENT UPLOAD -->
<!-- ===================== -->

<fieldset>
  <legend>DOCUMENT UPLOAD (MAX 250KB EACH)</legend>
  <div class="note-text" style="margin-top:-10px; margin-bottom:15px; color:var(--primary); font-weight:600;">All documents must be uploaded within 250KB. Allowed formats: JPG, JPEG, PNG, PDF.</div>
  <!-- SSLC -->
  <div class="upload-row">
    <label>SSLC Statement <span class="required-star">*</span></label>
    <input type="file" name="sslc"
           id="file_sslc"
           accept=".pdf,.jpg,.jpeg,.png" <?php echo empty($s2["sslc_file"]) ? "" : ""; ?>>
<?php if(!empty($s2["sslc_file"])) echo "<br><i>(Previously uploaded: " . htmlspecialchars($s2["sslc_file"]) . ")</i>"; ?>
    <div class="error-text" id="error_sslc"></div>
  </div>
  <!-- HSC -->
  <div class="upload-row">
    <label>HSC / Diploma Statement <span class="required-star">*</span></label>
    <input type="file" name="hsc" id="file_hsc" accept=".pdf,.jpg,.jpeg,.png" <?php echo empty($s2["hsc_file"]) ? "" : ""; ?>>
<?php if(!empty($s2["hsc_file"])) echo "<br><i>(Previously uploaded: " . htmlspecialchars($s2["hsc_file"]) . ")</i>"; ?>
    <div class="error-text" id="error_hsc"></div>
  </div>
  <!-- UG -->
  <div class="upload-row">
    <label>UG Statement (PG Only)</label>
    <input type="file" name="ug" id="file_ug" accept=".pdf,.jpg,.jpeg,.png" <?php echo empty($s2["ug_file"]) ? "" : ""; ?>>
<?php if(!empty($s2["ug_file"])) echo "<br><i>(Previously uploaded: " . htmlspecialchars($s2["ug_file"]) . ")</i>"; ?>
    <div class="error-text" id="error_ug"></div>
  </div>
  <!-- TC -->
  <div class="upload-row">
    <label>Transfer Certificate <span class="required-star">*</span></label>
    <input type="file" name="tc" id="file_tc" accept=".pdf,.jpg,.jpeg,.png" <?php echo empty($s2["tc_file"]) ? "" : ""; ?>>
<?php if(!empty($s2["tc_file"])) echo "<br><i>(Previously uploaded: " . htmlspecialchars($s2["tc_file"]) . ")</i>"; ?>
    <div class="error-text" id="error_tc"></div>
  </div>
  <!-- Migration -->
  <div class="upload-row">
    <label>Migration Certificate <span class="required-star">*</span></label>
    <input type="file" name="migration" id="file_migration" accept=".pdf,.jpg,.jpeg,.png" <?php echo empty($s2["migration_file"]) ? "" : ""; ?>>
<?php if(!empty($s2["migration_file"])) echo "<br><i>(Previously uploaded: " . htmlspecialchars($s2["migration_file"]) . ")</i>"; ?>
    <div class="error-text" id="error_migration"></div>
  </div>
  <!-- Undertaking -->
  <div class="upload-row">
    <label>Undertaking <span class="required-star">*</span></label>
    <input type="file" name="undertaking" id="file_undertaking" accept=".pdf,.jpg,.jpeg,.png" <?php echo empty($s2["undertaking_file"]) ? "" : ""; ?>>
<?php if(!empty($s2["undertaking_file"])) echo "<br><i>(Previously uploaded: " . htmlspecialchars($s2["undertaking_file"]) . ")</i>"; ?>
    <div class="error-text" id="error_undertaking"></div>
  </div>
</fieldset>

<button type="button" class="prevBtn">Back</button>
<button type="button" class="nextBtn">Next</button>
</div>
<div class="form-step" style="display:none;">
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
</fieldset>
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
<label><strong>Signature of the Applicant</strong></label><br><br>

<input type="file" name="signature" id="file_signature" accept=".jpg,.jpeg,.png" <?php echo empty($s2["signature_file"]) ? "required" : ""; ?>>
<?php if(!empty($s2["signature_file"])) echo "<i>(Previously uploaded: " . htmlspecialchars($s2["signature_file"]) . ")</i>"; ?>

<div class="error-text" id="error_signature"></div>

</div>
   </div> 
</fieldset>
      <div id="validationSummary" style="color:red; margin-bottom:10px;"></div>
      <div class="actions">
        <button type="button" class="prevBtn">Back</button>
        <button type="submit" id="finalNext">Preview Application</button>
        <button type="reset" class="secondary">RESET</button>
      </div>
</form>
</main>
</section>
<!-- FOOTER -->
<footer>
  <p>© 2026 University of Madras. All Rights Reserved.</p>
</footer>
<script src="script.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {

  let steps = document.querySelectorAll(".form-step");
  let circles = document.querySelectorAll(".step-circle:not(.completed)");
  let connectors = document.querySelectorAll(".step-connector:not(.done)");

  let currentStep = 0;

  function showStep(step) {

    steps.forEach((s, i) => {
      s.style.display = (i === step) ? "block" : "none";
    });

    // Update dynamic circles (sc4, sc5, sc6) and their connectors
    const dynamic = [
      { circle: document.getElementById('sc4'), conn: document.getElementById('conn4') },
      { circle: document.getElementById('sc5'), conn: document.getElementById('conn5') },
    ];
    dynamic.forEach((item, i) => {
      if (!item.circle) return;
      item.circle.classList.remove('active', 'completed');
      if (i < step) {
        item.circle.classList.add('completed');
        item.circle.innerHTML = '&#10003;';
        if (item.conn) item.conn.classList.add('done');
      } else if (i === step) {
        item.circle.classList.add('active');
        item.circle.innerHTML = (i + 4);
      } else {
        item.circle.innerHTML = (i + 4);
        if (item.conn) item.conn.classList.remove('done');
      }
    });
    const sc6 = document.getElementById('sc6');
    if (sc6) {
      sc6.classList.remove('active', 'completed');
      if (step >= 2) { sc6.classList.add('active'); }
    }
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

  /* ABC SHOW / HIDE */
  document.querySelectorAll('input[name="abc"]').forEach(r => {
    r.addEventListener("change", function () {
      document.getElementById("abcBox").style.display =
        (this.value === "Yes") ? "block" : "none";
    });
  });

  /* DEB SHOW / HIDE */
  document.querySelectorAll('input[name="deb"]').forEach(r => {
    r.addEventListener("change", function () {
      document.getElementById("debBox").style.display =
        (this.value === "Yes") ? "block" : "none";
    });
  });

  showStep(currentStep);

});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const s2 = <?php echo json_encode($s2); ?>;
    if (!s2 || Object.keys(s2).length === 0) return;

    const setRadio = (name, value) => {
        if (!value) return;
        const radios = document.querySelectorAll(`input[type='radio'][name='${name}']`);
        radios.forEach(r => { if (r.value === value) r.checked = true; });
    };

    setRadio('other_course', s2.other_course);
    setRadio('defence_status', s2.defence_status);
    setRadio('abc', s2.abc);
    setRadio('deb', s2.deb);

    if (s2.other_course === 'Yes') {
        const otherCourseBox = document.getElementById("otherCourseBox");
        if(otherCourseBox) otherCourseBox.style.display = "block";
    }

    if (s2.abc === 'Yes') {
        const abcBox = document.getElementById("abcBox");
        if(abcBox) abcBox.style.display = "block";
    }

    if (s2.deb === 'Yes') {
        const debBox = document.getElementById("debBox");
        if(debBox) debBox.style.display = "block";
    }

    // Checkboxes for enclosures
    if (s2.enclosures && typeof s2.enclosures === 'string') {
        const encs = s2.enclosures.split(',');
        document.querySelectorAll('input[type="checkbox"][name="enclosures[]"]').forEach(cb => {
            if (encs.includes(cb.value)) cb.checked = true;
        });
    } else if (s2.enclosures && Array.isArray(s2.enclosures)) {
        document.querySelectorAll('input[type="checkbox"][name="enclosures[]"]').forEach(cb => {
            if (s2.enclosures.includes(cb.value)) cb.checked = true;
        });
    }
});
document.getElementById("finalNext").addEventListener("click", function(){

    let errors = [];

    // File validation
    const allowed = ["application/pdf","image/jpeg","image/png"];
    const maxSize = 250 * 1024;

    document.querySelectorAll("input[type='file']").forEach(f => {
        if(f.files.length > 0){
            let file = f.files[0];

            if(!allowed.includes(file.type)){
                errors.push(f.name + " invalid file type");
            }

            if(file.size > maxSize){
                errors.push(f.name + " exceeds 250KB");
            }
        }
    });

    // TC OR Migration rule
    let tc = document.getElementById("file_tc").files.length;
    let migration = document.getElementById("file_migration").files.length;

    if(!tc && !migration){
        errors.push("Upload either TC or Migration Certificate");
    }

    // UG rule
    let ug = document.getElementById("file_ug").files.length;

    if(!ug){
        errors.push("Upload UG / Provisional Certificate");
    }

    // Show errors
    if(errors.length > 0){
        document.getElementById("validationSummary").innerHTML = errors.join("<br>");
        return;
    }

    // If valid → submit form
    document.querySelector("form").submit();
});
</script>
</body>

</html>