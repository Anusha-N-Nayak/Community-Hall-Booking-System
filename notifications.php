<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"])) {
    die("Please login first.");
}

$user_id = $_SESSION["user_id"];

$sql = "SELECT bookings.booking_id,
               bookings.event_name,
               bookings.event_date,
               bookings.booking_status,
               halls.hall_name,
               (SELECT payments.payment_status
                FROM payments
                WHERE payments.booking_id = bookings.booking_id
                ORDER BY payments.payment_id DESC
                LIMIT 1) AS payment_status
        FROM bookings
        JOIN halls ON bookings.hall_id = halls.hall_id
        WHERE bookings.user_id = ?
        ORDER BY bookings.created_at DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?><!DOCTYPE html><html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0"><title>My Notifications</title><style>
body {
    font-family: Arial, sans-serif;
    background: #f2f4f8;
    padding: 25px;
}

.container {
    max-width: 700px;
    margin: auto;
    background: white;
    padding: 25px;
    border-radius: 10px;
}

h2 {
    text-align: center;
    color: #142d4e;
    margin-bottom: 25px;
}

.notification {
    background: #f2f5f9;
    padding: 18px;
    margin-bottom: 18px;
    border-left: 5px solid #1e6091;
    border-radius: 8px;
}

.notification h3 {
    color: #142d4e;
    margin-bottom: 10px;
}

.notification p {
    color: #555;
    line-height: 1.5;
}

.empty {
    text-align: center;
    color: #777;
}

.back {
    display: block;
    text-align: center;
    margin-top: 25px;
    text-decoration: none;
    color: #1e6091;
    font-weight: bold;
}
</style>
</head>
<body>

<a href="dashboard.php" style="
    display:inline-block;
    padding:10px 20px;
    background:#243b55;
    color:white;
    text-decoration:none;
    border-radius:5px;
    margin-bottom:20px;">
    ← Back to Dashboard
</a>

<div class="container">
    <h2>🔔 My Notifications</h2><?php
$notification_count = 0;

while ($row = mysqli_fetch_assoc($result)) {

    $booking_status = strtolower($row["booking_status"] ?? "");
    $payment_status = strtolower($row["payment_status"] ?? "");

    if ($booking_status == "confirmed") {
?><div class="notification">
    <h3>🔔 Booking Confirmed!</h3>
    <p>Your booking for
    <b><?php echo htmlspecialchars($row["hall_name"]); ?></b>
    has been confirmed.</p>
    <p>Booking ID: <?php echo $row["booking_id"]; ?></p>
</div>

<?php
        $notification_count++;
    }

    if ($payment_status == "successful") {
?><div class="notification">
    <h3>💳 Payment Successful!</h3>
    <p>Your payment for
    <b><?php echo htmlspecialchars($row["event_name"]); ?></b>
    has been completed successfully.</p>
    <p>Booking ID: <?php echo $row["booking_id"]; ?></p>
</div>

<?php
        $notification_count++;
    }

    if (
        $booking_status == "confirmed" &&
        !empty($row["event_date"]) &&
        strtotime($row["event_date"]) >= strtotime(date("Y-m-d"))
    ) {
?><div class="notification">
    <h3>📅 Upcoming Event!</h3>
    <p>Your event at
    <b><?php echo htmlspecialchars($row["hall_name"]); ?></b>
    is scheduled for
    <b><?php echo htmlspecialchars($row["event_date"]); ?></b>.</p>
</div>

<?php
        $notification_count++;
    }
}

if ($notification_count == 0) {
    echo '<p class="empty">No new notifications available.</p>';
}
?>


</div></body>
</html>