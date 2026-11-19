<?php
session_start();
include "db.php";

// Check whether the user is logged in
if (!isset($_SESSION["user_id"])) {
    die("Please login first.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = $_SESSION["user_id"];
    $hall_id = $_POST["hall_id"];
    $event_name = $_POST["event_name"];
    $event_date = $_POST["event_date"];
    $start_time = $_POST["start_time"];
    $end_time = $_POST["end_time"];
    $guest_count = $_POST["guest_count"];
    $total_amount = $_POST["total_amount"];

    // Check that the end time is after the start time
    if ($end_time <= $start_time) {
        die("Please enter a valid event time.");
    }

    // Check overlapping bookings
    $check_sql = "SELECT booking_id FROM bookings
                  WHERE hall_id = ?
                  AND event_date = ?
                  AND booking_status = 'Confirmed'
                  AND start_time < ?
                  AND end_time > ?";

    $check_stmt = mysqli_prepare($conn, $check_sql);

    mysqli_stmt_bind_param(
        $check_stmt,
        "isss",
        $hall_id,
        $event_date,
        $end_time,
        $start_time
    );

    mysqli_stmt_execute($check_stmt);

    $result = mysqli_stmt_get_result($check_stmt);

    if (mysqli_num_rows($result) > 0) {

        echo "Sorry! This hall is already booked during this time.";

    } else {

        // Save booking details
        $sql = "INSERT INTO bookings
                (user_id, hall_id, event_name, event_date,
                 start_time, end_time, guest_count,
                 total_amount, booking_status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending Payment')";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "iissssid",
            $user_id,
            $hall_id,
            $event_name,
            $event_date,
            $start_time,
            $end_time,
            $guest_count,
            $total_amount
        );

        if (mysqli_stmt_execute($stmt)) {

    $booking_id = mysqli_insert_id($conn);

    echo "<h2>Booking successful!</h2>";

    echo '<form action="payment.php" method="POST">';

    echo '<input type="hidden" name="booking_id" value="' . $booking_id . '">';

    echo '<button type="submit">Proceed to Payment</button>';

    echo '</form>';

} else {
    echo "Booking failed: " . mysqli_error($conn);
}

        mysqli_stmt_close($stmt);
    }

    mysqli_stmt_close($check_stmt);
}
?>