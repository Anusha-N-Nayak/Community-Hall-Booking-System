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

echo '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>

    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #f1f4f8;
            padding: 20px;
        }

        .success-card {
            background: white;
            width: 100%;
            max-width: 500px;
            padding: 40px 30px;
            text-align: center;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.10);
        }

        .success-icon {
            width: 80px;
            height: 80px;
            line-height: 80px;
            margin: 0 auto 20px;
            border-radius: 50%;
            background: #dcfce7;
            color: #16a34a;
            font-size: 45px;
            font-weight: bold;
        }

        h1 {
            color: #166534;
            margin-bottom: 8px;
        }

        h2 {
            color: #142d4e;
            margin-bottom: 20px;
        }

        .details {
            background: #f1f5f9;
            padding: 18px;
            border-radius: 9px;
            text-align: left;
            margin: 20px 0;
            line-height: 2;
            overflow-wrap: anywhere;
        }

        .button {
            display: inline-block;
            width: 100%;
            padding: 14px;
            background: #142d4e;
            color: white;
            text-decoration: none;
            border-radius: 7px;
            font-weight: bold;
        }

        .button:hover {
            background: #2563eb;
        }

        .message {
            color: #555;
            line-height: 1.6;
        }
    </style>
</head>

<body>

<div class="success-card">

    <div class="success-icon">&#10003;</div>

    <h1>Payment Successful!</h1>

    <h2>Booking Confirmed!</h2>

    <p class="message">
        Your payment has been completed successfully.
        Your community hall booking is now confirmed.
    </p>

    <div class="details">

        <strong>Booking ID:</strong> ' . $booking_id . '<br>

        <strong>Amount Paid:</strong> ₹' . number_format($amount, 2) . '<br>

        <strong>Payment Method:</strong> ' . htmlspecialchars($payment_method) . '<br>

        <strong>Transaction ID:</strong> ' . htmlspecialchars($transaction_id) . '

    </div>

    <a href="my_bookings.php" class="button">
        View My Bookings
    </a>

</div>

</body>
</html>';


    

} catch (Exception $e) {

    mysqli_rollback($conn);
    echo "Payment failed: " . htmlspecialchars($e->getMessage());

}
?>