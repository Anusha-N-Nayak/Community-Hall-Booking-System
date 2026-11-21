<?php
include "db.php";

header("Content-Type: application/json");

$hall_id = (int)($_GET["hall_id"] ?? 0);
$month = (int)($_GET["month"] ?? 0);
$year = (int)($_GET["year"] ?? 0);

if ($hall_id <= 0 || $month < 1 || $month > 12 || $year < 2020) {
    echo json_encode(["error" => "Invalid request"]);
    exit;
}

$start_date = sprintf("%04d-%02d-01", $year, $month);
$end_date = date("Y-m-t", strtotime($start_date));

$sql = "SELECT event_date, start_time, end_time
        FROM bookings
        WHERE hall_id = ?
        AND event_date BETWEEN ? AND ?
        AND booking_status = 'Confirmed'";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "iss",
    $hall_id,
    $start_date,
    $end_date
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$bookings = [];

while ($row = mysqli_fetch_assoc($result)) {
    $bookings[] = $row;
}

echo json_encode(["bookings" => $bookings]);

mysqli_stmt_close($stmt);
?>