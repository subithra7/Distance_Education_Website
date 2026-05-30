<?php
session_start();
require_once "../../db.php";

$error = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if(empty($username) || empty($password)){
        $error = "Please enter username and password.";
    } else {

        $stmt = $pdo->prepare("SELECT * FROM staff WHERE username=?");
        $stmt->execute([$username]);
        $staff = $stmt->fetch(PDO::FETCH_ASSOC);

        if($staff && $password == $staff['password']){
            $_SESSION['staff'] = $staff['username'];
            $_SESSION['course_type'] = $staff['course_type'];

            header("Location: students.php");
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
<title>University Staff Login</title>
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

background-image:url("../../image/back.jpeg");
background-size:cover;
background-position:center;
background-repeat:no-repeat;
background-attachment:fixed;
}

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
    font-family: "Times New Roman", Times, serif;
    --shadow:0 4px 15px rgba(0,0,0,0.12);
    --radius:16px;
}

html{
    scroll-behavior:smooth;
    -webkit-text-size-adjust:100%;
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

/* LOGIN WRAPPER */
.login-wrapper{
display:flex;
justify-content:center;
align-items:center;
height:60vh;
}

/* LOGIN BOX */
.login-box{
background:white;
width:380px;
padding:40px;
border-radius:12px;
box-shadow:0 15px 35px rgba(0,0,0,0.3);
text-align:center;
}

.login-box h2{
margin-bottom:25px;
color:#1e293b;
}

/* INPUTS */
.login-box input{
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

</style>
</head>

<body>

<!-- HEADER -->

<header class="top-header">

    <div class="container">

        <div class="header-top">
        <div class="logo-section">

<img
    src="../../image/Univ.png"
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

<!-- LOGIN BOX -->

<div class="login-wrapper">
<div class="login-box">

<h2>DISTANCE EDUCATION STAFF </h2>

<p>🔒 Authorized staff access only</p>

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

</body>
</html>