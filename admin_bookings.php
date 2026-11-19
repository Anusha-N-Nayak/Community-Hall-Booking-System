<?php
include "db.php";

// Get all booking details
$sql = "SELECT 
        bookings.booking_id,
        users.name,
        halls.hall_name,
        bookings.event_name,
        bookings.event_date,
        bookings.start_time,
        bookings.end_time,
        bookings.guest_count,
        bookings.total_amount,
        bookings.booking_status
        FROM bookings
        JOIN users ON bookings.user_id = users.user_id
        JOIN halls ON bookings.hall_id = halls.hall_id
        ORDER BY bookings.created_at DESC";

$result = mysqli_query($conn, $sql);

$booking_list = [];

while ($row = mysqli_fetch_assoc($result)) {
    $booking_list[] = $row;
}

// Send booking details to frontend
header("Content-Type: application/json");
echo json_encode($booking_list);
?>