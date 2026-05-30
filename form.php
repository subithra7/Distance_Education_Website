<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| SECURE ERROR SETTINGS
|--------------------------------------------------------------------------
*/

ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

/*
|--------------------------------------------------------------------------
| SECURE SESSION SETTINGS
|--------------------------------------------------------------------------
*/

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();

/*
|--------------------------------------------------------------------------
| SECURITY HEADERS
|--------------------------------------------------------------------------
*/

header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("X-XSS-Protection: 1; mode=block");

header("
Content-Security-Policy:
default-src 'self';
style-src 'self' 'unsafe-inline';
script-src 'self' 'unsafe-inline';
img-src 'self' data:;
");

/*
|--------------------------------------------------------------------------
| CSRF TOKEN
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/*
|--------------------------------------------------------------------------
| RATE LIMITING
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['register_attempt'])) {
    $_SESSION['register_attempt'] = time();
    $_SESSION['register_count'] = 0;
}

if (
    $_SESSION['register_count'] >= 10 &&
    (time() - $_SESSION['register_attempt']) < 600
) {
    die("Too many attempts. Please try again later.");
}

$error = "";

/*
|--------------------------------------------------------------------------
| FORM SUBMISSION
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    /*
    |--------------------------------------------------------------------------
    | CSRF VALIDATION
    |--------------------------------------------------------------------------
    */

    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        die("Invalid request.");
    }

    /*
    |--------------------------------------------------------------------------
    | INPUT SANITIZATION
    |--------------------------------------------------------------------------
    */

    $programme = trim($_POST['programme'] ?? '');

    $name = trim($_POST['name'] ?? '');

    $mobile = trim($_POST['mobile'] ?? '');

    $email = trim($_POST['email'] ?? '');

    $confirmEmail = trim($_POST['confirm_email'] ?? '');

    $password = $_POST['password'] ?? '';

    $confirm = $_POST['confirm_password'] ?? '';

    $dob = trim($_POST['dob'] ?? '');

    $courseId = trim($_POST['course'] ?? '');

    $courseName = trim($_POST['course_name'] ?? '');

    $eligibility = trim($_POST['eligibility'] ?? '');

    $abcStatus = trim($_POST['abc_status'] ?? '');

    $abcId = trim($_POST['abc_id'] ?? '');

    $debStatus = trim($_POST['deb_status'] ?? '');

    $debId = trim($_POST['deb_id'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | NAME VALIDATION
    |--------------------------------------------------------------------------
    */

    if (
        empty($name) ||
        strlen($name) > 100 ||
        !preg_match('/^[a-zA-Z\s\.]+$/u', $name)
    ) {

        $error = "Invalid name.";
    }

    /*
    |--------------------------------------------------------------------------
    | MOBILE VALIDATION
    |--------------------------------------------------------------------------
    */

    elseif (!preg_match('/^[0-9]{10}$/', $mobile)) {

        $error = "Invalid mobile number.";
    }

    /*
    |--------------------------------------------------------------------------
    | EMAIL VALIDATION
    |--------------------------------------------------------------------------
    */

    elseif (
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        $email !== $confirmEmail
    ) {

        $error = "Invalid email.";
    }

    /*
    |--------------------------------------------------------------------------
    | PASSWORD VALIDATION
    |--------------------------------------------------------------------------
    */

    elseif ($password !== $confirm) {

        $error = "Passwords do not match.";
    }

    elseif (
        !preg_match(
            '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/',
            $password
        )
    ) {

        $error = "Weak password.";
    }

    /*
    |--------------------------------------------------------------------------
    | AGE VALIDATION
    |--------------------------------------------------------------------------
    */

    else {

        try {

            $dobDate = new DateTime($dob);

            $today = new DateTime();

            $age = $today->diff($dobDate)->y;

            if ($age < 17) {

                $error = "Applicant must be at least 17 years old.";
            }

        } catch (Exception $e) {

            $error = "Invalid date of birth.";
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ABC VALIDATION
    |--------------------------------------------------------------------------
    */

    if (!$error && $abcStatus !== "Yes") {

        $error = "ABC ID required.";
    }

    if (
        !$error &&
        !preg_match('/^[0-9]{12}$/', str_replace(' ', '', $abcId))
    ) {

        $error = "Invalid ABC ID.";
    }

    /*
    |--------------------------------------------------------------------------
    | DEB VALIDATION
    |--------------------------------------------------------------------------
    */

    if (!$error && $debStatus !== "Yes") {

        $error = "DEB ID required.";
    }

    if (
        !$error &&
        !preg_match('/^[0-9]{12}$/', str_replace(' ', '', $debId))
    ) {

        $error = "Invalid DEB ID.";
    }

    /*
    |--------------------------------------------------------------------------
    | STORE SESSION DATA
    |--------------------------------------------------------------------------
    */

    if (!$error) {

        session_regenerate_id(true);

        $hashedPassword = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $_SESSION['form_data'] = [

            'programme' => htmlspecialchars($programme, ENT_QUOTES, 'UTF-8'),

            'name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),

            'mobile' => $mobile,

            'email' => $email,

            'password' => $hashedPassword,

            'dob' => $dob,

            'course_id' => htmlspecialchars($courseId, ENT_QUOTES, 'UTF-8'),

            'course_name' => htmlspecialchars($courseName, ENT_QUOTES, 'UTF-8'),

            'eligibility' => htmlspecialchars($eligibility, ENT_QUOTES, 'UTF-8'),

            'abc_status' => $abcStatus,

            'abc_id' => preg_replace('/\s+/', '', $abcId),

            'deb_status' => $debStatus,

            'deb_id' => preg_replace('/\s+/', '', $debId)
        ];

        $_SESSION['register_count']++;

        header("Location: next.php");
        exit;
    }
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

<div class="container">

    <div class="header-top">
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

</header>

<nav class="navbar">
    <div class="nav-container">
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="#">About</a>
            <a href="#">Contact Us</a>
        </div>
    </div>
</nav>


<div class="form-container">
<h2>DISTANCE EDUCATION REGISTRATION

</h2>

<?php if (!empty($error)): ?>

<div class="error">

<?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>

</div>

<?php endif; ?>

<form method="post">
<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
<div class="form-fields">

<label>Programme<span style="color:red;">*</span></label>
<select name="programme" onchange="loadCourses(this.value)" required>
    <option value="">-- Select Programme --</option>
    <option value="UG">Under Graduate</option>
    <option value="PG">Post Graduate</option>
    <option value="Diploma">Diploma</option>
    <option value="Certificate">Certificate</option>
</select>

<label>Course<span style="color:red;">*</span></label>
<select name="course" id="course" onchange="loadEligibility(this); validateForm();" required>
    <option value="">-- Select Course --</option>
</select>

<label>Name<span class="required">*</span></label>
<input type="name" name="name" required>

<label>Mobile<span style="color:red;">*</span></label>
<input type="text" name="mobile" maxlength="10" pattern="[0-9]{10}" required>

<!-- EMAIL -->
<label>Email<span style="color:red;">*</span></label>
<input type="email" name="email" id="email" required oninput="validateForm()">

<label>Confirm Email<span style="color:red;">*</span></label>
<input type="email" name="confirm_email" id="confirm_email" required oninput="validateForm()">
<small id="emailHint" style="color:red;"></small>

<!-- PASSWORD -->
<label>Password<span style="color:red;">*</span></label>
<input type="password" name="password" id="password" required oninput="validateForm()">

<label>Confirm Password<span style="color:red;">*</span></label>
<input type="password" name="confirm_password" id="confirm_password" required oninput="validateForm()">
<small style="color:#555; display:block; margin-top:5px;">Note: Password must be at least 8 characters long, contain at least one uppercase letter (A-Z), one numeric value (0-9), and one special character (@, #, $, etc.).</small>
<small id="passwordHint" style="color:red; display:block; margin-top:5px;"></small>

<!-- DOB -->
<label>Date of Birth<span style="color:red;">*</span></label>
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