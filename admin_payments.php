<?php
include "db.php";

// Get all payment details
$sql = "SELECT
        payments.payment_id,
        payments.booking_id,
        users.name,
        halls.hall_name,
        payments.amount,
        payments.payment_method,
        payments.payment_status,
        payments.transaction_id,
        payments.paid_at
        FROM payments
        JOIN bookings ON payments.booking_id = bookings.booking_id
        JOIN users ON bookings.user_id = users.user_id
        JOIN halls ON bookings.hall_id = halls.hall_id
        ORDER BY payments.paid_at DESC";

$result = mysqli_query($conn, $sql);

$payment_list = [];

while ($row = mysqli_fetch_assoc($result)) {
    $payment_list[] = $row;
}

// Send payment details to frontend
header("Content-Type: application/json");
echo json_encode($payment_list);
?>