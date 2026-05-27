<?php

declare(strict_types=1);

session_start();

include "db.php";

/*
|--------------------------------------------------------------------------
| SECURITY HEADERS
|--------------------------------------------------------------------------
*/

header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Cache-Control: no-store, no-cache, must-revalidate");

/*
|--------------------------------------------------------------------------
| CSRF TOKEN
|--------------------------------------------------------------------------
*/

if(empty($_SESSION['csrf_token'])){

    $_SESSION['csrf_token'] =
        bin2hex(random_bytes(32));
}

/*
|--------------------------------------------------------------------------
| SESSION CHECK
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['reset_email'])) {

    header("Location: login.php");
    exit;
}

$error = "";

$email = $_SESSION['reset_email'];

/*
|--------------------------------------------------------------------------
| OTP ATTEMPT LIMIT
|--------------------------------------------------------------------------
*/

if(!isset($_SESSION['otp_attempts'])){

    $_SESSION['otp_attempts'] = 0;
}

if($_SESSION['otp_attempts'] >= 5){

    $error = "Too many invalid attempts.";
}

/*
|--------------------------------------------------------------------------
| OTP TIMER
|--------------------------------------------------------------------------
*/

$remaining = 240;

if(isset($_SESSION['otp_timer'])){

    $elapsed = time() - $_SESSION['otp_timer'];

    $remaining = 240 - $elapsed;

    if($remaining < 0){

        $remaining = 0;
    }
}

/*
|--------------------------------------------------------------------------
| VERIFY OTP
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && empty($error)
) {

    /*
    |--------------------------------------------------------------------------
    | CSRF VALIDATION
    |--------------------------------------------------------------------------
    */

    if(
        !isset($_POST['csrf_token']) ||
        !hash_equals(
            $_SESSION['csrf_token'],
            $_POST['csrf_token']
        )
    ){

        die("Invalid request.");
    }

    $otp = trim($_POST['otp'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | OTP VALIDATION
    |--------------------------------------------------------------------------
    */

    if(!preg_match('/^[0-9]{6}$/', $otp)){

        $error = "Enter valid 6 digit OTP.";

    } else {

        try{

            $stmt = $pdo->prepare("
                SELECT otp, otp_expires_at
                FROM users
                WHERE email = ?
                LIMIT 1
            ");

            $stmt->execute([$email]);

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if($row){

                /*
                |--------------------------------------------------------------------------
                | CHECK OTP EXPIRY
                |--------------------------------------------------------------------------
                */

                if(
                    strtotime($row['otp_expires_at'])
                    < time()
                ){

                    $error =
                        "OTP expired. Please resend.";

                }

                /*
                |--------------------------------------------------------------------------
                | VERIFY OTP
                |--------------------------------------------------------------------------
                */

                elseif(
                    password_verify(
                        $otp,
                        $row['otp']
                    )
                ){

                    session_regenerate_id(true);

                    $_SESSION['otp_verified'] = true;

                    $_SESSION['otp_attempts'] = 0;

                    header(
                        "Location: reset_password.php"
                    );

                    exit;

                } else {

                    $_SESSION['otp_attempts']++;

                    $error = "Invalid OTP.";
                }

            } else {

                $error =
                    "Session expired. Please try again.";
            }

        }catch(PDOException $e){

            error_log($e->getMessage());

            $error =
                "System temporarily unavailable.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>

<title>Verify OTP | Distance Education</title>

<link rel="stylesheet" href="style.css">

<style>

:root{
    --primary:#0b5fa5;
    --primary-dark:#083c72;
    --secondary:#075d9f;
    --white:#ffffff;
    --shadow:0 4px 15px rgba(0,0,0,0.12);
}

/* RESET */

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family: "Times New Roman", Times, serif;
}

/* HEADER */

.top-header{
    width:100%;
    background:rgb(216,230,220);
    border-top:7px solid var(--primary);
    box-shadow:var(--shadow);
}

.container{
    width:100%;
    max-width:1400px;
    margin:auto;
    padding:0 15px;
}

.header-top{
    position:relative;
    display:flex;
    align-items:center;
    justify-content:center;
    font-family: "Times New Roman", Times, serif;
    min-height:180px;
    padding:20px;
    text-align:center;
}

.logo-section{
    position:absolute;
    left:20px;
    top:50%;
    transform:translateY(-50%);
}

.logo-section img{
    width:110px;
    height:auto;
}

.title-section{
    flex:1;
    font-family: "Times New Roman", Times, serif;
}

.tamil-text{
    font-family: "Times New Roman", Times, serif;
    color:var(--primary);
    font-size:clamp(13px,2vw,22px);
    font-weight:700;
}

.english-text{
    font-family: "Times New Roman", Times, serif;
    color:var(--primary-dark);
    font-size:clamp(18px,3vw,32px);
    font-weight:800;
}

.sub-text{
    font-family: "Times New Roman", Times, serif;
    margin-top:8px;
    color:#444;
    font-size:clamp(11px,1.5vw,14px);
    font-weight:600;
    line-height:1.6;
}

/* NAVBAR */

.navbar{
    width:100%;
    background:#005ea6;
}

.nav-container{
    width:100%;
    max-width:1400px;
    margin:auto;
    display:flex;
    justify-content:center;
}

.nav-links{
    display:flex;
    gap:35px;
    padding:18px 0;
    flex-wrap:wrap;
}

.nav-links a{
    font-family: "Times New Roman", Times, serif;
    color:#fff;
    text-decoration:none;
    font-size:16px;
    font-weight:600;
}

/* BANNER */

.banner{

    min-height:calc(100vh - 140px);
    background:url("image/back.jpeg")
    no-repeat center center/cover;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:20px;
}

/* OTP BOX */

.login-box{
    background:rgba(255,255,255,0.96);
    width:100%;
    max-width:420px;
    padding:35px;
    border-radius:8px;
    box-shadow:0 10px 25px rgba(0,0,0,0.25);
}

.login-box h2{
    margin-bottom:10px;
    color:#0c1d42;
    font-size:24px;
    text-align:center;
    font-family: "Times New Roman", Times, serif;
}

.login-box p.info{
    font-size:14px;
    font-family: "Times New Roman", Times, serif;
    color:#555;
    margin-bottom:15px;
    text-align:center;
}

/* TIMER */

#timer-container{
    font-family: "Times New Roman", Times, serif;
    text-align:center;
    margin-bottom:15px;
}

#timer{
    font-family: "Times New Roman", Times, serif;
    color:red;
    font-size:22px;
    font-weight:bold;
    text-align:center;
}

/* INPUT */

.login-box input{
    width:100%;
    padding:12px;
    margin:12px 0;
    border:1px solid #ccc;
    border-radius:4px;
    font-size:15px;
    font-family: "Times New Roman", Times, serif;
}

.login-box input:focus{
    border-color:#0c1d42;
    outline:none;
    font-family: "Times New Roman", Times, serif;
}

/* BUTTON */

.login-box button{
    background:#0c1d42;
    color:white;
    padding:12px;
    width:100%;
    border:none;
    border-radius:4px;
    font-size:16px;
    cursor:pointer;
    font-family: "Times New Roman", Times, serif;
}

.login-box button:hover{
    background: #142d6b;
}

button:disabled{
    opacity:0.6;
    cursor:not-allowed;
}

/* ERROR */

.error{
    background: #ffe5e5;
    color: #c62828;
    padding:10px;
    border-radius:4px;
    font-size:14px;
    margin-bottom:12px;
    text-align:center;
    font-family: "Times New Roman", Times, serif;
}

/* RESEND */

#resendBtn{
    display:block;
    text-align:center;
    margin-top:15px;
    color:#1a73e8;
    text-decoration:none;
    font-size:16px;
    font-family: "Times New Roman", Times, serif;
    font-weight:600;
    pointer-events:none;
    opacity:0.5;
}

#resendBtn:hover{
    text-decoration:underline;
    font-family: "Times New Roman", Times, serif;
}

/* FOOTER */

footer{
    background:var(--secondary);
    color:#fff;
    text-align:center;
    padding:25px 15px;
    font-family: "Times New Roman", Times, serif;
}

.about-ide{
    max-width:1100px;
    margin:auto;
    font-family: "Times New Roman", Times, serif;
}

.about-ide p{
    line-height:1.8;
    font-family: "Times New Roman", Times, serif;
}

/* MOBILE */

@media(max-width:768px){

    .header-top{
        flex-direction:column;
    }

    .logo-section{
        position:static;
        transform:none;
        margin-bottom:10px;
    }

    .logo-section img{
        width:75px;
    }
}

</style>

</head>

<body>

<header class="top-header">

<div class="container">

<div class="header-top">

<div class="logo-section">

<img
src="image/Univ.png"
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
Chepauk Campus, Chennai – 600 005
</div>

</div>

</div>

<nav class="navbar">

<div class="nav-container">

<div class="nav-links">

<a href="#">Home</a>
<a href="#">About Us</a>
<a href="#">Contact Us</a>
<a href="admin/login.php">Admin Panel</a>

</div>

</div>

</nav>

</div>

</header>

<div class="banner">

<div class="login-box">

<h2>Verify OTP</h2>

<p class="info">
Enter the OTP sent to your email
</p>

<div id="timer-container">

<div id="timer"></div>

</div>

<?php if(!empty($error)): ?>

<div class="error">

<?php echo htmlspecialchars(
    $error,
    ENT_QUOTES,
    'UTF-8'
); ?>

</div>

<?php endif; ?>

<form method="post" autocomplete="off">

<input
type="hidden"
name="csrf_token"
value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"
>

<input
type="text"
name="otp"
placeholder="Enter OTP"
maxlength="6"
inputmode="numeric"
pattern="[0-9]{6}"
required
>

<button type="submit">
Verify
</button>

</form>

<a
href="forgot_password.php"
id="resendBtn"
>
← Resend OTP
</a>

</div>

</div>

<script>

document.addEventListener(
    "DOMContentLoaded",
    function(){

    let timeLeft =
        <?php echo $remaining; ?>;

    const timer =
        document.getElementById("timer");

    const resendBtn =
        document.getElementById("resendBtn");

    const otpInput =
        document.querySelector(
            'input[name="otp"]'
        );

    const verifyButton =
        document.querySelector(
            'button[type="submit"]'
        );

    const countdown = setInterval(() => {

        let minutes =
            Math.floor(timeLeft / 60);

        let seconds =
            timeLeft % 60;

        seconds = seconds < 10
            ? "0" + seconds
            : seconds;

        timer.innerHTML =
            "OTP expires in: " +
            minutes +
            ":" +
            seconds;

        timeLeft--;

        if(timeLeft < 0){

            clearInterval(countdown);

            timer.innerHTML =
                "OTP expired.";

            otpInput.disabled = true;

            verifyButton.disabled = true;

            resendBtn.style.pointerEvents =
                "auto";

            resendBtn.style.opacity = "1";
        }

    }, 1000);

});

</script>

<footer>

<div class="about-ide">

<h2>
About the Institute of Distance Education
</h2>

<p>
The Institute of Correspondence Education (ICE), now called the
Institute of Distance Education (IDE), was established in 1981.
</p>

<p>
Having completed 43 years, IDE today is a mega institute with more than
one lakh learners.
</p>

<p>
IDE offers <strong>73 Programmes</strong> including UG, PG, Diploma and Certificate courses.
</p>

<p>
Admissions are open throughout the year in both Academic Year (July–June)
and Calendar Year (January–December).
</p>

<p>
69 Learner Support Centres have been established and online admission
facility has been introduced.
</p>

<p>© 2025 University of Madras. All Rights Reserved.</p>

</div>

</footer>

</body>
</html>