<?php
session_start();
include "db.php";

// Check admin login
if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit();
}

$hall_id = (int)($_GET["hall_id"] ?? 0);

if ($hall_id <= 0) {
    die("Invalid hall selected.");
}

// Get hall name
$hall_sql = "SELECT hall_name FROM halls WHERE hall_id = ?";
$hall_stmt = mysqli_prepare($conn, $hall_sql);
mysqli_stmt_bind_param($hall_stmt, "i", $hall_id);
mysqli_stmt_execute($hall_stmt);
$hall_result = mysqli_stmt_get_result($hall_stmt);
$hall = mysqli_fetch_assoc($hall_result);
mysqli_stmt_close($hall_stmt);

if (!$hall) {
    die("Hall not found.");
}

// Get bookings for this hall
$sql = "SELECT booking_id, user_id, event_name, event_date,
               start_time, end_time, guest_count,
               total_amount, booking_status
        FROM bookings
        WHERE hall_id = ?
        ORDER BY event_date DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $hall_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hall Bookings</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f4f8;
            padding: 25px;
        }
        .container {
            background: white;
            padding: 25px;
            border-radius: 10px;
            overflow-x: auto;
        }
        h2 {
            color: #243b64;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
        }
        th {
            background: #243b64;
            color: white;
        }
        a {
            color: #243b64;
        }
    </style>
</head>
<body>

<div class="container">

    <h2>Bookings for <?php echo htmlspecialchars($hall["hall_name"]); ?></h2>

    <table>
        <tr>
            <th>Booking ID</th>
            <th>User ID</th>
            <th>Event</th>
            <th>Event Date</th>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Guests</th>
            <th>Amount</th>
            <th>Status</th>
        </tr>

        <?php if (mysqli_num_rows($result) > 0) { ?>
            <?php while ($booking = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($booking["booking_id"]); ?></td>
                    <td><?php echo htmlspecialchars($booking["user_id"]); ?></td>
                    <td><?php echo htmlspecialchars($booking["event_name"]); ?></td>
                    <td><?php echo htmlspecialchars($booking["event_date"]); ?></td>
                    <td><?php echo htmlspecialchars($booking["start_time"]); ?></td>
                    <td><?php echo htmlspecialchars($booking["end_time"]); ?></td>
                    <td><?php echo htmlspecialchars($booking["guest_count"]); ?></td>
                    <td>₹<?php echo htmlspecialchars($booking["total_amount"]); ?></td>
                    <td><?php echo htmlspecialchars($booking["booking_status"]); ?></td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <td colspan="9">No bookings found for this hall.</td>
            </tr>
        <?php } ?>

    </table>

    <p><a href="admin_dashboard.php">Back to Admin Dashboard</a></p>

</div>

</body>
</html>

<?php
mysqli_stmt_close($stmt);
?>