<?php
session_start();

// Generate CSRF token if it does not exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* -------------------------------
   SERVER-SIDE VALIDATION
-------------------------------- */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF verification failed.");
    }

    /* AGE VALIDATION (17+) */
    $dob = $_POST['dob'];
    $dobDate = new DateTime($dob);
    $today   = new DateTime();
    $age     = $today->diff($dobDate)->y;

    if ($age < 17) {
        echo "<script>
            alert('Applicant must be at least 17 years old.');
            window.history.back();
        </script>";
        exit;
    }

    /* EMAIL VALIDATION */
    $email        = $_POST['email'];
    $confirmEmail = $_POST['confirm_email'];

    if ($email !== $confirmEmail) {
        echo "<script>
            alert('Emails do not match.');
            window.history.back();
        </script>";
        exit;
    }

    /* PASSWORD VALIDATION */
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    if ($password !== $confirm) {
        echo "<script>
            alert('Passwords do not match.');
            window.history.back();
        </script>";
        exit;
    }

    if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\\d)(?=.*[@$!%*?&]).{8,}$/', $password)) {
        echo "<script>
            alert('Password must contain uppercase, lowercase, number & special character (min 8 chars).');
            window.history.back();
        </script>";
        exit;
    }

    /* HASH PASSWORD */
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $_SESSION['form_data'] = [
        'programme'   => $_POST['programme'],
        'name'        => $_POST['name'],
        'mobile'      => $_POST['mobile'],
        'email'       => $email,
        'password'    => $hashedPassword,
        'dob'         => $_POST['dob'],
        'course_id'   => $_POST['course'],
        'course_name' => $_POST['course_name'],
        'eligibility' => $_POST['eligibility'],
        'abc_status'  => $_POST['abc_status'],
        'abc_id'      => $_POST['abc_id'] ?? null,
        'deb_status'  => $_POST['deb_status'],
        'deb_id'      => $_POST['deb_id'] ?? null
    ];

    header("Location: next.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Distance Education </title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="top-header">

    <div class="header-container">

        <div class="header-top">

            <!-- LEFT LOGO -->
            <div class="logo-section">

                <img src="image/Univ.png" alt="University Logo">

            </div>

            <!-- CENTER TEXT -->
            <div class="title-section">

                <div class="tamil-text">
                    சென்னை பல்கலைக்கழகம் – தொலைதூரக் கல்வி நிறுவனம்
                </div>

                <div class="english-text">
                    University of Madras – Institute of Distance Education
                </div>

                <div class="sub-text">
                    Affiliated to University of Madras | NAAC Accredited with Grade “A++”<br>
                    A Premier Distance Education Institution<br>
                    Chepauk Campus, Chennai – 600 005
                </div>

            </div>

        </div>

        <!-- NAVBAR -->
        <nav class="nav">

            <a class="active" href="index.php">Home</a>
            <a href="#">About Us</a>
            <a href="#">Contact Us</a>
            

        </nav>

    </div>

</header>

<div class="form-container">
<h2>DISTANCE EDUCATION REGISTRATION

</h2>

<form method="post">
<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
<div class="form-fields">

<label>Programme</label>
<select name="programme" onchange="loadCourses(this.value)" required>
    <option value="">-- Select Programme --</option>
    <option value="UG">Under Graduate</option>
    <option value="PG">Post Graduate</option>
    <option value="Diploma">Diploma</option>
    <option value="Certificate">Certificate</option>
</select>

<label>Course</label>
<select name="course" id="course" onchange="loadEligibility(this); validateForm();" required>
    <option value="">-- Select Course --</option>
</select>

<label>Name</label>
<input type="text" name="name" required>

<label>Mobile</label>
<input type="text" name="mobile" maxlength="10" pattern="[0-9]{10}" required>

<!-- EMAIL -->
<label>Email</label>
<input type="email" name="email" id="email" required oninput="validateForm()">

<label>Confirm Email</label>
<input type="email" name="confirm_email" id="confirm_email" required oninput="validateForm()">
<small id="emailHint" style="color:red;"></small>

<!-- PASSWORD -->
<label>Password</label>
<input type="password" name="password" id="password" required oninput="validateForm()">

<label>Confirm Password</label>
<input type="password" name="confirm_password" id="confirm_password" required oninput="validateForm()">
<small style="color:#555; display:block; margin-top:5px;">Note: Password must be at least 8 characters long, contain at least one uppercase letter (A-Z), one numeric value (0-9), and one special character (@, #, $, etc.).</small>
<small id="passwordHint" style="color:red; display:block; margin-top:5px;"></small>

<!-- DOB -->
<label>Date of Birth</label>
<input
    type="date"
    name="dob"
    id="dob"
    required
    max="<?php echo date('Y-m-d', strtotime('-17 years')); ?>"
    onchange="validateForm()"
>



<!-- ABC ID START -->
<label>Do you have an Academic Bank of Credit (ABC) ID? <span style="color:red;">*</span></label>
<div style="margin-bottom: 10px;">
    <label style="margin-right:15px; display:inline-block;">
        <input type="radio" name="abc_status" value="Yes" required onchange="toggleAbc(); validateForm();"> Yes
    </label>
    <label style="display:inline-block;">
        <input type="radio" name="abc_status" value="No" onchange="toggleAbc(); validateForm();"> No
    </label>
</div>

<div id="abcIdBox" style="display:none; margin-bottom:15px;">
    <label>ABC ID <span style="color:red;">*</span></label>
    <input type="text" name="abc_id" id="abc_id" maxlength="14" placeholder="XXXX XXXX XXXX" oninput="formatAbc(); validateForm();">
</div>

<div id="abcInstructions" class="note" style="display:none; margin-bottom:15px; color:#d9534f; background:#f9dede; border:1px solid #ebccd1; padding:10px; border-radius:5px;">
    <strong>Note:</strong> You must have an ABC ID to complete registration. 
    <br>Please refer to this <a href="https://youtu.be/Avpg8dIsL5Q?si=OBAtrheew-b2DaOM" target="_blank" style="color: #0275d8; text-decoration: underline;">YouTube video guide</a> to create and get your ABC ID.
</div>
<!-- ABC ID END -->

<!-- DEB ID START -->
<label>Do you have a Distance Education Bureau (DEB) ID? <span style="color:red;">*</span></label>
<div style="margin-bottom: 10px;">
    <label style="margin-right:15px; display:inline-block;">
        <input type="radio" name="deb_status" value="Yes" required onchange="toggleDeb(); validateForm();"> Yes
    </label>
    <label style="display:inline-block;">
        <input type="radio" name="deb_status" value="No" onchange="toggleDeb(); validateForm();"> No
    </label>
</div>

<div id="debIdBox" style="display:none; margin-bottom:15px;">
    <label>DEB ID <span style="color:red;">*</span></label>
    <input type="text" name="deb_id" id="deb_id" maxlength="14" placeholder="XXXX XXXX XXXX" oninput="formatDeb(); validateForm();">
</div>

<div id="debInstructions" class="note" style="display:none; margin-bottom:15px; color:#d9534f; background:#f9dede; border:1px solid #ebccd1; padding:10px; border-radius:5px;">
    <strong>Note:</strong> You must have a DEB ID to complete registration. 
    <br>Please refer to this <a href="https://youtu.be/Avpg8dIsL5Q?si=OBAtrheew-b2DaOM" target="_blank" style="color: #0275d8; text-decoration: underline;">YouTube video guide</a> to create and get your DEB ID.
</div>
<!-- DEB ID END -->

</div>

<div id="eligibilityBox" class="note">
   Please Check The Eligibility Criteria For Candidates Before Applying 
</div>

<input type="hidden" name="eligibility" id="eligibility">
<input type="hidden" name="course_name" id="course_name">

<div class="checkbox-group">

    <input type="checkbox" id="agree">

    <label for="agree">
        All Details Are Authorized
    </label>

</div>

<button type="submit" id="nextBtn" disabled>Next</button>
</form>
</div>


<script src="script.js"></script>

<script>
function validateForm() {
    const dob = document.getElementById("dob").value;
    const pwd = document.getElementById("password").value;
    const cpw = document.getElementById("confirm_password").value;
    const email = document.getElementById("email").value;
    const cemail = document.getElementById("confirm_email").value;
    const course = document.getElementById("course").value;

    const hint = document.getElementById("passwordHint");
    const emailHint = document.getElementById("emailHint");

    let valid = true;

    /* AGE CHECK */
    if (dob) {
        const d = new Date(dob);
        const t = new Date();
        let age = t.getFullYear() - d.getFullYear();
        const m = t.getMonth() - d.getMonth();
        if (m < 0 || (m === 0 && t.getDate() < d.getDate())) age--;
        if (age < 17) valid = false;
    } else valid = false;

    /* EMAIL CHECK */
    if (email !== cemail) {
        emailHint.innerText = "Emails do not match.";
        valid = false;
    } else {
        emailHint.innerText = "";
    }

    /* PASSWORD CHECK */
    const regex = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/;
    if (!regex.test(pwd)) {
        hint.innerText = "Password must be 8+ chars with upper, lower, number & symbol.";
        valid = false;
    } else if (pwd !== cpw) {
        hint.innerText = "Passwords do not match.";
        valid = false;
    } else {
        hint.innerText = "";
    }

    if (!course) valid = false;

    /* ABC ID CHECK */
    const abcStatus = document.querySelector('input[name="abc_status"]:checked')?.value;
    const abcIdInput = document.getElementById("abc_id");
    if (abcStatus === "Yes") {
        if (abcIdInput && abcIdInput.value.replace(/\s+/g, "").length !== 12) {
            valid = false;
        }
    } else {
        // If they select "No" or haven't selected anything, they cannot proceed.
        valid = false;
    }

    /* DEB ID CHECK */
    const debStatus = document.querySelector('input[name="deb_status"]:checked')?.value;
    const debIdInput = document.getElementById("deb_id");
    if (debStatus === "Yes") {
        if (debIdInput && debIdInput.value.replace(/\s+/g, "").length !== 12) {
            valid = false;
        }
    } else {
        // If they select "No" or haven't selected anything, they cannot proceed.
        valid = false;
    }

    document.getElementById("nextBtn").disabled = !valid;
}

function toggleAbc() {
    const abcStatus = document.querySelector('input[name="abc_status"]:checked')?.value;
    const abcIdBox = document.getElementById("abcIdBox");
    const abcInstructions = document.getElementById("abcInstructions");
    const abcId = document.getElementById("abc_id");

    if (abcStatus === "Yes") {
        abcIdBox.style.display = "block";
        abcInstructions.style.display = "none";
        abcId.required = true;
    } else if (abcStatus === "No") {
        abcIdBox.style.display = "none";
        abcInstructions.style.display = "block";
        abcId.required = false;
        abcId.value = "";
    } else {
        abcIdBox.style.display = "none";
        abcInstructions.style.display = "none";
        abcId.required = false;
    }
}

function formatAbc() {
    const input = document.getElementById("abc_id");
    let digits = input.value.replace(/\D/g, "");
    digits = digits.substring(0, 12);
    digits = digits.replace(/(.{4})/g, "$1 ").trim();
    input.value = digits;
}

function toggleDeb() {
    const debStatus = document.querySelector('input[name="deb_status"]:checked')?.value;
    const debIdBox = document.getElementById("debIdBox");
    const debInstructions = document.getElementById("debInstructions");
    const debId = document.getElementById("deb_id");

    if (debStatus === "Yes") {
        debIdBox.style.display = "block";
        debInstructions.style.display = "none";
        debId.required = true;
    } else if (debStatus === "No") {
        debIdBox.style.display = "none";
        debInstructions.style.display = "block";
        debId.required = false;
        debId.value = "";
    } else {
        debIdBox.style.display = "none";
        debInstructions.style.display = "none";
        debId.required = false;
    }
}

function formatDeb() {
    const input = document.getElementById("deb_id");
    let digits = input.value.replace(/\D/g, "");
    digits = digits.substring(0, 12);
    digits = digits.replace(/(.{4})/g, "$1 ").trim();
    input.value = digits;
}

window.onload = function () {
    loadCourses('UG');
};
</script>

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

<p>© 2025 University of Madras. All Rights Reserved.</p>

</div>

</footer>

</body>
</html>