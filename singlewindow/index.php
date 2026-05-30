
<?php

session_start();

require_once __DIR__ . "/db.php";

/* =========================
   SECURITY HEADERS
========================= */

header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");

/* =========================
   ERROR VARIABLE
========================= */

$error = "";

/* =========================
   LOGIN PROCESS
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    /* =========================
       GET INPUTS
    ========================= */

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    /* =========================
       EMPTY VALIDATION
    ========================= */

    if (empty($username) || empty($password)) {

        $error = "Please enter username and password.";

    }

    /* =========================
       USERNAME VALIDATION
    ========================= */

    elseif ($username !== "swadmin") {

        $error = "Unauthorized username.";

    }

    /* =========================
       LENGTH VALIDATION
    ========================= */

    elseif (strlen($username) > 50) {

        $error = "Invalid username.";

    }

    elseif (strlen($password) > 100) {

        $error = "Invalid password.";

    }

    else {

        /* =========================
           FETCH USER FROM DATABASE
        ========================= */

        $stmt = $pdo->prepare(
            "
            SELECT id, username, password
            FROM staff_users
            WHERE username = ?
            LIMIT 1
            "
        );

        $stmt->execute([$username]);

        $user = $stmt->fetch();


/* =========================
   VERIFY PASSWORD
========================= */

if ($user && password_verify($password, $user['password'])) {

    /* =========================
       SECURE SESSION
    ========================= */

    session_regenerate_id(true);

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = 'swadmin';

    /* =========================
       REDIRECT
    ========================= */

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
<title>SWA Login</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>

    

/* RESET */
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:"Times New Roman";
}





/* BODY */
body{
    min-height:100vh;
    background-image: url("image/back.jpeg");
    background-size: cover;
    background-position: center;
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



/* LOGIN */
.login-wrapper{
    display:flex;
    justify-content:center;
    align-items:center;
    height:60vh;
}

.login-box{
    font-family: "Times New Roman", Times, serif;
    background:white;
    width:380px;
    padding:40px;
    border-radius:12px;
    box-shadow:0 15px 35px rgba(0,0,0,0.3);
    text-align:center;
}

.login-box h2{
    font-family: "Times New Roman", Times, serif;
    margin-bottom:20px;
}

/* INPUT */
.login-box input{
    width:100%;
    padding:12px;
    font-family: "Times New Roman", Times, serif;
    margin-bottom:15px;
    border:1px solid #ccc;
    border-radius:8px;
}

/* BUTTON */
.login-box button{
    width:100%;
    padding:12px;
    background:#2563eb;
    color:white;
    border:none;
    border-radius:8px;
    font-weight:bold;
    cursor:pointer;
}

.login-box button:hover{
    font-family: "Times New Roman", Times, serif;
    background:#1e40af;
}

/* ERROR */
.error{
    background:#fee2e2;
    color:#991b1b;
    padding:10px;
    font-family: "Times New Roman", Times, serif;
    border-radius:6px;
    margin-bottom:15px;
}

.note{
    font-size:13px;
    color:#555;
    margin-bottom:15px;
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
    max-width:1100px;
    margin:auto;
}

.about-ide p{
    line-height:1.8;
    font-family: "Times New Roman", Times, serif;
    font-size:clamp(14px,1.5vw,16px);
}

/* =========================
   TOP BUTTON
========================= */
#topBtn{

    bottom:20px;
    right:20px;

    width:45px;
    height:45px;

    border:none;
    border-radius:50%;

    background:var(--primary);
    color:var(--white);
font-family: "Times New Roman", Times, serif;
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
}





@media(max-width:768px){

    .header-top{
        flex-direction:column;
        padding:20px 10px;
    }

    .logo-section{
        position:static;
        transform:none;
        margin-bottom:10px;
    }

    .logo-section img{
        width:90px;
    }

    .nav-links{
        gap:18px;
        padding:15px 10px;
    }

    .login-box{
        width:95%;
        padding:25px;
    }

    .english-text{
        font-size:24px;
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



<!-- LOGIN -->
<div class="login-wrapper">
<div class="login-box">

<h2>🎓 SWA Staff Login</h2>

<div class="note">
🔒 Authorized staff access only
</div>

<?php if(!empty($error)): ?> <div class="error"> 
<?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?> 
</div>
<?php endif; ?>
    <form method="POST" autocomplete="off"> 
    <input type="text" name="username" placeholder="Username" required >
    <input type="password" name="password" placeholder="Password" required > 
    <button type="submit"> Login </button> 
</form>

</div>
</div>

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