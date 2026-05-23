<?php
session_start();
require "../db.php";

if($_SERVER["REQUEST_METHOD"]=="POST"){

    $username = $_POST['username'];
    $password = $_POST['password'];

    // 🔥 MYSQLI QUERY (FIXED)
    $stmt = $pdo->prepare("SELECT * FROM lsc_users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if($user && $password == $user['password']){

        $_SESSION['lsc_code'] = $user['lsc_code'];
        $_SESSION['lsc_name'] = $user['lsc_name'];

        header("Location: ../ap1.php");
        exit;

    } else {
        echo "Invalid Login";
    }
}
?>

<form method="post">
    <h2>LSC Login</h2>
    <input type="text" name="username" placeholder="Username" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>
    <button type="submit">Login</button>
</form>