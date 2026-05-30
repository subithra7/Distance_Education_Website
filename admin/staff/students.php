<?php
session_start();
require_once "../../db.php";

if(!isset($_SESSION['staff'])){
    header("Location: staff_login.php");
    exit();
}

$type = $_SESSION['course_type'] ?? '';
$search = $_GET['search'] ?? '';

if(!empty($search)){
    $searchTerm = "%".$search."%";
    
    // ⭐ CONDITION: If search contains 'UBA'
    $order = (stripos($search, 'UBA') !== false || stripos($search, 'A26') !== false) ? "ASC" : "DESC";
    
    $stmt = $pdo->prepare("
        SELECT * FROM records 
        WHERE course_type=? 
        AND (
            application_no LIKE ?
            OR enrollment_no LIKE ?
            OR name LIKE ?
            OR mobile LIKE ?
        )
        ORDER BY id $order
    ");
    
    $stmt->execute([$type, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
} else {
    $stmt = $pdo->prepare("
        SELECT * FROM records 
        WHERE course_type=? 
        ORDER BY id DESC
    ");
    
    $stmt->execute([$type]);
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Student List</title>

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



body{
font-family:Arial;
background:#f5f7fb;
margin:0;
}

/* TABLE */

.table-container{
width:95%;
margin:30px auto;
background:white;
border-radius:8px;
overflow:hidden;
box-shadow:0 3px 10px rgba(0,0,0,0.1);
}

table{
width:100%;
border-collapse:collapse;
}

thead{
background:#1f2a44;
color:white;
}

thead th{
padding:15px;
text-align:left;
font-size:15px;
}

tbody td{
padding:15px;
border-bottom:1px solid #eee;
}

tbody tr:hover{
background:#f9fafc;
}

/* CHECKBOX */

input[type="checkbox"]{
width:18px;
height:18px;
}

/* STATUS BADGE */

.status{
padding:6px 14px;
border-radius:20px;
font-size:13px;
font-weight:bold;
}

.approved{
background:#c7ead7;
color:#0b6b3a;
}

.pending{
background:#ffe6a3;
color:#996c00;
}

.rejected{
background:#f5c6cb;
color:#7a0000;
}

/* VIEW BUTTON */

.view-btn{
background:#1d4ed8;
color:white;
padding:8px 18px;
border-radius:8px;
text-decoration:none;
font-size:14px;
}

.view-btn:hover{
background:#123bb5;
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
            <a href="../../admin/login.php">Admin Panel</a>
         
        </div>

    </div>

</nav>


</div>

</header>


<form method="GET" style="margin:20px;">

<input type="text" name="search"
placeholder="Search Application / Enrollment / Name / Mobile"
value="<?php echo $_GET['search'] ?? ''; ?>"
style="padding:10px;width:300px;border:1px solid #ccc;border-radius:6px;">

<button type="submit"
style="padding:10px 15px;background:#1d4ed8;color:#fff;border:none;border-radius:6px;">
Search
</button>

<a href="students.php"
style="padding:10px 15px;background:#555;color:#fff;text-decoration:none;border-radius:6px;">
Reset
</a>

</form>

<div class="table-container">

<table>

<thead>
<tr>
<th><input type="checkbox"></th>
<th>Enrollment No</th>
<th>Application ID</th>
<th>Name</th>
<th>Course</th>
<th>Mobile</th>
<th>Status</th>
<th>Date</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php while($row = $stmt->fetch(PDO::FETCH_ASSOC)){

$statusClass="pending";

if($row['status']=="Approved") $statusClass="approved";
elseif($row['status']=="Rejected") $statusClass="rejected";

?>

<tr>

<td><input type="checkbox"></td>

<td>
<?php 
if(!empty($row['enrollment_no'])){
    echo $row['enrollment_no'];
}else{
    echo "-";
}
?>
</td>

<td><?php echo $row['application_no']; ?></td>

<td><?php echo $row['name']; ?></td>

<td><?php echo $row['programme_name']; ?></td>

<td><?php echo $row['mobile']; ?></td>

<td>
<span class="status <?php echo $statusClass; ?>">
<?php echo $row['status']; ?>
</span>
</td>

<td><?php echo date("d-m-Y",strtotime($row['created_at'])); ?></td>

<td>
<a class="view-btn" href="view.php?id=<?php echo $row['id']; ?>">
View
</a>
</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</body>
</html>