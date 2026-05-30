<?php
session_start();
include "../db.php";

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}

$result=$pdo->query("SELECT * FROM staff_users");
?>

<!DOCTYPE html>

<html>
<head>

<title>Manage Staff</title>

<link rel="stylesheet" href="assets/style.css">
<style>
html, body{
    height:100%;
    overflow:hidden;
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

<div class="back manage-staff-page">

<h1>Manage Staff</h1>

<div class="manage-staff-card">

<a class="staff-create-btn" href="create_staff.php">Create New Staff</a>

<table class="staff-table">
<tr>
<th>ID</th>
<th>Username</th>
<th>Department</th>
<th>Action</th>
</tr>

<?php while($row=$result->fetch(PDO::FETCH_ASSOC)) { ?>

<tr>

<td><?php echo $row['id']; ?></td>

<td><?php echo $row['username']; ?></td>

<td><?php echo $row['department']; ?></td>

<td>

<a class="btn" href="edit_staff.php?id=<?php echo $row['id']; ?>">Edit</a>

<a class="btn red" href="delete_staff.php?id=<?php echo $row['id']; ?>"
onclick="return confirm('Delete this staff?')">
Delete </a>

</td>

</tr>

<?php } ?>

</table>

</div>

</div>

</body>
</html>
