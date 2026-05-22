<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
/* WRAPPER STRUCTURE */
.wrapper {
    display: flex;
    min-height: 100vh;
}
.main {
    flex: 1;
    padding: 20px;
}
/* SIDEBAR */
.sidebar {
    width: 230px;
    background: #1e293b;
    color: white;
    padding-top: 20px;
    flex-shrink: 0;
}
.sidebar a {
    display: block;
    padding: 12px 20px;
    color: white;
    text-decoration: none;
    font-size: 15px;
    transition: 0.3s;
    border-left: 4px solid transparent; /* for hover */
}
.sidebar a i {
    width: 25px;
    text-align: center;
    margin-right: 8px;
}
.sidebar a:hover, .sidebar a.active {
    background: #334155;
    border-left: 4px solid #3b82f6;
}
/* RESPONSIVE */
@media (max-width: 768px) {
    .wrapper {
        flex-direction: column;
    }
    .sidebar {
        width: 100%;
        min-height: auto;
        display: flex;
        flex-wrap: wrap;
        padding: 5px 0;
    }
    .sidebar a {
        flex: 1;
        min-width: 80px;
        text-align: center;
        border-left: none;
        padding: 10px;
    }
    .sidebar a i {
        display: block;
        margin: 0 auto 5px auto;
    }
    .sidebar a:hover, .sidebar a.active {
        border-bottom: 4px solid #3b82f6;
        border-left: none;
    }
}
</style>

<div class="sidebar">
    <a href="dashboard.php" class="<?php echo ($currentPage == 'dashboard.php') ? 'active' : ''; ?>"><i class="fa fa-home"></i> Dashboard</a>
    <a href="new_application1.php" class="<?php echo ($currentPage == 'new_application1.php') ? 'active' : ''; ?>"><i class="fa fa-plus"></i> New Application</a>
    <a href="reintimation.php" class="<?php echo ($currentPage == 'reintimation.php') ? 'active' : ''; ?>"><i class="fa fa-refresh"></i> Reintimation</a>
    <a href="payment.php" class="<?php echo ($currentPage == 'payment.php') ? 'active' : ''; ?>"><i class="fa fa-credit-card"></i> Payment</a>
    <a href="list.php" class="<?php echo ($currentPage == 'list.php') ? 'active' : ''; ?>"><i class="fa fa-list"></i> Application List</a>
    <a href="logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a>
</div>
