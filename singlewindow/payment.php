<?php
session_start();
include("db.php"); // make sure $pdo is defined here
?>

<!DOCTYPE html>
<html>
<head>
<title>Payment Page</title>

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




.search-wrapper{
    display:flex;
    justify-content:center;
    margin-top:30px;
}
.search-card{
    background:#f5f7fa;
    font-family: "Times New Roman", Times, serif;
    padding:25px;
    border-radius:15px;
    width:500px;
    box-shadow:0 10px 25px rgba(0,0,0,0.08);
}
.search-box-pro{
    display:flex;
    gap:10px;
}
.search-box-pro input{
    flex:1;
    font-family: "Times New Roman", Times, serif;
    padding:12px;
    border-radius:10px;
    border:1px solid #ccc;
}
.search-box-pro button{
    background:linear-gradient(135deg,#2a5298,#1e3c72);
    color:#fff;
    font-family: "Times New Roman", Times, serif;
    border:none;
    padding:12px 20px;
    border-radius:10px;
}
.card{
    background:#fff;
    padding:25px;
    font-family: "Times New Roman", Times, serif;
    border-radius:12px;
    box-shadow:0 8px 20px rgba(0,0,0,0.08);
    margin-top:20px;
}
.details{
    display:grid;
    grid-template-columns: 1fr 1fr;
    font-family: "Times New Roman", Times, serif;
    gap:15px;
}
.details div{
    font-family: "Times New Roman", Times, serif;
    background:#f7f9fc;
    padding:10px;
    border-radius:6px;
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

<?php

$currentPage = basename($_SERVER['PHP_SELF']);

?>


<div class="nav-links" id="navLinks">

<a href="dashboard.php"
class="<?php echo ($currentPage == 'dashboard.php') ? 'active' : ''; ?>">

<i class="fa fa-home"></i>
Dashboard

</a>
|
<a href="new_application1.php"
class="<?php echo ($currentPage == 'new_application1.php') ? 'active' : ''; ?>">

<i class="fa fa-user-plus"></i>
New Admission

</a>
|
<a href="reintimation.php"
class="<?php echo ($currentPage == 'reintimation.php') ? 'active' : ''; ?>">

<i class="fa fa-file-alt"></i>
Re-Intimation

</a>
|
<a href="payment.php"
class="<?php echo ($currentPage == 'payment.php') ? 'active' : ''; ?>">

<i class="fa fa-credit-card"></i>
Fee Payment

</a>
|
<a href="list.php"
class="<?php echo ($currentPage == 'list.php') ? 'active' : ''; ?>">

<i class="fa fa-list"></i>
Applications

</a>
|
<a href="logout.php">

<i class="fa fa-sign-out-alt"></i>
Logout

</a>

</div>

</div>
</nav>


</div>

</header>


<div class="wrapper">
<?php include "sidebar.php"; ?>
<div class="main">

<h2 style="text-align:center;">Payment Page</h2>

<!-- SEARCH -->
<div class="search-wrapper">
<form method="GET" class="search-card">
    <label><b>🔍 Enrollment Number</b></label><br><br>
    <div class="search-box-pro">
        <input type="text" name="enrollment_no" required>
        <button type="submit">Search</button>
    </div>
</form>
</div>

<?php
if(isset($_GET['enrollment_no'])){

    $en = trim($_GET['enrollment_no']);

    $stmt = $pdo->prepare("SELECT * FROM records WHERE enrollment_no = ?");
    $stmt->execute([$en]);
    $d = $stmt->fetch(PDO::FETCH_ASSOC);

    if($d){

        // COURSE TABLE
        switch($d['course_type']){
            case "UG": $courseTable="ug_courses"; break;
            case "PG": $courseTable="pg_courses"; break;
            case "DIP": $courseTable="diploma_courses"; break;
            default: $courseTable="certificate_courses";
        }

        // GET COURSE CODE
        $getCourse = $pdo->prepare("SELECT course_code FROM $courseTable WHERE programme_degree=? AND main_subject=? LIMIT 1");
        $getCourse->execute([$d['programme_name'], $d['main_subject']]);
        $courseRow = $getCourse->fetch(PDO::FETCH_ASSOC);
        $courseCode = $courseRow['course_code'] ?? '';

        // GET FEES
        $feeStmt = $pdo->prepare("SELECT special_fee, tuition_fee, general_fee FROM course_fees WHERE course_code=? LIMIT 1");
        $feeStmt->execute([$courseCode]);
        $fee = $feeStmt->fetch(PDO::FETCH_ASSOC);

        $G  = $fee['general_fee'] ?? 0;
        $SF = $fee['special_fee'] ?? 0;
        $TF = $fee['tuition_fee'] ?? 0;

        $category = strtoupper($d['approved_category'] ?? 'GENERAL');

        switch($category){
            case "VC":
            case "PRISONER": $total_fee = $G - $TF; break;
            case "DA": $total_fee = $G - ($SF + $TF); break;
            case "STAFF": $total_fee = $G - (($TF * 50)/100); break;
            default: $total_fee = $G;
        }

        if($total_fee < 0) $total_fee = 0;
?>

<div class="card">
<h3>Student Details</h3>

<div class="details">
<div><b>Name:</b> <?= htmlspecialchars($d['name']) ?></div>
<div><b>Enrollment:</b> <?= htmlspecialchars($d['enrollment_no']) ?></div>
<div><b>Programme:</b> <?= htmlspecialchars($d['programme_name']." - ".$d['main_subject']) ?></div>
<div><b>Fees:</b> ₹<?= number_format($total_fee) ?></div>
<div><b>Phone:</b> <?= htmlspecialchars($d['mobile']) ?></div>
<div><b>Email:</b> <?= htmlspecialchars($d['email']) ?></div>
</div>

<?php if(!empty($d['paid_amount']) && $d['paid_amount'] > 0){ ?>
<p style="color:green;">
Paid: ₹<?= $d['paid_amount'] ?><br>
<?= $d['payment_date'] ?>
</p>
<?php } ?>

</div>

<!-- PAYMENT -->
<form target="_blank" method="post" action="https://apps.indianbank.in/muexam/finalCustModule.aspx">

<input type="hidden" name="UserId" value="IdeUnom">
<input type="hidden" name="PassWord" value="MuniCh1857">
<input type="hidden" name="RegNo" value="<?= $d['enrollment_no'] ?>">
<input type="hidden" name="Name" value="<?= $d['name'] ?>">
<input type="hidden" name="Purpose" value="1 YR ADMISSION FEE">
<input type="hidden" name="Degree" value="<?= $courseCode ?>">
<input type="hidden" name="Amt" value="<?= $total_fee ?>">

<div style="text-align:center;">
<input type="submit" value="💳 Pay Now"
style="padding:10px 20px;background:#27ae60;color:#fff;border:none;border-radius:6px;">
</div>
</form>

<br>

<!-- MARK PAID -->
<div style="text-align:center;">
<form method="POST">
<input type="hidden" name="paid_enrollment" value="<?= $d['enrollment_no'] ?>">
<input type="hidden" name="paid_amount" value="<?= $total_fee ?>">
<button type="submit" name="mark_paid"
style="padding:10px;background:#e74c3c;color:#fff;border:none;border-radius:6px;">
Mark as Paid
</button>
</form>
</div>

<?php
    } else {
        echo "<h3 style='color:red;text-align:center;'>No record found!</h3>";
    }
}

// MARK PAID
if(isset($_POST['mark_paid'])){

    $enroll = $_POST['paid_enrollment'];
    $amount = $_POST['paid_amount'];

    $stmt = $pdo->prepare("
        UPDATE records 
        SET fees = 0, paid_amount = ?, payment_date = NOW() 
        WHERE enrollment_no = ?
    ");
    $stmt->execute([$amount, $enroll]);

    echo "<script>alert('Payment Stored Successfully');window.location.href='';</script>";
}
?>

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