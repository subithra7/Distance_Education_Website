<?php
session_start();
include "../../db.php";

$id = $_GET['id'];
$stmt = $conn->prepare("UPDATE records SET status='Approved' WHERE id=?");
$stmt->execute([$id]);

header("Location: list.php");