<?php
session_start();
include "../db.php";

$error = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if(empty($username) || empty($password)){
        $error = "Please enter username and password.";
    } else {

        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username=?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if($admin && password_verify($password, $admin['password'])){
            $_SESSION['admin'] = $admin['username'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid login credentials.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>University Admin Login</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>

/* RESET */
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:"Segoe UI", Arial, sans-serif;
}

/* BODY */
body{
    min-height:100vh;
    margin:0;

    background-image: url("../image/back.jpeg");
    background-size: cover;
    background-position: center;

    position: relative;
}

/* HEADER */
.top-header{
    width:100%;

    background:#e8efe9;

    border-top:5px solid #0b5fa5;

    box-shadow:
    0 2px 10px rgba(0,0,0,0.08);
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

    gap:25px;

    padding:20px 20px 15px;

    flex-wrap:wrap;
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

    font-size:20px;
    font-weight:700;

    line-height:1.5;

    margin-bottom:5px;

    font-family:
    "Latha",
    "Nirmala UI",
    sans-serif;
}

/* ENGLISH TEXT */
.english-text{
    color:#083c72;

    font-size:22px;
    font-weight:800;

    line-height:1.4;

    margin-bottom:5px;

    font-family:
    Georgia,
    "Times New Roman",
    serif;
}

/* SUB TEXT */
.sub-text{
    color:#333;

    font-size:13px;
    font-weight:600;

    line-height:1.7;
}

.form-fields{
    width:100%;
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

    gap:14px;

    padding:10px;
}

.nav a{
    text-decoration:none;

    color:#ffffff;

    padding:8px 15px;

    font-size:14px;
    font-weight:700;

    border-radius:4px;

    transition:0.3s ease;
}

.nav a:hover,
.nav a.active{
    background:
    rgba(255,255,255,0.15);
}

/* LOGIN WRAPPER */
.login-wrapper{
    display:flex;

    justify-content:center;
    align-items:center;

    padding:60px 15px;
}

/* LOGIN BOX */
.login-box{
    background:white;
    width:380px;
    padding:40px;
    border-radius:12px;
    box-shadow:0 15px 35px rgba(235, 240, 245, 0.3);
    text-align:center;
    animation:fadeIn 0.5s ease-in-out;
}

.login-box h2{
    margin-bottom:10px;
    /* Similar college style font */
    font-family:Georgia, "Times New Roman", serif;
    font-size:21px;color: #0b5fa5;
    
}

.login-note{
    margin-bottom:10px;
    /* Similar college style font */
    font-family:Georgia, "Times New Roman", serif;
    font-size:12px;

}

/* INPUTS */
.login-box input{
    width:100%;
    padding:12px;
    margin-bottom:18px;
    border:1px solid #d1d5db;
    border-radius:8px;
    font-size:14px;
    transition:0.3s;
}

.login-box input:focus{
    border-color:#2563eb;
    outline:none;
    box-shadow:0 0 0 3px rgba(37,99,235,0.15);
}

/* BUTTON */
.login-box button{
    width:100%;
    padding:12px;
    background:#2563eb;
    color:white;
    border:none;
    border-radius:8px;
    font-weight:600;
    cursor:pointer;
    transition:0.3s;
}

.login-box button:hover{
    background:#1e40af;
}

/* ERROR */
.error{
    background:#fee2e2;
    color:#991b1b;
    padding:10px;
    border-radius:6px;
    margin-bottom:15px;
    font-size:14px;
}


/* ANIMATION */
@keyframes fadeIn{
    from{ opacity:0; transform:translateY(-15px);}
    to{ opacity:1; transform:translateY(0);}
}


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
   TABLET
========================= */
@media(max-width:900px){

.header-top{
    flex-direction:column;

    gap:10px;

    padding:18px 10px;
}

.logo-section img{
    width:75px;
}

.tamil-text{
    font-size:16px;
}

.english-text{
    font-size:19px;
}

.sub-text{
    font-size:12px;
}

.nav{
    gap:8px;
}

.nav a{
    font-size:13px;
    padding:8px 10px;
}

}

/* =========================
   MOBILE
========================= */
@media(max-width:600px){

.logo-section img{
    width:60px;
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
    padding:8px;
}

.nav a{
    font-size:12px;
    padding:7px 8px;
}

}

/* TABLET + MOBILE */
@media(max-width:900px){

.header-top{
    flex-direction:column;
    text-align:center;
}

.logo-section{
    position:static;
    transform:none;
}

.logo-section img{
    width:75px;
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

                <img src="../image/Univ.png" alt="University Logo">

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
            <a href="../index.php">Home</a>
            <a href="#">About</a>
            <a href="#">Contact</a>
            <a href="staff/staff_login.php">Staff Login</a>
            <a href="../singlewindow/index.php">S-W-L</a>
        </nav>

    </div>

</header>


<!-- LOGIN SECTION -->
<div class="login-wrapper">
    <div class="login-box">

        <h2>Distance Education Admin</h2>
        <div class="login-note">
    🔒 This portal is restricted to authorized university administrators only.
</div>

        <?php if($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>

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

</body>
</html>