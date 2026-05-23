<?php
session_start();
include "../../db.php";

$id = $_GET['id'];
$stmt = $pdo->prepare("UPDATE records SET status='Rejected' WHERE id=?");
$stmt->execute([$id]);

header("Location: list.php");