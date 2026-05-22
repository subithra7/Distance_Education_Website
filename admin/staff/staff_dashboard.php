<?php
session_start();

if(!isset($_SESSION['staff'])){
    header("Location: staff_login.php");
    exit();
}

$dept = $_SESSION['course_type'] ?? '';
?>

<h2>Welcome Staff</h2>

<p>Your Department : <?php echo $dept; ?></p>

<a href="students.php">View Students</a>