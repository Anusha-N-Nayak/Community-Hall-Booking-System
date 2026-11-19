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

$sql = "SELECT * FROM bookings
        WHERE booking_id = ? AND user_id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $booking_id, $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$booking = mysqli_fetch_assoc($result);

if (!$booking) {
    die("Booking not found.");
}

if ($booking["booking_status"] != "Pending Payment") {
    die("This booking is not awaiting payment.");
}

$amount = $booking["total_amount"];

$qr_data = "DEMO PAYMENT | Booking ID: " . $booking_id .
           " | Amount: Rs. " . $amount;

$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=" .
          urlencode($qr_data);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Hall Booking Payment</title>

    <style>
        body {
            font-family: Arial;
            background: #f2f5f9;
            text-align: center;
            padding: 40px;
        }

        .box {
            background: white;
            padding: 30px;
            max-width: 400px;
            margin: auto;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        img {
            width: 220px;
            height: 220px;
            margin: 20px;
        }

        button {
            background: #198754;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }
    </style>
</head>

<body>

<div class="box">

    <h2>Complete Your Payment</h2>

    <p>Booking ID: <?php echo $booking_id; ?></p>

    <h3>Amount: ₹<?php echo number_format($amount, 2); ?></h3>

    <p>Scan this Demo QR Code</p>

    <img src="<?php echo htmlspecialchars($qr_url); ?>"
         alt="Demo QR Code">

    <form action="payment_process.php" method="POST">

        <input type="hidden" name="booking_id"
               value="<?php echo $booking_id; ?>">

        <br><br>

        <button type="submit">Make Payment</button>

    </form>

    <p>Demo payment only. No real money will be transferred.</p>

</div>

</body>
</html>