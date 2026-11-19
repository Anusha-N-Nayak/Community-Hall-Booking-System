<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"])) {
    die("Please login first.");
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    die("Invalid request.");
}

$user_id = $_SESSION["user_id"];
$booking_id = (int)$_POST["booking_id"];

// Check booking
$sql = "SELECT * FROM bookings
        WHERE booking_id = ? AND user_id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $booking_id, $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$booking = mysqli_fetch_assoc($result);

if (!$booking || $booking["booking_status"] != "Pending Payment") {
    die("Invalid booking or payment already completed.");
}

$amount = $booking["total_amount"];
$payment_method = "Demo";
$payment_status = "Successful";
$transaction_id = "DEMO-" . strtoupper(bin2hex(random_bytes(6)));

mysqli_begin_transaction($conn);

try {

    // Save payment
    $payment_sql = "INSERT INTO payments
        (booking_id, amount, payment_method,
         payment_status, transaction_id)
        VALUES (?, ?, ?, ?, ?)";

    $payment_stmt = mysqli_prepare($conn, $payment_sql);

    mysqli_stmt_bind_param(
        $payment_stmt,
        "idsss",
        $booking_id,
        $amount,
        $payment_method,
        $payment_status,
        $transaction_id
    );

    if (!mysqli_stmt_execute($payment_stmt)) {
        throw new Exception("Payment could not be saved.");
    }

    // Confirm booking after payment
    $update_sql = "UPDATE bookings
                   SET booking_status = 'Confirmed'
                   WHERE booking_id = ? AND user_id = ?";

    $update_stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($update_stmt, "ii", $booking_id, $user_id);

    if (!mysqli_stmt_execute($update_stmt)) {
        throw new Exception("Booking confirmation failed.");
    }

    mysqli_commit($conn);

    echo "<h1>Payment Successful!</h1>";
    echo "<h2>Booking Confirmed!</h2>";
    echo "<p>Booking ID: " . $booking_id . "</p>";
    echo "<p>Amount Paid: ₹" . number_format($amount, 2) . "</p>";
    echo "<p>Transaction ID: " . htmlspecialchars($transaction_id) . "</p>";

    echo '<a href="my_bookings.php">View My Bookings</a>';

} catch (Exception $e) {

    mysqli_rollback($conn);
    echo "Payment failed: " . htmlspecialchars($e->getMessage());

}
?>