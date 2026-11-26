
<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"])) {
    die("Please login first.");
}

$user_id = $_SESSION["user_id"];
$booking_id = (int)($_GET["booking_id"] ?? 0);

if ($booking_id <= 0) {
    die("Invalid booking.");
}

$sql = "SELECT b.*, h.hall_name, h.location,
               u.name AS profile_name,
               u.phone AS profile_phone,
               u.email AS profile_email,
               p.payment_method, p.payment_status,
               p.transaction_id
        FROM bookings b
        JOIN halls h ON b.hall_id = h.hall_id
        LEFT JOIN users u ON b.user_id = u.user_id
        JOIN payments p ON b.booking_id = p.booking_id
        WHERE b.booking_id = ?
        AND b.user_id = ?
        AND b.booking_status = 'Confirmed'
        AND p.payment_status = 'Successful'
        ORDER BY p.payment_id DESC
        LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $booking_id, $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$receipt = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$receipt) {
    die("Receipt not found or payment is not completed.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f4f8;
            padding: 30px 15px;
        }

        .receipt {
            max-width: 650px;
            margin: auto;
            background: white;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .header {
            text-align: center;
            color: #142d4e;
            border-bottom: 2px solid #142d4e;
            padding-bottom: 20px;
        }

        .paid {
            color: #15803d;
            font-weight: bold;
            background: #dcfce7;
            padding: 10px;
            border-radius: 6px;
            display: inline-block;
        }

        .details {
            margin-top: 25px;
            line-height: 2;
        }

        .details h3 {
            color: #142d4e;
            border-bottom: 1px solid #ddd;
            padding-bottom: 8px;
        }

        .amount {
            background: #f1f5f9;
            padding: 15px;
            border-radius: 7px;
            font-size: 20px;
            font-weight: bold;
            color: #142d4e;
            text-align: center;
        }

        .buttons {
            text-align: center;
            margin-top: 25px;
        }

        button {
            padding: 12px 22px;
            margin: 5px;
            border: none;
            border-radius: 6px;
            background: #142d4e;
            color: white;
            font-size: 15px;
            cursor: pointer;
        }

        button:hover {
            background: #2563eb;
        }

   <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f4f8;
            padding: 30px 15px;
        }

        .receipt {
            max-width: 650px;
            margin: auto;
            background: white;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .header {
            text-align: center;
            color: #142d4e;
            border-bottom: 2px solid #142d4e;
            padding-bottom: 20px;
        }

        .paid {
            color: #15803d;
            font-weight: bold;
            background: #dcfce7;
            padding: 10px;
            border-radius: 6px;
            display: inline-block;
        }

        .details {
            margin-top: 25px;
            line-height: 2;
        }

        .details h3 {
            color: #142d4e;
            border-bottom: 1px solid #ddd;
            padding-bottom: 8px;
        }

        .amount {
            background: #f1f5f9;
            padding: 15px;
            border-radius: 7px;
            font-size: 20px;
            font-weight: bold;
            color: #142d4e;
            text-align: center;
        }

        .buttons {
            text-align: center;
            margin-top: 25px;
        }

        button {
            padding: 12px 22px;
            margin: 5px;
            border: none;
            border-radius: 6px;
            background: #142d4e;
            color: white;
            font-size: 15px;
            cursor: pointer;
        }

        button:hover {
            background: #2563eb;
        }

       
@page {
    size: A4 portrait;
    margin: 10mm;
}

@page {
    size: A4 portrait;
    margin: 10mm;
}


@page {
    size: A4 portrait;
    margin: 12mm;
}

@media print {

    body {
        background: white;
        padding: 0;
        margin: 0;
        font-size: 15px;
    }

    .receipt {
        width: 100%;
        max-width: none;
        padding: 20px 35px;
        margin: 0;
        box-shadow: none;
        border-radius: 0;
    }

    .header {
        padding-bottom: 25px;
    }

    .header h2 {
        font-size: 27px;
        margin: 15px 0;
    }

    .header h3 {
        font-size: 21px;
        margin: 15px 0;
    }

    .paid {
        padding: 12px 20px;
        font-size: 15px;
    }

    .details {
        margin-top: 30px;
        line-height: 2;
        font-size: 15px;
    }

    .details h3 {
        font-size: 19px;
        margin: 25px 0 15px;
        padding-bottom: 10px;
    }

    .details p {
        margin: 13px 0;
    }

    .amount {
        padding: 18px;
        font-size: 22px;
        margin-top: 25px;
    }

    .buttons {
        display: none !important;
    }
        .details {
        line-height: 1.4;
        margin-top: 20px;
    }

    .details p {
        margin: 5px 0;
    }

    .details h3 {
        margin: 12px 0 8px;
    }

    .receipt > .header:last-of-type {
        margin-top: 8px !important;
        padding-bottom: 0;
        break-inside: avoid;
    }

}


    </style>
</head>

<body>

<div class="receipt">

    <div class="header">
        <h2>COMMUNITY HALL BOOKING</h2>
        <h3>PAYMENT RECEIPT</h3>
        <p class="paid">✓ PAYMENT SUCCESSFUL</p>
    </div>

    <div class="details">

        <h3>Booking Information</h3>

        <p><b>Receipt Number:</b> RCP-<?php echo $booking_id; ?></p>

        <p><b>Booking ID:</b>
            <?php echo htmlspecialchars($booking_id); ?>
        </p>

        <p><b>Customer Name:</b>
            <?php
echo htmlspecialchars(
    !empty($receipt["customer_name"])
        ? $receipt["customer_name"]
        : $receipt["profile_name"]
);
?>
        </p>

        <p><b>Phone:</b>
    <?php
echo htmlspecialchars(
    !empty($receipt["mobile"])
        ? $receipt["mobile"]
        : $receipt["profile_phone"]
);
?>
</p>

<p><b>Email:</b>
    <?php
echo htmlspecialchars(
    !empty($receipt["email"])
        ? $receipt["email"]
        : $receipt["profile_email"]
);
?>
</p>

        <p><b>Hall Name:</b>
            <?php echo htmlspecialchars($receipt["hall_name"]); ?>
        </p>

        <p><b>Place:</b>
            <?php echo htmlspecialchars($receipt["location"]); ?>
        </p>

        <p><b>Event:</b>
            <?php echo htmlspecialchars($receipt["event_name"]); ?>
        </p>

        <p><b>Event Date:</b>
            <?php echo htmlspecialchars($receipt["event_date"]); ?>
        </p>

        <p><b>Start Time:</b>
            <?php echo htmlspecialchars($receipt["start_time"]); ?>
        </p>

        <p><b>End Time:</b>
            <?php echo htmlspecialchars($receipt["end_time"]); ?>
        </p>

        <p><b>Guests:</b>
            <?php echo htmlspecialchars($receipt["guest_count"]); ?>
        </p>

        <h3>Payment Information</h3>

        <p><b>Payment Method:</b>
            <?php echo htmlspecialchars($receipt["payment_method"]); ?>
        </p>

        <p><b>Transaction ID:</b>
            <?php echo htmlspecialchars($receipt["transaction_id"]); ?>
        </p>

        <p><b>Payment Status:</b>
            <?php echo htmlspecialchars($receipt["payment_status"]); ?>
        </p>

        <div class="amount">
            Amount Paid: ₹<?php echo number_format($receipt["total_amount"], 2); ?>
        </div>

    </div>

    <div class="header" style="margin-top:25px; border:none;">
        <p>Thank you for booking with us!</p>
    </div>

    <div class="buttons">
        <button onclick="window.print()">Download / Print Receipt</button>

        <button onclick="window.location.href='my_bookings.php'">
            Back to My Bookings
        </button>
    </div>

</div>

</body>
</html>
    


    