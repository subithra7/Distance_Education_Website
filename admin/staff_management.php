<?php
session_start();
require_once "../db.php";

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>

<html>
<head>
<title>Staff Management</title>
<link rel="stylesheet" href="assets/style.css">
</head>

<body>

<!-- HEADER -->

<header class="top-header">

    <div class="container">

        <div class="header-top">
        <div class="logo-section">

<img
    src="../image/Univ.png"
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

<div class="sidebar">
<h2>Admin Panel</h2>

<a href="dashboard.php">Dashboard</a> <a href="admission/list.php">Applications</a> <a href="staff_management.php">Staff Management</a> <a href="logout.php">Logout</a>

</div>

<div class="main">

<h1>Staff Management</h1>

<div class="card-container">

<a href="create_staff.php" class="card blue">
<h3>Create Staff</h3>
<p>Add new staff login</p>
</a>

<a href="manage_staff.php" class="card green">
<h3>Manage Staff</h3>
<p>Edit or delete staff accounts</p>
</a>

</div>

</div>

</body>
</html>
