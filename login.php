<?php
session_start();
include "db.php";

// Generate CSRF token if it does not exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF verification failed.");
    }

    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare(
        "SELECT id, password 
         FROM users 
         WHERE email=? AND is_verified=1"
    );
    $stmt->execute([$email]);

    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        if (password_verify($password, $row['password'])) {

            $_SESSION['student_id'] = $row['id'];
            $_SESSION['student_email'] = $email;

            // Check if application is already submitted
            $check = $pdo->prepare("SELECT application_no FROM records WHERE email = ? LIMIT 1");
            $check->execute([$email]);
            if ($record = $check->fetch()) {
                $error = "Application Already Submitted. Login Expired.";
                // Clear session to prevent login
                session_destroy();
            } else {
                // Always reset form state on login so the form starts at Step 1
                unset($_SESSION['current_step']);
                unset($_SESSION['step1_data']);
                unset($_SESSION['step2_data']);
                unset($_SESSION['application_no']);

                header("Location: admission-form/ap1.php");
                exit;
            }



        } else {
            $error = "Invalid email or password";
        }

    } else {
        $error = "Account not found or not verified";
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
    font-family: Arial, Helvetica, sans-serif;
}

/* HEADER */
.top-header{
    width:100%;
    background:rgb(216, 230, 220);
    border-top:7px solid #0b5fa5;
    box-shadow:0 2px 10px rgba(0,0,0,0.08);
   
}

.container{
    width:100%;
    
    max-width:1400px;
    margin:auto;
}

/* =========================
   HEADER TOP
========================= */
/* =========================
   HEADER TOP
========================= */
.header-top{
    position:relative;

    display:flex;
    align-items:center;
    justify-content:center;

    padding:20px 0;
}

/* =========================
   LEFT LOGO
========================= */
.logo-section{
    position:absolute;
    left:20px;
    top:50%;
    transform:translateY(-50%);
    
}

.logo-section img{
    width:230px;
    height:auto;
    object-fit:contain;

}

/* =========================
   TITLE SECTION
========================= */
.title-section{
    text-align:center;
}

/* TAMIL TEXT */
.tamil-text{
    color:#0b5fa5;

    font-size:18px;
    font-weight:700;

    line-height:1.5;

    margin-bottom:5px;

    /* Similar Tamil font style */
    font-family:"Latha","Nirmala UI",sans-serif;
}

/* ENGLISH TEXT */
.english-text{
    color:#083c72;

    font-size:20px;
    font-weight:800;

    line-height:1.3;

    /* Similar college style font */
    font-family:Georgia, "Times New Roman", serif;
}

/* SUB TEXT */
.sub-text{
    margin-top:8px;

    color:#444;

    font-size:12px;
    font-weight:600;

    line-height:1.6;
}

/* =========================
   NAVBAR
========================= */
.nav{
    width:100%;
    background:#0b5fa5;

    display:flex;
    justify-content:center;
    align-items:center;
    flex-wrap:wrap;

    gap:28px;

    padding:5px 5px;
}

.nav a{
    text-decoration:none;
    color:#ffffff;

    padding:7px 18px;

    font-size:15px;
    font-weight:600;

    border-radius:5px;

    transition:0.3s ease;
}

.nav a:hover,
.nav a.active{
    background:rgba(32, 223, 79, 0.15);
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
}

.login-content h3 span {
    color: red;
    font-weight: bold;
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

    transition:0.3s ease;
}

.login-table input:focus{
    border-color:#0b5fa5;
    box-shadow:0 0 5px rgba(11,95,165,0.2);
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
    color:#0b5fa5;
    text-decoration:none;
    font-weight:700;
}

.login-links a:hover{
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
    background:#075d9f;
    color:#ffffff;
    text-align:center;
    padding:20px;
    /* Similar college style font */
    font-family:Georgia, "Times New Roman", serif;
}

.about-ide{
    max-width:1100px;
    margin:auto;
}

.about-ide p{
    line-height:1.8;
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
            <a href="#">Contact Us</a>
            

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

                <button type="submit">Check</button>
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