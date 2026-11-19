<?php
include "db.php";

if (isset($_GET["hall_id"]) && isset($_GET["event_date"])) {

    $hall_id = $_GET["hall_id"];
    $event_date = $_GET["event_date"];

    $sql = "SELECT * FROM bookings
            WHERE hall_id = ?
            AND event_date = ?
            AND booking_status = 'Confirmed'";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "is", $hall_id, $event_date);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        echo "Hall is already booked on this date.";
    } else {
        echo "Hall is available.";
    }

} else {
    echo "Please provide hall ID and event date.";
}
?>