
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
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Bookings</title>

    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            margin: 0;
            padding: 25px;
            background: #f2f4f8;
        }

        .back-button {
            display: inline-block;
            padding: 11px 20px;
            background: #e8edf3;
            color: #243b55;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        h1 {
            text-align: center;
            color: #263b5e;
            margin-bottom: 30px;
        }

        .table-container {
    width: 100%;
    overflow-x: auto;
    background: white;
    padding: 10px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

       table {
    width: 100%;
    min-width: 950px;
    border-collapse: collapse;
    font-size: 13px;
}

th {
    background: #142d4e;
    color: white;
    padding: 10px 8px;
    text-align: center;
    white-space: nowrap;
    font-size: 13px;
}

td {
    padding: 9px 8px;
    text-align: center;
    border-bottom: 1px solid #ddd;
    color: #333;
    white-space: nowrap;
    font-size: 12px;
}

        tr:hover {
            background: #f5f8fc;
        }

        .status {
            font-weight: bold;
        }

        .action-link {
            display: inline-block;
            margin: 4px;
            padding: 8px 12px;
            background: #e8edf3;
            color: #142d4e;
            text-decoration: none;
            border-radius: 5px;
            font-size: 13px;
        }

        .action-link:hover {
            background: #142d4e;
            color: white;
        }

        .no-bookings {
            text-align: center;
            padding: 30px;
            color: #666;
        }
    </style>

</head>

<body>

    <a href="dashboard.php" class="back-button">
        &#8592; Back to Dashboard
    </a>

    <h1>My Bookings</h1>

    <div class="table-container">

        <table>

            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Hall Name</th>
                    <th>Event</th>
                    <th>Event Date</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Guests</th>

<th>Amount</th>
                    <th>Booking Status</th>
                    <th>Payment Status</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>

                <?php if (count($bookings) > 0) { ?>

                    <?php foreach ($bookings as $booking) { ?>

                        <tr>

                            <td>
                                <?php echo htmlspecialchars($booking['booking_id']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($booking['hall_name']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($booking['event_name']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($booking['event_date']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($booking['start_time']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($booking['end_time']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($booking['guest_count']); ?>
                            </td>

                            <td>
                                &#8377;<?php echo htmlspecialchars($booking['total_amount']); ?>
                            </td>

                            <td class="status">
                                <?php echo htmlspecialchars($booking['booking_status']); ?>
                            </td>

                            <td class="status">
                                <?php echo htmlspecialchars($booking['payment_status'] ?? 'Not Paid'); ?>
                            </td>

                            <td>

                                <?php if ($booking['booking_status'] == 'Confirmed') { ?>

                                    <a class="action-link"
                                       href="feedback.html?booking_id=<?php echo $booking['booking_id']; ?>">
                                        Submit Feedback
                                    </a>

                                    <?php if ($booking['payment_status'] == 'Successful') { ?>

                                        <a class="action-link"
                                           href="receipt.php?booking_id=<?php echo $booking['booking_id']; ?>">
                                            View / Download Receipt
                                        </a>

                                    <?php } ?>

                                <?php } ?>

                            </td>

                        </tr>

                    <?php } ?>

                <?php } else { ?>

                    <tr>
                        <td colspan="11" class="no-bookings">
                            No bookings found.
                        </td>
                    </tr>

                <?php } ?>

            </tbody>

        </table>

    </div>

</body>
</html>

<?php
mysqli_stmt_close($stmt);
?>
