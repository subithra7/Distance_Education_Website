<?php
session_start();
require "../db.php";

if($_SERVER["REQUEST_METHOD"]=="POST"){

    $username = $_POST['username'];
    $password = $_POST['password'];

    // 🔥 MYSQLI QUERY (FIXED)
    $stmt = $pdo->prepare("SELECT * FROM lsc_users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if($user && $password == $user['password']){

        $_SESSION['lsc_code'] = $user['lsc_code'];
        $_SESSION['lsc_name'] = $user['lsc_name'];

        header("Location: ../admission-form/ap1.php");
        exit;

    } else {
        echo "Invalid Login";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>University Staff Login</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>

    /* =========================
   ROOT VARIABLES
========================= */
:root{
    --primary:#0b5fa5;
    --primary-dark:#083c72;
    --secondary:#075d9f;
    --white:#ffffff;
    --light:#f5f7fb;
    --text:#222;
    --shadow:0 4px 15px rgba(0,0,0,0.12);
    --radius:16px;
}

/* =========================
   RESET
========================= */
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

html{

    scroll-behavior:smooth;
    -webkit-text-size-adjust:100%;
}

body{
    font-family: "Times New Roman", Times, serif;    
overflow-x:hidden;
    background:var(--light);
    color:var(--text);
    line-height:1.6;
}


/* =========================
   HEADER
========================= */
.top-header{
    width:100%;
    font-family: "Times New Roman", Times, serif;
    background:rgb(216,230,220);
    border-top:7px solid var(--primary);
    box-shadow:var(--shadow);
}

.container{
    font-family: "Times New Roman", Times, serif;
    width:100%;
    max-width:1400px;
    margin:auto;
    padding:0 15px;
}

/* =========================
   HEADER TOP
========================= */
.header-top{
    font-family: "Times New Roman", Times, serif;
    position:relative;

    display:flex;
    align-items:center;
    justify-content:center;

    min-height:180px;

    padding:20px;

    text-align:center;
}
/* =========================
   LOGO
========================= */
.logo-section{
    position:absolute;
    left:20px;
    top:50%;
    font-family: "Times New Roman", Times, serif;
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
    font-family: "Times New Roman", Times, serif;
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
    font-family: "Times New Roman", Times, serif;
    line-height:1.6;
}

/* =========================
   NAVBAR
========================= */

.navbar{
    width:100%;
    background: #005ea6;
    position:sticky;
    top:0;
    z-index:1000;
    max-width:2000px;
}

.nav-container{
    width:100%;
    max-width:1400px;
    margin:auto;
font-family: "Times New Roman", Times, serif;
    display:flex;
    justify-content:center;
    align-items:center;

    padding:0 0px;
}

/* NAV LINKS */

.nav-links{
    display:flex;
    align-items:center;
    justify-content:center;
    gap:35px;

    padding:18px 0;
font-family: "Times New Roman", Times, serif;
    flex-wrap:wrap;
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
font-family: "Times New Roman", Times, serif;
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
    font-family: "Times New Roman", Times, serif;
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

/* LOGIN WRAPPER */
.login-wrapper{
display:flex;
justify-content:center;
align-items:center;
height:80vh;
}

/* LOGIN BOX */
.login-box{
background:white;
width:380px;
padding:40px;
border-radius:12px;
box-shadow:0 15px 35px rgba(0,0,0,0.3);
text-align:center;
font-family: "Times New Roman", Times, serif;
}

.login-box h2{
margin-bottom:25px;
color:#1e293b;
font-family: "Times New Roman", Times, serif;
}

/* INPUTS */
.login-box input{
    font-family: "Times New Roman", Times, serif;
width:100%;
padding:12px;
margin-bottom:18px;
border:1px solid #d1d5db;
border-radius:8px;
font-size:15px;
}

/* BUTTON */
.login-box button{
width:60%;
font-family: "Times New Roman", Times, serif;
padding:12px;
background:#2563eb;
color:white;
border:none;
border-radius:8px;
font-weight:600;
cursor:pointer;
}

.login-box button:hover{
background:#1e40af;
font-family: "Times New Roman", Times, serif;
}

/* ERROR */
.error{
    font-family: "Times New Roman", Times, serif;
background:#fee2e2;
color:#991b1b;
padding:10px;
border-radius:6px;
margin-bottom:15px;
font-size:14px;
}

.banner{
    font-family: "Times New Roman", Times, serif;
    width:100%;
    min-height:100vh;

    background-image:url("image/back.jpeg");
    background-size:cover;
    background-position:center;
    background-repeat:no-repeat;

    display:flex;
    justify-content:center;
    align-items:center;

    padding:40px 15px;
}   



/* =========================
   FOOTER
========================= */
footer{
    background:var(--secondary);
    color:var(--white);
    text-align:center;
    padding:25px 15px;
    font-family: "Times New Roman", Times, serif;
}

.about-ide{
    font-family: "Times New Roman", Times, serif;
    max-width:1100px;
    margin:auto;
}

.about-ide p{
    line-height:1.8;
    font-size:clamp(14px,1.5vw,16px);
    font-family: "Times New Roman", Times, serif;
}

/* =========================
   TOP BUTTON
========================= */
#topBtn{
    position:fixed;
    font-family: "Times New Roman", Times, serif;
    bottom:20px;
    right:20px;

    width:45px;
    height:45px;

    border:none;
    border-radius:50%;

    background:var(--primary);
    color:var(--white);

    font-size:22px;
    font-weight:bold;

    cursor:pointer;

    box-shadow:var(--shadow);

    transition:0.3s ease;

    z-index:999;
}

#topBtn:hover{
    background:var(--primary-dark);
    transform:translateY(-3px);
font-family: "Times New Roman", Times, serif;
}





</style>
</head>

<body>

<!-- HEADER -->
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

<div
    class="nav-links"
    id="navLinks"
>

<a class="active" href="#">Home</a>
<a href="#">About Us</a>
<a href="#">Contact Us</a>
<a href="../admin/login.php">Admin Panel</a>

<a href="lsc/login.php">LSC Login</a>
<a href="../singlewindow/index.php">S-W-L</a>

</div>

</div>

</nav>

</div>

</header>

<!-- LOGIN BOX -->
<section class="banner">
<div class="login-wrapper">
<div class="login-box">

<h2>LSC LOGIN</h2>

<p>🔒 Authorized people access only</p>


<form method="post">
    <input type="text" name="username" placeholder="Username" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>
    <button type="submit">Login</button>
</form>

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


</body>
</html>