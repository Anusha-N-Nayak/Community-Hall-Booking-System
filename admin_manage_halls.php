<?php
session_start();
include "db.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.html");
    exit();
}

$sql = "SELECT * FROM halls ORDER BY hall_id DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Halls</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f5f9;
            padding: 25px;
        }

        h2 {
            color: #243b55;
        }

        table {
            width: 100%;
            background: white;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 14px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #243b55;
            color: white;
        }

        a {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: white;
            background: #243b55;
            padding: 10px 16px;
            border-radius: 5px;
        }
    </style>
</head>

<body>

<h2>Manage Community Halls</h2>

<table>
    <tr>
        <th>Hall ID</th>
        <th>Hall Name</th>
        <th>Location</th>
        <th>Owner ID</th>
    </tr>

    <?php while ($hall = mysqli_fetch_assoc($result)) { ?>

    <tr>
        <td><?php echo htmlspecialchars($hall["hall_id"]); ?></td>
        <td><?php echo htmlspecialchars($hall["hall_name"]); ?></td>
        <td><?php echo htmlspecialchars($hall["location"]); ?></td>
        <td><?php echo htmlspecialchars($hall["owner_id"]); ?></td>
    </tr>

    <?php } ?>

</table>

<a href="admin_dashboard.php">Back to Dashboard</a>

</body>
</html>