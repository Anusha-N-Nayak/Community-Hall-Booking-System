<?php
session_start();
include "db.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit();
}

// Dashboard counts
$halls = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM halls"));
$bookings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM bookings"));
$users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users"));
$feedback = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM feedback"));
?><!DOCTYPE html><html>
<head>
    <title>Admin Dashboard</title><style>
    * {
        box-sizing: border-box;
        font-family: Arial, sans-serif;
    }

    body {
        margin: 0;
        background: #f2f5f9;
    }

    header {
        background: #243b55;
        color: white;
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .container {
        padding: 25px;
    }

    .cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-top: 25px;
    }

    .card {
        background: white;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 2px 8px #ddd;
        text-align: center;
    }

    .card h2 {
        color: #243b55;
        font-size: 32px;
    }

    .menu {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 15px;
        margin-top: 30px;
    }

    .menu a {
        background: #243b55;
        color: white;
        padding: 18px;
        text-decoration: none;
        border-radius: 8px;
        text-align: center;
    }

    .menu a:hover {
        background: #41658a;
    }

    .logout {
        color: white;
        text-decoration: none;
        background: #d9534f;
        padding: 10px 16px;
        border-radius: 5px;
    }
</style>

</head><body><header>
    <h2>Admin Dashboard</h2><div>
    Welcome, <?php echo htmlspecialchars($_SESSION["admin_username"]); ?>
    &nbsp;
    <a class="logout" href="admin_logout.php">Logout</a>
</div>

</header><div class="container"><h2>Dashboard Overview</h2>

<div class="cards">

    <div class="card">
        <h3>Total Halls</h3>
        <h2><?php echo $halls["total"]; ?></h2>
    </div>

    <div class="card">
        <h3>Total Bookings</h3>
        <h2><?php echo $bookings["total"]; ?></h2>
    </div>

    <div class="card">
        <h3>Total Users</h3>
        <h2><?php echo $users["total"]; ?></h2>
    </div>

    <div class="card">
        <h3>Total Feedback</h3>
        <h2><?php echo $feedback["total"]; ?></h2>
    </div>

</div>

<h2>Manage System</h2>

<div class="menu">

    <a href="admin_halls.php">Manage Halls</a>

    <a href="admin_bookings.php">Manage Bookings</a>

    <a href="admin_users.php">Manage Users</a>

    <a href="admin_feedback.php">Manage Feedback</a>

    <a href="admin_maintenance.php">Maintenance Complaints</a>

    <a href="admin_profile.php">Admin Profile</a>

</div>

</div></body>
</html>