<?php 
include("db.php");

// ---------- UPDATE ----------
if(isset($_POST['update'])){
    $stmt = $pdo->prepare("
        UPDATE records SET 
        name=?, name_tamil=?, mobile=?, email=?, medium=? 
        WHERE enrollment_no=?
    ");

    $stmt->execute([
        $_POST['name'],
        $_POST['name_tamil'],
        $_POST['mobile'],
        $_POST['email'],
        $_POST['medium'],
        $_POST['enrollment_no']
    ]);

    echo "<script>alert('Updated Successfully');</script>";
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Admission Page</title>

<style>
body{
    font-family: "Times New Roman", Times, serif;
    background: #f4f6f9;
}

/* SEARCH */
.search-box{
    width:400px;
    margin:40px auto;
    font-family: "Times New Roman", Times, serif;
    text-align:center;
}

.search-box input{
    font-family: "Times New Roman", Times, serif;
    padding:10px;
    width:70%;
}

.search-box button{
    font-family: "Times New Roman", Times, serif;
    padding:10px;
    background:#4a6ea9;
    color:#fff;
    border:none;
}

/* CARD */
.card{
    width:900px;
    font-family: "Times New Roman", Times, serif;
    margin:20px auto;
    background:#fff;
    padding:25px;
    border-radius:12px;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
}

/* HEADER */
.header{text-align:center;font-family: "Times New Roman", Times, serif;}

/* BUTTONS */
.btn{
    padding:10px 15px;
    border:none;
    border-radius:5px;
    margin:5px;
    cursor:pointer;
    font-family: "Times New Roman", Times, serif;
}

.print{background:#27ae60;color:#fff;}
.pdf{background:#2980b9;color:#fff;}
.edit{background:#f39c12;color:#fff;}
.save{background:#8e44ad;color:#fff;}

/* EDIT MODE */
.edit-field{display:none;}
.editing .view{display:none;}
.editing .edit-field{display:inline-block;}

/* ================= PRINT DESIGN (OLD STYLE) ================= */
@media print {

    body * {
        visibility: hidden;
    }

    #printArea, #printArea * {
        visibility: visible;
    }

    #printArea {
        position: absolute;
        left: 0;
        top: 0;
        width: 850px;
        border: 3px solid #4a6ea9;
        border-radius: 15px;
        padding: 20px;
        font-family: 'Times New Roman';
        background: #fff;
    }

    input{border:none;}

    .btn{display:none;}

    .header h2, .header h3, .header p{
        text-align:center;
    }
}

.search-container{
    width:100%;
    font-family: "Times New Roman", Times, serif;
    display:flex;
    justify-content:center;
    margin-top:50px;
}

.search-form{
    font-family: "Times New Roman", Times, serif;
    background:#ffffff;
    padding:25px 30px;
    border-radius:12px;
    box-shadow:0 8px 25px rgba(0,0,0,0.1);
    width:420px;
}

.search-form label{
    font-family: "Times New Roman", Times, serif;
    font-weight:600;
    color:#2c3e50;
    display:block;
    margin-bottom:10px;
}

.search-box-new{
    display:flex;
    font-family: "Times New Roman", Times, serif;
    gap:10px;
}

.search-box-new input{
    flex:1;
    font-family: "Times New Roman", Times, serif;
    padding:12px;
    border:1px solid #ccc;
    border-radius:8px;
    font-size:14px;
    outline:none;
    transition:0.3s;
}

.search-box-new input:focus{
    border-color:#4a6ea9;
    font-family: "Times New Roman", Times, serif;
    box-shadow:0 0 5px rgba(74,110,169,0.3);
}


.search-box-new button{
    padding:12px 18px;
    font-family: "Times New Roman", Times, serif;
    background:linear-gradient(135deg,#4a6ea9,#2c3e50);
    color:#fff;
    border:none;
    border-radius:8px;
    cursor:pointer;
    font-weight:600;
    transition:0.3s;
}

.search-box-new button:hover{
    transform:translateY(-2px);
    box-shadow:0 5px 15px rgba(0,0,0,0.2);
    font-family: "Times New Roman", Times, serif;
}



/* ===== FOOTER ===== */
footer{

    width:100%;

    background:#005ea6;

    color:#ffffff;

    text-align:center;

    padding:30px 20px;

    margin-top:40px;
}

.about-ide{

    width:100%;

    max-width:1400px;

    margin:auto;
}

.about-ide p{
    line-height:1.8;
    font-family: "Times New Roman", Times, serif;
    font-size:clamp(14px,1.5vw,16px);
}


/* =========================
   HEADER
========================= */

.top-header{

    width:100%;

    background:#d9e4dc;

    border-top:6px solid #005ea6;

    box-shadow:0 2px 10px rgba(0,0,0,0.08);

    position: relative;
}

.head-container{
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

    padding:25px 20px;

    text-align:center;
}

/* =========================
   LOGO
========================= */

.logo-section{

    position:absolute;

    left:25px;

    top:50%;

    transform:translateY(-50%);
}

.logo-section img{

    width:210px;

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

    box-shadow:0 2px 6px rgba(0,0,0,0.08);
}

.nav-container{

    width:100%;

    max-width:1400px;

    margin:auto;

    display:flex;

    justify-content:center;

    align-items:center;
}

.nav-links{

    display:flex;

    align-items:center;

    justify-content:center;

    gap:40px;

    padding:16px 20px;

    flex-wrap:wrap;
}

/* NAV LINKS */


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
</style>

</head>


<!-- HEADER -->

<header class="top-header">

<div class="head-container">

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

<div class="search-container">
    <form method="GET" class="search-form">
        <label>🔍 Enrollment Number</label>
        
        <div class="search-box-new">
            <input type="text" name="enrollment_no" placeholder="Enter Enrollment No (Eg: A26101UEC6017)" required>
            <button type="submit">Search</button>
        </div>
    </form>
</div>

<?php
if(isset($_GET['enrollment_no'])){

    $en = trim($_GET['enrollment_no']);

    $stmt = $pdo->prepare("SELECT * FROM records WHERE LOWER(TRIM(enrollment_no)) = LOWER(TRIM(?))");
    $stmt->execute([$en]);
    $d = $stmt->fetch();

    if($d){

        // COURSE TABLE
        if($d['course_type']=="UG"){
            $courseTable="ug_courses";
        }elseif($d['course_type']=="PG"){
            $courseTable="pg_courses";
        }elseif($d['course_type']=="DIP"){
            $courseTable="diploma_courses";
        }else{
            $courseTable="certificate_courses";
        }

        // COURSE CODE
        $getCourse = $pdo->prepare("
        SELECT course_code FROM $courseTable
        WHERE programme_degree=? AND main_subject=?
        LIMIT 1
        ");
        $getCourse->execute([$d['programme_name'], $d['main_subject']]);
        $courseRow = $getCourse->fetch();
        $courseCode = $courseRow['course_code'] ?? '';

        // FEES
        $feeStmt = $pdo->prepare("
        SELECT special_fee, tuition_fee, general_fee
        FROM course_fees
        WHERE course_code=?
        LIMIT 1
        ");
        $feeStmt->execute([$courseCode]);
        $fee = $feeStmt->fetch();

        $G  = $fee['general_fee'] ?? 0;
        $SF = $fee['special_fee'] ?? 0;
        $TF = $fee['tuition_fee'] ?? 0;

        // CATEGORY
        $category = strtoupper($d['approved_category'] ?? 'GENERAL');

        switch($category){
            case "VC":
            case "PRISONER":
                $total_fee = $G - $TF;
                break;

            case "DA":
                $total_fee = $G - ($SF + $TF);
                break;

            case "STAFF":
                $total_fee = $G - (($TF * 50)/100);
                break;

            default:
                $total_fee = $G;
        }

        if($total_fee < 0){ $total_fee = 0; }

        $date = date("d-m-Y");

        $full_name = $d['name'];
        if(!empty($d['name_tamil'])){
            $full_name .= " - ".$d['name_tamil'];
        }
?>

<div class="card" id="printArea">

<div class="header">
    <h2>UNIVERSITY OF MADRAS</h2>
    <h3>INSTITUTE OF DISTANCE EDUCATION</h3>
    <p>Chepauk, Chennai - 600 005</p>
</div>

<h3 style="text-align:center;">PROVISIONAL ADMISSION INTIMATION</h3>

<form method="POST">
<input type="hidden" name="enrollment_no" value="<?php echo $d['enrollment_no']; ?>">

<table width="100%">

<tr>
<td><b>Name</b></td>
<td>
<span class="view"><?php echo $full_name; ?></span>
<input class="edit-field" name="name" value="<?php echo $d['name']; ?>">
<input class="edit-field" name="name_tamil" value="<?php echo $d['name_tamil']; ?>">
</td>

<td><b>Date</b></td>
<td><?php echo $date; ?></td>
</tr>

<tr>
<td><b>Programme</b></td>
<td><?php echo $d['programme_name']." - ".$d['main_subject']; ?></td>
</tr>

<tr>
<td><b>Enrollment No</b></td>
<td><?php echo $d['enrollment_no']; ?></td>
</tr>

<tr>
<td><b>Fees</b></td>
<td>₹ <?php echo number_format($total_fee); ?> /-</td>
</tr>

<tr>
<td><b>DOB</b></td>
<td><?php echo $d['dob']; ?></td>

<td><b>Gender</b></td>
<td><?php echo $d['gender']; ?></td>
</tr>

<tr>
<td><b>Mobile</b></td>
<td>
<span class="view"><?php echo $d['mobile']; ?></span>
<input class="edit-field" name="mobile" value="<?php echo $d['mobile']; ?>">
</td>

<td><b>Email</b></td>
<td>
<span class="view"><?php echo $d['email']; ?></span>
<input class="edit-field" name="email" value="<?php echo $d['email']; ?>">
</td>
</tr>

<tr>
<td><b>Medium</b></td>
<td>
<span class="view"><?php echo $d['medium']; ?></span>
<input class="edit-field" name="medium" value="<?php echo $d['medium']; ?>">
</td>
</tr>

<tr>
<td><b>Certificate Status</b></td>
<td>
<?php echo ($d['certificate_verified']==1) ? "Certificates Verified" : "Not Verified"; ?>
</td>
</tr>

</table>

<br>

<div style="text-align:center;">
    <button type="button" class="btn edit" onclick="toggleEdit()">✏️ Edit</button>
    <button class="btn save" name="update">💾 Save</button>
    <button type="button" class="btn print" onclick="printPage()">🖨️ Print</button>
    <button type="button" class="btn pdf" onclick="downloadPDF()">📄 PDF</button>
</div>

</form>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
function toggleEdit(){
    document.getElementById("printArea").classList.toggle("editing");
}

function printPage(){
    window.print();
}

function downloadPDF(){
    var element = document.getElementById("printArea");
    html2pdf().from(element).save("Admission.pdf");
}
</script>

<?php
    } else {
        echo "<p style='text-align:center;color:red;'>No record found</p>";
    }
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