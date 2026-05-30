<?php
session_start();
include "../db.php";

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}

if(isset($_POST['create'])){

    $username   = trim($_POST['username']);
    $password   = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $department = $_POST['department'];

    if(empty($username) || empty($department)){
        echo "<script>alert('All fields are required');</script>";
    } else {

        // ✅ IF ADMIN USER
        if($department == "ADMIN"){

            $stmt = $pdo->prepare("INSERT INTO admin_users (username, password, department) VALUES (?, ?, ?)");

        } else {
            // ✅ NORMAL STAFF
            $stmt = $pdo->prepare("INSERT INTO staff_users (username, password, department) VALUES (?, ?, ?)");
        }

        if($stmt->execute([$username, $password, $department])){
            echo "<script>alert('User Created Successfully');</script>";
        } else {
            echo "<script>alert('Error creating user');</script>";
        }
    }
}
?>
<!DOCTYPE html>

<html>
<head>

<title>Create Staff</title>

<link rel="stylesheet" href="assets/style.css">
<style>html, body{
    height:100%;
    overflow:hidden;
}</style>
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

<a href="dashboard.php">Dashboard</a>

<a href="admission/list.php">Applications</a>

<a href="staff_management.php">Staff Management</a>

<a href="logout.php">Logout</a>

</div>

<div class="back create-staff-page">

<h1>Create Staff</h1>

<div class="staff-form-card">

<form method="POST">

<div class="staff-form-group">
<label>Username</label>
<input type="text" name="username" required>
</div>

<div class="staff-form-group">
<label>Password</label>
<input type="password" name="password" required>
</div>

<div class="staff-form-group">
<label>Department</label>

<select name="department">
      <option value="">Select Department</option>
<option value="UG">UG Application</option>
<option value="PG">PG Application</option>
<option value="DIP">Diploma Application</option>
<option value="CERT">Certificate Application</option>
<option value="SWA">Single Window</option>
<option value="ADMIN">Admin Users</option>
</select>

</div>

<button class="staff-btn" name="create">Create Staff</button>

</form>

<br>

<a href="manage_staff.php" class="staff-link-btn">View Staff List</a>

</div>
</div>

</body>
</html>