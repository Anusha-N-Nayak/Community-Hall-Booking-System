
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


// 1. Fetch confirmed bookings

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

mysqli_stmt_close($stmt);


// 2. Fetch maintenance dates for this hall

$maintenance_sql = "SELECT maintenance_date, reason
                    FROM hall_maintenance
                    WHERE hall_id = ?
                    AND maintenance_date BETWEEN ? AND ?";

$maintenance_stmt = mysqli_prepare($conn, $maintenance_sql);

mysqli_stmt_bind_param(
    $maintenance_stmt,
    "iss",
    $hall_id,
    $start_date,
    $end_date
);

mysqli_stmt_execute($maintenance_stmt);

$maintenance_result = mysqli_stmt_get_result($maintenance_stmt);

$maintenance = [];

while ($row = mysqli_fetch_assoc($maintenance_result)) {
    $maintenance[] = $row;
}

mysqli_stmt_close($maintenance_stmt);


// 3. Send bookings and maintenance information

echo json_encode([
    "bookings" => $bookings,
    "maintenance" => $maintenance
]);

?>
