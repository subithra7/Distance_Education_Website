<?php
session_start();

if(!isset($_SESSION['username'])){
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html>
<head>
<title>SWA Dashboard</title>

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>

/* RESET */
*{margin:0;padding:0;box-sizing:border-box;font-family: "Times New Roman", Times, serif;}

/* BODY */
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



/* CARDS */
.cards{
display:grid;
grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
gap:20px;
font-family: "Times New Roman", Times, serif;
}

.card{
padding:25px;
border-radius:12px;
color:white;
font-family: "Times New Roman", Times, serif;
text-align:center;
transition:0.3s;
cursor:pointer;
}

.card:hover{
    font-family: "Times New Roman", Times, serif;
transform:translateY(-5px);
}

.card i{
font-size:30px;
margin-bottom:10px;
font-family: "Times New Roman", Times, serif;
}

.blue{ background:#2563eb; }
.green{ background:#16a34a; }
.orange{ background:#f59e0b; }
.purple{ background:#7c3aed; }

/* =========================
   FOOTER
========================= */
footer{
    background: #083c72;
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



<div class="wrapper">

<!-- SIDEBAR -->
<?php include "sidebar.php"; ?>

<!-- MAIN -->
<div class="main">

<div class="header-box">
<h2>Welcome <?php echo $username; ?></h2>
<p>SWA Staff Dashboard</p>
</div>

<!-- CARDS -->
<div class="cards">

<a href="new_application1.php" style="text-decoration:none;">
<div class="card blue">
<i class="fa fa-user-plus"></i>
<h3>New Application</h3>
<p>Create new SWA admission</p>
</div>
</a>

<a href="reintimation.php" style="text-decoration:none;">
<div class="card orange">
<i class="fa fa-refresh"></i>
<h3>Reintimation</h3>
<p>Update existing application</p>
</div>
</a>

<a href="payment.php" style="text-decoration:none;">
<div class="card green">
<i class="fa fa-credit-card"></i>
<h3>Payment</h3>
<p>Manage fee payments</p>
</div>
</a>

<a href="list.php" style="text-decoration:none;">
<div class="card purple">
<i class="fa fa-list"></i>
<h3>Application List</h3>
<p>View all SWA applications</p>
</div>
</a>

</div>



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