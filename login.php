<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| SECURE PHP SETTINGS
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
| DATABASE CONNECTION
|--------------------------------------------------------------------------
*/

require_once "db.php";

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
| RATE LIMITING / BRUTE FORCE PROTECTION
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt'] = time();
}

$maxAttempts = 5;
$lockTime = 300; // 5 minutes

if (
    $_SESSION['login_attempts'] >= $maxAttempts &&
    (time() - $_SESSION['last_attempt']) < $lockTime
) {
    die("Too many login attempts. Please try again later.");
}

$error = "";

/*
|--------------------------------------------------------------------------
| LOGIN PROCESS
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
    | INPUT VALIDATION
    |--------------------------------------------------------------------------
    */

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (
        empty($email) ||
        empty($password) ||
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {

        $error = "Invalid email or password.";

    } else {

        try {

            /*
            |--------------------------------------------------------------------------
            | FETCH USER
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                SELECT id, password
                FROM users
                WHERE email = :email
                AND is_verified = 1
                LIMIT 1
            ");

            $stmt->bindParam(':email', $email, PDO::PARAM_STR);

            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            /*
            |--------------------------------------------------------------------------
            | VERIFY PASSWORD
            |--------------------------------------------------------------------------
            */

            if ($row && password_verify($password, $row['password'])) {

                /*
                |--------------------------------------------------------------------------
                | REGENERATE SESSION ID
                |--------------------------------------------------------------------------
                */

                session_regenerate_id(true);

                /*
                |--------------------------------------------------------------------------
                | STORE SESSION DATA
                |--------------------------------------------------------------------------
                */

                $_SESSION['student_id'] = (int)$row['id'];
                $_SESSION['student_email'] = $email;

                /*
                |--------------------------------------------------------------------------
                | RESET LOGIN ATTEMPTS
                |--------------------------------------------------------------------------
                */

                $_SESSION['login_attempts'] = 0;

                /*
                |--------------------------------------------------------------------------
                | CHECK EXISTING APPLICATION
                |--------------------------------------------------------------------------
                */

                $check = $pdo->prepare("
                    SELECT application_no
                    FROM records
                    WHERE email = :email
                    LIMIT 1
                ");

                $check->bindParam(':email', $email, PDO::PARAM_STR);

                $check->execute();

                $record = $check->fetch(PDO::FETCH_ASSOC);

                if ($record) {

                    /*
                    |--------------------------------------------------------------------------
                    | FULL SESSION DESTROY
                    |--------------------------------------------------------------------------
                    */

                    $_SESSION = [];

                    if (ini_get("session.use_cookies")) {

                        $params = session_get_cookie_params();

                        setcookie(
                            session_name(),
                            '',
                            time() - 42000,
                            $params["path"],
                            $params["domain"],
                            $params["secure"],
                            $params["httponly"]
                        );
                    }

                    session_destroy();

                    $error = "Application already submitted.";

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | RESET FORM SESSION DATA
                    |--------------------------------------------------------------------------
                    */

                    unset(
                        $_SESSION['current_step'],
                        $_SESSION['step1_data'],
                        $_SESSION['step2_data'],
                        $_SESSION['application_no']
                    );

                    header("Location: admission-form/ap1.php");
                    exit;
                }

            } else {

                /*
                |--------------------------------------------------------------------------
                | FAILED LOGIN
                |--------------------------------------------------------------------------
                */

                $_SESSION['login_attempts']++;
                $_SESSION['last_attempt'] = time();

                $error = "Invalid email or password.";
            }

        } catch (PDOException $e) {

            /*
            |--------------------------------------------------------------------------
            | SECURE ERROR HANDLING
            |--------------------------------------------------------------------------
            */

            error_log($e->getMessage());

            $error = "System temporarily unavailable. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Login | University of Madras</title>

<style>
/* RESET */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Times New Roman", Times, serif; 
}

:root{
    --primary:#0b5fa5;
    --primary-dark:#083c72;
    --shadow:0 4px 15px rgba(0,0,0,.12);
}

body{
    font-family:"Times New Roman", Times, serif;
}


/* =========================
   HEADER
========================= */
.top-header{
    width:100%;
    background:rgb(216,230,220);
    border-top:7px solid var(--primary);
    box-shadow:var(--shadow);
}

.container{
    width:100%;
    max-width:1400px;
    margin:auto;
    padding:0 15px;
}

/* =========================
   HEADER TOP
========================= */
.header-top{
    position:relative;

    display:flex;
    align-items:center;
    justify-content:center;

    min-height:180px;
    padding:20px;
    font-family: "Times New Roman", Times, serif;
    text-align:center;
}
/* =========================
   LOGO
========================= */
.logo-section{
    position:absolute;
    left:20px;
    top:50%;
    transform:translateY(-50%);
}

.logo-section img{
    width:220px;
    height:auto;
}



/* =========================
   TITLE
========================= */
.title-section{
    flex:1;
}

.tamil-text{
    color:var(--primary);
    font-size:clamp(13px,2vw,22px);
    font-weight:700;
    line-height:1.5;
    font-family: "Times New Roman", Times, serif;
}

.english-text{
    color:var(--primary-dark);
    font-size:clamp(18px,3vw,32px);
    font-weight:800;
    line-height:1.3;
    font-family: "Times New Roman", Times, serif;
}

.sub-text{
    margin-top:8px;
    color:#444;
    font-size:clamp(11px,1.5vw,14px);
    font-weight:600;
    line-height:1.6;
    font-family: "Times New Roman", Times, serif;
    
}

/* =========================
   NAVBAR
========================= */

.navbar{
    width:100%;
    background:#005ea6;
    position:sticky;
    top:0;
    left:0;
    z-index:1000;
    margin:0;
    padding:0;
}
.nav-container{
    width:100%;
    display:flex;
    justify-content:center;
    align-items:center;
}
/* NAV LINKS */

.nav-links{
    display:flex;
    align-items:center;
    justify-content:center;
    gap:35px;
    
    padding:18px 0;

    flex-wrap:wrap;
}

.nav-links a:not(:last-child)::after{
    content:"|";
    margin-left:18px;
    color:rgba(255,255,255,.5);
}


.nav-links a{
    color: #ffffff;
    text-decoration:none;
    font-family: "Times New Roman", Times, serif;
    font-size:16px;
    font-weight:600;

    transition:0.3s ease;
}

.nav-links a:hover{
    color:#d6ecff;
}

/* MENU BUTTON */

.menu-toggle{
    display:none;

    font-size:32px;
    color:#ffffff;
    font-family: "Times New Roman", Times, serif;
    cursor:pointer;
}

@media(max-width:768px){

.header-top{
    flex-direction:column;
    min-height:auto;
    padding:20px 10px;
}

.logo-section{
    position:static;
    transform:none;
    margin-bottom:10px;
}

.logo-section img{
    width:75px;
}

}




/* BANNER */
.banner {
    min-height: 100vh;
    background: url("image/back.jpeg") center/cover no-repeat;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* LOGIN BOX */
/* =========================
   LOGIN CARD
========================= */
.white-wrapper{
    width:100%;
    max-width:480px;

    background: #caf5db;

    border-radius:18px;

    overflow:hidden;

    box-shadow:
    0 10px 30px rgba(0,0,0,0.18);

    animation:fadeUp 0.5s ease;
}

/* CARD ANIMATION */
@keyframes fadeUp{

    from{
        opacity:0;
        transform:translateY(20px);
    }

    to{
        opacity:1;
        transform:translateY(0);
    }

}

/* TITLE BAR */
/* =========================
   LOGIN TITLE BAR
========================= */
.uni-title{
    background:
    linear-gradient(
    135deg,
    #0b5fa5,
    #1d8ae7
    );

    color: #ffffff;

    text-align:center;

    font-size:24px;
    font-weight:700;

    padding:16px;
    font-family: "Times New Roman", Times, serif;
    letter-spacing:1px;
}

/* CONTENT */
/* LOGIN CONTENT */
.login-content{
    padding:35px 30px;
    text-align:center;
    background: #e2ece1;
}

.login-content img{
    width:95px;
    margin-bottom:1px;
}

.login-content h3{
    font-size:20px;
    color:#083c72;
    margin-bottom:25px;
    line-height:1.6;
    font-family: "Times New Roman", Times, serif;
}

.login-content h3 span {
    color: red;
    font-weight: bold;
    font-family: "Times New Roman", Times, serif;
}

/* TABLE */
/* =========================
   LOGIN TABLE
========================= */
.login-table{
    width:100%;
    border-collapse:collapse;
    margin-bottom:20px;
}

.login-table td{
    padding:10px;
    border:none;
    font-family: "Times New Roman", Times, serif;
    text-align:left;
    font-size:15px;
    font-weight:600;
    color:#333;
}

/* INPUTS */
.login-table input{
    width:100%;

    padding:12px;

    border:1px solid #cfd8dc;
    border-radius:20px;

    outline:none;

    font-size:15px;
    font-family: "Times New Roman", Times, serif;
    transition:0.3s ease;
}

.login-table input:focus{
    border-color:#0b5fa5;
    box-shadow:0 0 5px rgba(11,95,165,0.2);
    font-family: "Times New Roman", Times, serif;
}

/* BUTTON */
/* =========================
   BUTTON
========================= */
button{
    width:250px;

    padding:12px;

    border:none;
    border-radius:25px;

    background:
    linear-gradient(
    135deg,
    #0b5fa5,
    #1d8ae7
    );

    color: #ffffff;

    font-size:16px;
    font-weight:700;

    cursor:pointer;
    font-family: "Times New Roman", Times, serif;
    transition:0.3s ease;
}

button:hover{
    transform:translateY(-2px);

    box-shadow:
    0 6px 15px rgba(0,0,0,0.2);
}

/* ERROR */
.error {
    background: #ffe5e5;
    color: #c62828;
    padding: 8px;
    border-radius: 4px;
    margin-bottom: 10px;
}

/* SHOW PASSWORD */
.show-pass {
    text-align: left;
    margin-bottom: 10px;
    font-size: 14px;
    font-family: "Times New Roman", Times, serif;
}

/* LINKS */
/* =========================
   LOGIN LINKS
========================= */
.login-links{
    margin-top:20px;
    font-size:14px;
    line-height:2;
}

.login-links a{
    font-family: "Times New Roman", Times, serif;
    color:#0b5fa5;
    text-decoration:none;
    font-weight:700;
}

.login-links a:hover{
    font-family: "Times New Roman", Times, serif;
    text-decoration:underline;
}

/* FOOTER */
/* =========================
   FOOTER
========================= */
/* =========================
   FOOTER
========================= */
footer{
     background: #005ea6;
    color: #ffffff;
    text-align:center;
    padding:25px 15px;
    font-family: "Times New Roman", Times, serif;
}

.about-ide{
    max-width:1100px;
    margin:auto;
}

.about-ide p{
    line-height:1.8;
    font-family: "Times New Roman", Times, serif;
    font-size:clamp(14px,1.5vw,16px);
}

/* =========================
   LARGE LAPTOP
========================= */
@media(max-width:1200px){

.logo-section img{
    width:120px;
}

.english-text{
    font-size:22px;
}

}

/* =========================
   TABLET
========================= */
@media(max-width:900px){

.header-top{
    flex-direction:column;
    text-align:center;
}

.logo-section{
    position:static;
    transform:none;
    margin-bottom:10px;
}

.logo-section img{
    width:90px;
}

.tamil-text{
    font-size:15px;
}

.english-text{
    font-size:20px;
}

.sub-text{
    font-size:12px;
    padding:0 10px;
}

.nav{
    gap:10px;
    padding:10px;
}

.nav a{
    font-size:14px;
    padding:8px 12px;
}

.white-wrapper{
    width:92%;
}

}

/* =========================
   MOBILE
========================= */
@media(max-width:600px){

.logo-section img{
    width:70px;
}

.tamil-text{
    font-size:12px;
}

.english-text{
    font-size:16px;
    line-height:1.5;
}

.sub-text{
    font-size:11px;
    line-height:1.5;
}

.nav{
    gap:5px;
}

.nav a{
    font-size:12px;
    padding:7px 10px;
}

.login-content{
    padding:25px 15px;
}

.login-content h3{
    font-size:18px;
}

.login-table td{
    display:block;
    width:100%;
    padding:5px 0;
}

button{
    max-width:100%;
}

}

</style>
</head>

<body>


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


<div class="banner">
    <div class="white-wrapper">

        <div class="uni-title"> UNIVERSITY OF MADRAS</div>

        <div class="login-content">

            <img src="image/Univ.png">

            <h3>
                Institute of Distance Education
                <span></span><br>
                University Departments
            </h3>

            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="post">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                <table class="login-table">
                    <tr>
                        <td>Email :</td>
                        <td><input type="email" name="email" required></td>
                    </tr>
                    <tr>
                        <td>Password :</td>
                        <td><input type="password" name="password" id="password" required></td>
                    </tr>
                </table>

                <div class="show-pass">
                    <input type="checkbox" onclick="togglePassword()"> Show Password
                </div>

                <button type="submit">Login</button>
            </form>

            <!-- 🔹 NEW OPTIONS ADDED -->
            <div class="login-links">
                <p><a href="forgot_password.php">Forgot Password?</a></p>
                <p>New Applicant? <a href="form.php">Register Here</a></p>
            </div>

        </div>
    </div>
</div>

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

<script>
function togglePassword() {
    const pwd = document.getElementById("password");
    pwd.type = pwd.type === "password" ? "text" : "password";
}
</script>

</body>
</html>