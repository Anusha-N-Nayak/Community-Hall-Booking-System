<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"])) {
    die("Please login first.");
}

$user_id = $_SESSION["user_id"];

$sql = "SELECT bookings.*, halls.hall_name,
        (SELECT payment_status FROM payments
         WHERE payments.booking_id = bookings.booking_id
         ORDER BY payment_id DESC LIMIT 1) AS payment_status
        FROM bookings
        JOIN halls ON bookings.hall_id = halls.hall_id
        WHERE bookings.user_id = ?
        ORDER BY bookings.created_at DESC";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $user_id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$bookings = [];

while ($row = mysqli_fetch_assoc($result)) {
    $bookings[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Bookings</title>

    <style>
        body {
            font-family: Arial;
            background: #f2f4f8;
            padding: 25px;
        }

        .booking {
            background: white;
            padding: 20px;
            margin: 20px auto;
            max-width: 550px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        h2 {
            color: #263b5e;
        }
    </style>
</head>

<body>

<h1 align="center">My Bookings</h1>

<?php
if (count($bookings) > 0) {

    foreach ($bookings as $booking) {
?>

<div class="booking">

    <h2><?php echo htmlspecialchars($booking['hall_name']); ?></h2>

    <p><b>Booking ID:</b>
        <?php echo htmlspecialchars($booking['booking_id']); ?>
    </p>

    <p><b>Event:</b>
        <?php echo htmlspecialchars($booking['event_name']); ?>
    </p>

    <p><b>Date:</b>
        <?php echo htmlspecialchars($booking['event_date']); ?>
    </p>

    <p><b>Start Time:</b>
        <?php echo htmlspecialchars($booking['start_time']); ?>
    </p>

    <p><b>End Time:</b>
        <?php echo htmlspecialchars($booking['end_time']); ?>
    </p>

    <p><b>Guests:</b>
        <?php echo htmlspecialchars($booking['guest_count']); ?>
    </p>

    <p><b>Booking Status:</b>
        <?php echo htmlspecialchars($booking['booking_status']); ?>
    </p>

    <p><b>Payment Status:</b>
        <?php echo htmlspecialchars($booking['payment_status'] ?? 'Not Paid'); ?>
    </p>

    <p><b>Amount:</b>
        ₹<?php echo htmlspecialchars($booking['total_amount']); ?>
    </p>
    <?php if ($booking['booking_status'] == 'Confirmed') { ?>

<a href="feedback.html?booking_id=<?php echo $booking['booking_id']; ?>">
    Submit Feedback
</a>

<?php } ?>

</div>

<?php
    }

} else {
    echo "<p align='center'>No bookings found.</p>";
}
?>

</body>
</html>

<?php
mysqli_stmt_close($stmt);
?>