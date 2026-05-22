<?php
include "../db.php";

$id=$_GET['id'];

$stmt = $conn->prepare("DELETE FROM staff_users WHERE id=?");
$stmt->execute([$id]);

echo "<script>
alert('Staff Deleted Successfully');
window.location='manage_staff.php';
</script>";
?>