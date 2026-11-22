
<?php
session_start();
include "db.php";

// Check whether the user is logged in
if (!isset($_SESSION["user_id"])) {
    die("Please login first.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = (int)$_SESSION["user_id"];
    $hall_id = (int)($_POST["hall_id"] ?? 0);
    $event_name = trim($_POST["event_name"] ?? "");
    $event_date = $_POST["event_date"] ?? "";
    $start_time = $_POST["start_time"] ?? "";
    $end_time = $_POST["end_time"] ?? "";
    $guest_count = (int)($_POST["guest_count"] ?? 0);
    $total_amount = (float)($_POST["total_amount"] ?? 0);
    // Check whether the hall is available for the selected date

$hall_sql = "SELECT hall_status, deactivation_date
             FROM halls
             WHERE hall_id = ?";

$hall_stmt = mysqli_prepare($conn, $hall_sql);

mysqli_stmt_bind_param($hall_stmt, "i", $hall_id);

mysqli_stmt_execute($hall_stmt);

$hall_result = mysqli_stmt_get_result($hall_stmt);
$hall = mysqli_fetch_assoc($hall_result);

mysqli_stmt_close($hall_stmt);

if (
    !$hall ||
    strtolower(trim($hall["hall_status"] ?? "")) === "inactive" ||
    (
        !empty($hall["deactivation_date"]) &&
        $event_date >= $hall["deactivation_date"]
    )
) {
    die("Sorry! This hall is unavailable for the selected date.");
}
    if ($hall_id <= 0) {
        die("Invalid hall selected.");
    }

    // CHECK WHETHER THE HALL EXISTS AND IS ACTIVE
    $hall_sql = "SELECT hall_id, hall_status
                 FROM halls
                 WHERE hall_id = ?";

    $hall_stmt = mysqli_prepare($conn, $hall_sql);

    mysqli_stmt_bind_param($hall_stmt, "i", $hall_id);
    mysqli_stmt_execute($hall_stmt);

    $hall_result = mysqli_stmt_get_result($hall_stmt);
    $hall = mysqli_fetch_assoc($hall_result);

    mysqli_stmt_close($hall_stmt);

    if (!$hall) {
        die("Hall not found.");
    }

    if (strcasecmp(trim($hall["hall_status"] ?? ""), "Inactive") === 0) {
        die("<h2>Booking Unavailable!</h2>
             <p>Sorry! This hall is temporarily unavailable due to construction or maintenance.</p>
             <p>Please select another hall.</p>");
    }

    // Check whether the end time is after the start time
    if ($end_time <= $start_time) {
        die("Please enter a valid event time.");
    }

    // Check whether the selected hall is under maintenance
    $maintenance_sql = "SELECT maintenance_id, reason
                        FROM hall_maintenance
                        WHERE hall_id = ?
                        AND maintenance_date = ?";

    $maintenance_stmt = mysqli_prepare($conn, $maintenance_sql);

    mysqli_stmt_bind_param(
        $maintenance_stmt,
        "is",
        $hall_id,
        $event_date
    );

    mysqli_stmt_execute($maintenance_stmt);

    $maintenance_result = mysqli_stmt_get_result($maintenance_stmt);

    if (mysqli_num_rows($maintenance_result) > 0) {

        $maintenance_row = mysqli_fetch_assoc($maintenance_result);

        $reason = $maintenance_row["reason"] ?? "";

        echo "<h2>Booking Unavailable!</h2>";

        echo "<p>Sorry! This hall is under maintenance on "
             . htmlspecialchars($event_date) . ".</p>";

        if (!empty($reason)) {
            echo "<p>Reason: "
                 . htmlspecialchars($reason) . "</p>";
        }

        echo "<p>Please select another date.</p>";

        mysqli_stmt_close($maintenance_stmt);
        exit;
    }

    mysqli_stmt_close($maintenance_stmt);

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
