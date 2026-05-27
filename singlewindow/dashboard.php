<?php
session_start();

if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit();
}

$username = $_SESSION['user'];
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
body{background:#f1f5f9;}

/* HEADER */
.top-header{background:#2374ad;padding:15px 0;color:white;}

.container{
width:95%;margin:auto;display:flex;
justify-content:space-between;align-items:center;
}

.logo{display:flex;align-items:center;}
.logo img{width:60px;margin-right:10px;}

.tamil-text{
    font-size:14px;
    font-family: "Times New Roman", Times, serif;
}
.english-text{
    font-size:18px;
    font-weight:bold;
    font-family: "Times New Roman", Times, serif;
}


.nav a{
    color:white;
    text-decoration:none;
    font-family: "Times New Roman", Times, serif;
    margin-left:15px;
}



.header-box{
background:white;
padding:15px;border-radius:8px;
margin-bottom:20px;
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

/* FOOTER */
.footer{
text-align:center;margin-top:30px;color:gray;
font-family: "Times New Roman", Times, serif;
}

</style>
</head>

<body>

<!-- HEADER -->
<header class="top-header">
<div class="container">

<div class="logo">
<img src="image/Univ.png">
<div>
<div class="tamil-text">சென்னை பல்கலைக்கழகம் – தொலைதூரக் கல்வி நிறுவனம்</div>
<div class="english-text">University of Madras – IDE</div>
</div>
</div>

<nav class="nav">
<a href="dashboard.php">Dashboard</a>
<a href="../admin/login.php">Admin Login</a>
<a href="../admin/staff/staff_login.php">Staff Login</a>
<a href="logout.php">Logout</a>
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

<div class="footer">
© 2026 University Admission System
</div>

</div>

</div>

</body>
</html>