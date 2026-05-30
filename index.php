<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| PRODUCTION SECURITY SETTINGS
|--------------------------------------------------------------------------
| These settings help secure the application for production hosting.
| Keep display_errors OFF in live servers.
*/

ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

/*
|--------------------------------------------------------------------------
| SECURE SESSION CONFIGURATION
|--------------------------------------------------------------------------
| Protects against session hijacking and fixation attacks.
*/

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']), // HTTPS only in production
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();

/*
|--------------------------------------------------------------------------
| SECURITY HEADERS
|--------------------------------------------------------------------------
| Prevents XSS, Clickjacking, MIME attacks, etc.
*/

header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
header("X-XSS-Protection: 1; mode=block");

/*
|--------------------------------------------------------------------------
| CONTENT SECURITY POLICY (CSP)
|--------------------------------------------------------------------------
| Helps block malicious scripts.
| Adjust if external CDN/scripts are added later.
*/

header("
Content-Security-Policy:
default-src 'self';
img-src 'self' data:;
style-src 'self' 'unsafe-inline';
script-src 'self' 'unsafe-inline';
font-src 'self';
");

/*
|--------------------------------------------------------------------------
| CSRF TOKEN GENERATION
|--------------------------------------------------------------------------
| Future-ready CSRF protection.
*/

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/*
|--------------------------------------------------------------------------
| SAFE RESPONSE VARIABLE
|--------------------------------------------------------------------------
*/

$response = "";
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>University of Madras - IDE</title>

<link rel="stylesheet" href="stylee.css">

<style>

/* KEEPING ORIGINAL UI EXACTLY SAME */

.chatbot-box{
position:fixed;
bottom:20px;
right:20px;
width:300px;
background:#ffffff;
border:1px solid #ccc;
border-radius:10px;
box-shadow:0 0 10px rgba(0,0,0,0.2);
font-family:Arial;
}

/* Remaining CSS unchanged */

</style>

</head>

<body>

<!-- RULES POPUP -->
    
<div id="rulesPopup">

<div class="rules-box">

<p style="font-size:16px; font-weight:bold; color:#d9534f; text-align:center;">
⚠ Please read the instructions carefully before clicking "Read & Continue".
</p>

<div class="rules-body">

<b> APPLICATION FORMALITIES</b>

<p>1. Applicants must provide correct personal details.</p>
<p>2. Upload valid and clear documents only.</p>
<p>3. Incomplete applications will not be processed.</p>
<p>4. Incomplete or incorrect applications will be rejected without notice.</p>
<p>5. Applicants must ensure that the details entered match their official documents.</p>
<p>6. The institution reserves the right to verify all submitted documents.</p>
<p>7. Applicants must upload recent passport-size photographs.</p>
<p>8. All communication will be done through the registered email or phone number.</p>

<b>REGISTRATION AND LOGIN INSTRUCTIONS</b>

<p>1. Applicants must first complete the registration process before accessing the application form.</p>
<p>2. During registration, provide a valid email address and create a secure password.</p>

<b>APPLICATION INSTRUCTIONS</b>

<p>1. Read all instructions carefully before filling out the application form.</p>
<p>2. Ensure you meet the eligibility criteria for the course before applying.</p>
<p>3. Fill in all mandatory fields marked in the application form.</p>

</div>

<div class="rules-footer">

<label class="agree">

<input
    type="checkbox"
    id="agreeCheck"
>

I have read the instructions carefully

</label>

<button
    id="continueBtn"
    disabled
>
Read & Continue
</button>

</div>

</div>

</div>

<!-- HEADER -->

<header class="top-header">

    <div class="container">

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

        <div class="menu-toggle" id="menuToggle">☰</div>

        <div class="nav-links" id="navLinks">

            <a href="index.php">Home</a>
            <a href="#">About Us</a>
            <a href="#">Contact Us</a>
            <a href="admin/login.php">Admin Panel</a>
            <a href="lsc/login.php">LSC Login</a>
            <a href="singlewindow/index.php">S-W-L</a>

        </div>

    </div>

</nav>


</div>

</header>

<!-- BANNER -->

<section class="banner">

<div class="white-wrapper">

<div class="portal">

<h2>Institute of Distance Education</h2>

<h3>Online Application Portal</h3>

<div class="buttons">

<a href="login.php" class="btn btn-outline">
Login
</a>

<a href="form.php" class="btn btn-outline">
Register Here
</a>

</div>

<div class="notice-box">

<h4>📢 Important Notices</h4>

<div class="scroll-notice">

<ul class="notice-list">

<li>Admissions open for Academic Year 2025–2026</li>

<li>
Last date for UG applications:
<strong>30 June 2025</strong>
</li>

<li>
Online payment facility available for all programmes
</li>

<li>
Hall ticket download notification will be announced shortly
</li>

</ul>

</div>

</div>

</div>

</div>

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

<!-- JAVASCRIPT -->

<script>

/*
|--------------------------------------------------------------------------
| RULES POPUP CONTROL
|--------------------------------------------------------------------------
*/

const checkbox = document.getElementById("agreeCheck");
const button = document.getElementById("continueBtn");

checkbox.addEventListener("change", function () {

    const isChecked = this.checked;

    button.disabled = !isChecked;
    button.style.opacity = isChecked ? "1" : "0.5";
    button.style.cursor = isChecked ? "pointer" : "not-allowed";

});

button.addEventListener("click", function () {

    if (checkbox.checked) {

        const popup = document.getElementById("rulesPopup");

        if (popup) {
            popup.style.display = "none";
        }
    }
});

/*
|--------------------------------------------------------------------------
| SCROLL TO TOP
|--------------------------------------------------------------------------
*/

function scrollToTop(){

    window.scrollTo({
        top:0,
        behavior:"smooth"
    });

}

/*
|--------------------------------------------------------------------------
| MOBILE MENU TOGGLE
|--------------------------------------------------------------------------
*/

const menuToggle = document.getElementById("menuToggle");
const navLinks = document.getElementById("navLinks");

if(menuToggle && navLinks){

    menuToggle.addEventListener("click", () => {
        navLinks.classList.toggle("show");
    });

}

</script>

<!-- TOP BUTTON -->

<button
    id="topBtn"
    onclick="scrollToTop()"
>
↑
</button>

</body>
</html>