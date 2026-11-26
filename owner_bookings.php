<?php
session_start();
include "db.php";

if (!isset($_SESSION["owner_id"])) {
    die("Please login first.");
}

$owner_id = $_SESSION["owner_id"];
$hall_id = (int)($_GET["hall_id"] ?? 0);


$sql = "SELECT b.*, h.hall_name,
               u.name AS profile_name,
               u.phone AS profile_phone,
               u.email AS profile_email,
               p.payment_status
        FROM bookings b
        JOIN halls h ON b.hall_id = h.hall_id
        LEFT JOIN users u ON b.user_id = u.user_id
        LEFT JOIN payments p ON b.booking_id = p.booking_id
        WHERE h.owner_id = ? AND b.hall_id = ?
        ORDER BY b.created_at DESC";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "ii", $owner_id, $hall_id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Hall Bookings</title>
</head>

<body>

<a href="owner_dashboard.php"
   style="
       display:inline-block;
       padding:10px 18px;
       background:#142d4e;
       color:white;
       text-decoration:none;
       border-radius:6px;
       margin-bottom:20px;
       font-weight:bold;
   ">
    ← Back to Owner Dashboard
</a>

<h2>Manage Hall Bookings</h2>
<?php
if (mysqli_num_rows($result) > 0) {
?>

<table border="1" cellpadding="10" cellspacing="0">

<tr>
    <th>Booking ID</th>
    <th>Hall Name</th>
    <th>Customer Name</th>
    <th>Phone</th>
    <th>Email</th>
    <th>Event</th>
    <th>Event Date</th>
    <th>Amount</th>
    <th>Booking Status</th>
    <th>Payment Status</th>
</tr>

<?php
while ($booking = mysqli_fetch_assoc($result)) {
?>

<tr>
    <td><?php echo $booking["booking_id"]; ?></td>

    <td><?php echo htmlspecialchars($booking["hall_name"]); ?></td>

    <td>
    <?php
    echo htmlspecialchars(
        !empty($booking["customer_name"])
            ? $booking["customer_name"]
            : $booking["profile_name"]
    );
    ?>
</td>
<td>
    <?php
    echo htmlspecialchars(
        !empty($booking["mobile"])
            ? $booking["mobile"]
            : $booking["profile_phone"]
    );
    ?>
</td>
<td>
    <?php
    echo htmlspecialchars(
        !empty($booking["email"])
            ? $booking["email"]
            : $booking["profile_email"]
    );
    ?>
</td>

    <td><?php echo htmlspecialchars($booking["event_name"]); ?></td>

    <td><?php echo htmlspecialchars($booking["event_date"]); ?></td>
    <td>₹<?php echo htmlspecialchars($booking["total_amount"]); ?></td>

    <td><?php echo htmlspecialchars($booking["booking_status"]); ?></td>
    
    <td>
        <?php echo htmlspecialchars($booking["payment_status"] ?? "Not Paid"); ?>
    </td>
</tr>

<?php
}
?>

</table>

<?php
} else {
    echo "No bookings available.";
}
?>

</body>
</html>