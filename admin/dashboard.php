<?php
session_start();
require_once "../db.php";

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}

/* Dashboard Statistics */
$total = $pdo->query("SELECT COUNT(*) c FROM records")->fetch(PDO::FETCH_ASSOC)['c'];
$pending = $pdo->query("SELECT COUNT(*) c FROM records WHERE status='Pending'")->fetch(PDO::FETCH_ASSOC)['c'];
$approved = $pdo->query("SELECT COUNT(*) c FROM records WHERE status='Approved'")->fetch(PDO::FETCH_ASSOC)['c'];
$rejected = $pdo->query("SELECT COUNT(*) c FROM records WHERE status='Rejected'")->fetch(PDO::FETCH_ASSOC)['c'];

$ug  = $pdo->query("SELECT COUNT(*) c FROM records WHERE course_type='UG'")->fetch(PDO::FETCH_ASSOC)['c'];
$pg  = $pdo->query("SELECT COUNT(*) c FROM records WHERE course_type='PG'")->fetch(PDO::FETCH_ASSOC)['c'];
$dip = $pdo->query("SELECT COUNT(*) c FROM records WHERE course_type='DIP'")->fetch(PDO::FETCH_ASSOC)['c'];
$cert= $pdo->query("SELECT COUNT(*) c FROM records WHERE course_type='CERT'")->fetch(PDO::FETCH_ASSOC)['c'];

$oc  = $pdo->query("SELECT COUNT(*) c FROM records WHERE community='OC'")->fetch(PDO::FETCH_ASSOC)['c'];
$bc  = $pdo->query("SELECT COUNT(*) c FROM records WHERE community='BC'")->fetch(PDO::FETCH_ASSOC)['c'];
$mbc = $pdo->query("SELECT COUNT(*) c FROM records WHERE community='MBC'")->fetch(PDO::FETCH_ASSOC)['c'];
$sc  = $pdo->query("SELECT COUNT(*) c FROM records WHERE community='SC'")->fetch(PDO::FETCH_ASSOC)['c'];
$st  = $pdo->query("SELECT COUNT(*) c FROM records WHERE community='ST'")->fetch(PDO::FETCH_ASSOC)['c'];
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="assets/style.css">
<title>Admin Dashboard</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header class="top-header">
  <div class="app">
    
    <div class="logo">
      <img src="../image/Univ.png" alt="University Logo">
      <div class="university-text">
        <strong>சென்னை பல்கலைக்கழகம் – தொலைதூரக் கல்வி நிறுவனம்</strong><br>
        University of Madras – Institute of Distance Education
      </div>
    </div>

    <div class="nav">
      <a href="#">Home</a>
      <a href="#">Contact</a>
    </div>

  </div>
</header>

<div class="sidebar">
<h2>Admin Panel</h2>
<a href="dashboard.php">Dashboard</a>
<a href="admission/list.php">Applications</a>
<a href="staff_management.php">staff Dashboard</a>
<a href="logout.php">Logout</a>
</div>

<div class="main">

<h1>Dashboard Overview</h1>

<div class="card-container">

<a href="admission/list.php" class="card blue">
<h3>Total Applications</h3>
<p><?php echo $total; ?></p>
</a>

<a href="admission/list.php?status=Pending" class="card orange">
<h3>Pending</h3>
<p><?php echo $pending; ?></p>
</a>

<a href="admission/list.php?status=Approved" class="card green">
<h3>Approved</h3>
<p><?php echo $approved; ?></p>
</a>

<a href="admission/list.php?status=Rejected" class="card red">
<h3>Rejected</h3>
<p><?php echo $rejected; ?></p>
</a>

<a href="admission/list.php?type=UG" class="card blue">
<h3>UG Applications</h3>
<p><?php echo $ug; ?></p>
</a>

<a href="admission/list.php?type=PG" class="card orange">
<h3>PG Applications</h3>
<p><?php echo $pg; ?></p>
</a>

<a href="admission/list.php?type=DIP" class="card green">
<h3>Diploma Applications</h3>
<p><?php echo $dip; ?></p>
</a>

<a href="admission/list.php?type=CERT" class="card red">
<h3>Certificate Applications</h3>
<p><?php echo $cert; ?></p>
</a>

</div>

</div>
</body>
</html>