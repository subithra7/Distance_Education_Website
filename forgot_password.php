<?php
declare(strict_types=1);

session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once "db.php";
require_once "mail_config.php";

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');

    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){

        $msg = "Invalid email address.";

    } else {

        try{

            /*
            |--------------------------------------------------------------------------
            | CHECK USER
            |--------------------------------------------------------------------------
            */

            $check = $pdo->prepare("
                SELECT id
                FROM users
                WHERE email = :email
                AND is_verified = 1
                LIMIT 1
            ");

            $check->execute([
                ':email' => $email
            ]);

            $user = $check->fetch(PDO::FETCH_ASSOC);

            if($user){

                /*
                |--------------------------------------------------------------------------
                | GENERATE OTP
                |--------------------------------------------------------------------------
                */

                $otp = (string) random_int(100000, 999999);

                $expiry = date(
                    "Y-m-d H:i:s",
                    strtotime("+5 minutes")
                );

                /*
                |--------------------------------------------------------------------------
                | STORE OTP
                |--------------------------------------------------------------------------
                */

                $update = $pdo->prepare("
                    UPDATE users
                    SET otp = :otp,
                        otp_expires_at = :expiry
                    WHERE email = :email
                ");

                $update->execute([
                    ':otp' => $otp,
                    ':expiry' => $expiry,
                    ':email' => $email
                ]);

                $_SESSION['reset_email'] = $email;

                /*
                |--------------------------------------------------------------------------
                | SEND EMAIL
                |--------------------------------------------------------------------------
                */

                $mail = new PHPMailer(true);

                $mail->isSMTP();

                $mail->Host = MAIL_HOST;

                $mail->SMTPAuth = true;

                $mail->Username = MAIL_USER;

                $mail->Password = MAIL_PASS;

                $mail->SMTPSecure =
                    PHPMailer::ENCRYPTION_STARTTLS;

                $mail->Port = MAIL_PORT;

                $mail->setFrom(
                    MAIL_USER,
                    'Distance Education'
                );

                $mail->addAddress($email);

                $mail->isHTML(true);

                $mail->Subject = "Password Reset OTP";

                $mail->Body = "
                    <h2>Your OTP: {$otp}</h2>
                    <p>Valid for 5 minutes.</p>
                ";

                $mail->send();

                session_write_close();

            header("Location: reset_otp.php");

        exit;

            } else {

                $msg = "Email not found or not verified.";
            }

        }catch(Exception $e){

            $msg = $e->getMessage();
        }
    }
}
?>




<!DOCTYPE html>
<html>
<head>
<title>Forgot Password | Distance Education</title>

<style>


/* =========================
   HEADER
========================= */
.top-header{
    width:100%;

    background: #e8edf3;

    border-top:5px solid #0b5fa5;

    box-shadow:
    0 2px 10px rgba(0,0,0,0.08);
}

/* HEADER CONTAINER */
.header-container{
    width:100%;
    max-width:1400px;
    margin:auto;
}
/* =========================
   HEADER TOP
========================= */
.header-top{
    position:relative;

    display:flex;
    align-items:center;
    justify-content:center;
    font-family: "Times New Roman", Times, serif;
    padding:20px 0;
}

/* =========================
   LEFT LOGO
========================= */
.logo-section{
    position:absolute;
    left:20px;
    top:50%;
    transform:translateY(-50%);
    
}

.logo-section img{
    width:230px;
    height:auto;
    object-fit:contain;

}

/* =========================
   TITLE SECTION
========================= */
.title-section{
    text-align:center;
    font-family: "Times New Roman", Times, serif;
}

/* TAMIL TEXT */
.tamil-text{
    color:#0b5fa5;

    font-size:18px;
    font-weight:700;

    line-height:1.5;
    font-family: "Times New Roman", Times, serif;
    margin-bottom:5px;

    /* Similar Tamil font style */
}

/* ENGLISH TEXT */
.english-text{
    color:#083c72;

    font-size:20px;
    font-weight:800;

    line-height:1.3;
    font-family: "Times New Roman", Times, serif;
    /* Similar college style font */
}

/* SUB TEXT */
.sub-text{
    margin-top:8px;

    color:#444;

    font-size:12px;
    font-weight:600;
    font-family: "Times New Roman", Times, serif;
    line-height:1.6;
}

/* =========================
   NAVBAR
========================= */
.nav{
    width:100%;
    background:#0b5fa5;

    display:flex;
    justify-content:center;
    align-items:center;
    flex-wrap:wrap;

    gap:28px;

    padding:5px 5px;
}

.nav a{
    text-decoration:none;
    color:#ffffff;
    font-family: "Times New Roman", Times, serif;
    padding:7px 18px;

    font-size:15px;
    font-weight:600;

    border-radius:5px;

    transition:0.3s ease;
}

.nav a:hover,
.nav a.active{
    background:rgba(32, 223, 79, 0.15);
}


/* BANNER */

.banner{
min-height: calc(100vh - 160px);
background:url("image/back.jpeg") no-repeat center/cover;
display:flex;
align-items:center;
justify-content:center;
}


/* LOGIN BOX */

.login-box{
background:rgba(255,255,255,0.96);
width:380px;
padding:35px;
border-radius:6px;
box-shadow:0 10px 25px rgba(0,0,0,0.25);
}

.login-box h2{
margin-bottom:15px;
color:#0c1d42;
font-size:24px;
text-align:center;
font-family: "Times New Roman", Times, serif;
}

.login-box p.info{
font-size:14px;
color:#555;
margin-bottom:15px;
text-align:center;
font-family: "Times New Roman", Times, serif;
}

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

.login-box button{
background:#0c1d42;
color:white;
padding:12px;
width:100%;
border:none;
border-radius:4px;
font-size:16px;
font-family: "Times New Roman", Times, serif;
cursor:pointer;
}

.login-box button:hover{
background:#142d6b;
}

.error{
background:#ffe5e5;
color:#c62828;
padding:8px;
border-radius:4px;
font-family: "Times New Roman", Times, serif;
font-size:14px;
margin-bottom:10px;
text-align:center;
}

.back-link{
margin-top:15px;
text-align:center;
font-size:14px;
font-family: "Times New Roman", Times, serif;
}

.back-link a{
color:#1a73e8;
font-family: "Times New Roman", Times, serif;
text-decoration:none;
}

.back-link a:hover{
text-decoration:underline;
}


/* FOOTER */
footer{
    background:#075d9f;
    color:#ffffff;
    text-align:center;
    padding:20px;
    /* Similar college style font */
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

/* =========================
   LARGE LAPTOP
========================= */
@media(max-width:1200px){

.logo-section img{
    width:120px;
}

.english-text{
    font-size:22px;
}

}

/* =========================
   TABLET
========================= */
@media(max-width:900px){

.header-top{
    flex-direction:column;
    text-align:center;
}

.logo-section{
    position:static;
    transform:none;
    margin-bottom:10px;
}

.logo-section img{
    width:90px;
}

.tamil-text{
    font-size:15px;
}

.english-text{
    font-size:20px;
}

.sub-text{
    font-size:12px;
    padding:0 10px;
}

.nav{
    gap:10px;
    padding:10px;
}

.nav a{
    font-size:14px;
    padding:8px 12px;
}

.white-wrapper{
    width:92%;
}

}

/* =========================
   MOBILE
========================= */
@media(max-width:600px){

.logo-section img{
    width:70px;
}

.tamil-text{
    font-size:12px;
}

.english-text{
    font-size:16px;
    line-height:1.5;
}

.sub-text{
    font-size:11px;
    line-height:1.5;
}

.nav{
    gap:5px;
}

.nav a{
    font-size:12px;
    padding:7px 10px;
}

.login-content{
    padding:25px 15px;
}

.login-content h3{
    font-size:18px;
}

.login-table td{
    display:block;
    width:100%;
    padding:5px 0;
}

button{
    max-width:100%;
}

}


</style>
</head>

<body>


<!-- HEADER -->

<header class="top-header">

    <div class="container">

        <div class="header-top">

            <!-- LEFT LOGO -->
            <div class="logo-section">

                <img src="image/Univ.png" alt="University Logo">

            </div>

            <!-- CENTER TEXT -->
            <div class="title-section">

                <div class="tamil-text">
                    சென்னை பல்கலைக்கழகம் – தொலைதூரக் கல்வி நிறுவனம்
                </div>

                <div class="english-text">
                    University of Madras – Institute of Distance Education
                </div>

                <div class="sub-text">
                    Affiliated to University of Madras | NAAC Accredited with Grade “A++”<br>
                    A Premier Distance Education Institution<br>
                    Chepauk Campus, Chennai – 600 005
                </div>

            </div>

        </div>

        <!-- NAVBAR -->
        <nav class="nav">

            <a class="active" href="index.php">Home</a>
            <a href="#">Contact Us</a>
            

        </nav>

    </div>

</header>


<!-- MAIN CONTENT -->

<div class="banner">

<div class="login-box">

<h2>Forgot Password</h2>
<p class="info">Enter your registered email to receive OTP</p>

<?php if ($msg): ?>
<div class="error"><?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>

<form method="post">
<input type="email" name="email" placeholder="Registered Email" required>
<button type="submit">Send OTP</button>
</form>

<div class="back-link">
<p><a href="login.php">← Back to Login</a></p>
</div>

</div>

</div>



<!-- FOOTER -->

<footer>

<div class="about-ide">

<h2>About the Institute of Distance Education</h2>

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