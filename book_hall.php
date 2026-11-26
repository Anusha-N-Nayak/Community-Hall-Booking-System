
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
    $customer_name = trim($_POST["customer_name"] ?? "");
$mobile = trim($_POST["mobile"] ?? "");
$email = trim($_POST["email"] ?? "");
    // Check whether the hall is available for the selected date

$hall_sql = "SELECT hall_status, deactivation_date, capacity
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

    echo '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Hall Unavailable</title>

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

            .message-card {
                background: white;
                width: 100%;
                max-width: 480px;
                padding: 40px 30px;
                text-align: center;
                border-radius: 15px;
                box-shadow: 0 5px 20px rgba(0,0,0,0.10);
            }

            .warning-icon {
                width: 75px;
                height: 75px;
                line-height: 75px;
                margin: 0 auto 20px;
                border-radius: 50%;
                background: #fee2e2;
                color: #dc2626;
                font-size: 42px;
                font-weight: bold;
            }

            h2 {
                color: #b91c1c;
            }

            p {
                color: #555;
                line-height: 1.7;
            }

            .back-button {
                display: block;
                width: 100%;
                padding: 14px;
                margin-top: 20px;
                background: #142d4e;
                color: white;
                text-decoration: none;
                border-radius: 7px;
                font-size: 16px;
                font-weight: bold;
            }

            .back-button:hover {
                background: #2563eb;
            }
        </style>
    </head>

    <body>
        <div class="message-card">

            <div class="warning-icon">&#10005;</div>

            <h2>Hall Unavailable!</h2>

            <p>
                Sorry! This hall is currently unavailable
                for your selected date.
                Please choose another hall or date.
            </p>

            <a href="booking.html" class="back-button">
                &#8592; Back to Booking Form
            </a>

        </div>
    </body>
    </html>';

    exit;
}
// Check guest count against hall capacity
$hall_capacity = (int)$hall["capacity"];

if ($guest_count <= 0 || $guest_count > $hall_capacity) {
    die("Booking failed! The number of guests cannot exceed the hall capacity of "
        . $hall_capacity . " people.");
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

    mysqli_stmt_close($maintenance_stmt);

    echo '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Hall Under Maintenance</title>

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

            .message-card {
                background: white;
                width: 100%;
                max-width: 480px;
                padding: 40px 30px;
                text-align: center;
                border-radius: 15px;
                box-shadow: 0 5px 20px rgba(0,0,0,0.10);
            }

            .warning-icon {
                width: 75px;
                height: 75px;
                line-height: 75px;
                margin: 0 auto 20px;
                border-radius: 50%;
                background: #fef3c7;
                color: #d97706;
                font-size: 42px;
                font-weight: bold;
            }

            h2 {
                color: #b45309;
            }

            p {
                color: #555;
                line-height: 1.7;
            }

            .back-button {
                display: block;
                width: 100%;
                padding: 14px;
                margin-top: 20px;
                background: #142d4e;
                color: white;
                text-decoration: none;
                border-radius: 7px;
                font-size: 16px;
                font-weight: bold;
            }

            .back-button:hover {
                background: #2563eb;
            }
        </style>
    </head>

    <body>
        <div class="message-card">

            <div class="warning-icon">&#9888;</div>

            <h2>Hall Under Maintenance!</h2>

            <p>
                Sorry! This hall is under maintenance on '
                . htmlspecialchars($event_date) . '.
                Please choose another date.
            </p>';

    if (!empty($reason)) {
        echo '<p><b>Reason:</b> '
             . htmlspecialchars($reason) . '</p>';
    }

    echo '
            <a href="booking.html" class="back-button">
                &#8592; Back to Booking Form
            </a>

        </div>
    </body>
    </html>';

    exit;
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

    echo '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Booking Slot Unavailable</title>

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

            .message-card {
                background: white;
                width: 100%;
                max-width: 480px;
                padding: 40px 30px;
                text-align: center;
                border-radius: 15px;
                box-shadow: 0 5px 20px rgba(0,0,0,0.10);
            }

            .warning-icon {
                width: 75px;
                height: 75px;
                line-height: 75px;
                margin: 0 auto 20px;
                border-radius: 50%;
                background: #fee2e2;
                color: #dc2626;
                font-size: 42px;
                font-weight: bold;
            }

            h2 {
                color: #b91c1c;
                margin-bottom: 12px;
            }

            p {
                color: #555;
                line-height: 1.7;
            }

            button {
                width: 100%;
                padding: 14px;
                margin-top: 20px;
                background: #142d4e;
                color: white;
                border: none;
                border-radius: 7px;
                font-size: 16px;
                font-weight: bold;
                cursor: pointer;
            }

            button:hover {
                background: #2563eb;
            }
        </style>
    </head>

    <body>

        <div class="message-card">

            <div class="warning-icon">&#10005;</div>

            <h2>Booking Slot Unavailable!</h2>

            <p>
                Sorry! The selected date or time slot is already booked.
                Please choose another available date or time.
            </p>

            <button onclick="window.location.href=\'booking.html\'">
                &#8592; Back to Booking Form
            </button>

        </div>

    </body>
    </html>';

} else {

        // Save booking details
         $sql = "INSERT INTO bookings
        (user_id, hall_id, event_name, event_date,
         start_time, end_time, guest_count,
         total_amount, customer_name, mobile, email, booking_status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending Payment')";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
    $stmt,
    "iissssidsss",
    $user_id,
    $hall_id,
    $event_name,
    $event_date,
    $start_time,
    $end_time,
    $guest_count,
    $total_amount,
    $customer_name,
    $mobile,
    $email
);
        if (mysqli_stmt_execute($stmt)) {

    $booking_id = mysqli_insert_id($conn);

    echo '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Booking Confirmed</title>

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
                max-width: 480px;
                padding: 40px 30px;
                text-align: center;
                border-radius: 15px;
                box-shadow: 0 5px 20px rgba(0,0,0,0.10);
            }

            .success-icon {
                width: 75px;
                height: 75px;
                line-height: 75px;
                margin: 0 auto 20px;
                border-radius: 50%;
                background: #dcfce7;
                color: #16a34a;
                font-size: 42px;
                font-weight: bold;
            }

            h2 {
                color: #166534;
                margin-bottom: 12px;
            }

            p {
                color: #555;
                line-height: 1.6;
            }

            .booking-id {
                background: #f1f5f9;
                padding: 12px;
                border-radius: 7px;
                margin: 20px 0;
                color: #142d4e;
                font-weight: bold;
            }

            button {
                width: 100%;
                padding: 14px;
                background: #142d4e;
                color: white;
                border: none;
                border-radius: 7px;
                font-size: 16px;
                font-weight: bold;
                cursor: pointer;
            }

            button:hover {
                background: #2563eb;
            }
        </style>
    </head>

    <body>

        <div class="success-card">

            <div class="success-icon">&#10003;</div>

            <h2>Booking Successful!</h2>

            <p>Your hall booking has been submitted successfully.</p>

            <div class="booking-id">
                Booking ID: ' . $booking_id . '
            </div>

            <p>Please proceed to payment to complete your booking.</p>

            <form action="payment.php" method="POST">
                <input type="hidden" name="booking_id"
                       value="' . $booking_id . '">

                <button type="submit">Proceed to Payment</button>
            </form>

        </div>

    </body>
    </html>';

} else {

    echo "Booking failed: " . mysqli_error($conn);
}

        

        mysqli_stmt_close($stmt);
    }

    mysqli_stmt_close($check_stmt);
}
?>
