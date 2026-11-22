
<?php
include "db.php";

header("Content-Type: application/json");

$hall_id = isset($_GET["hall_id"]) ? (int)$_GET["hall_id"] : 0;

if ($hall_id <= 0) {
    echo json_encode(["error" => "Invalid hall."]);
    exit;
}

$sql = "SELECT hall_id, hall_name, price
        FROM halls
        WHERE hall_id = ?
        AND (hall_status IS NULL OR hall_status != 'Inactive')";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $hall_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$hall = mysqli_fetch_assoc($result);

if ($hall) {
    echo json_encode($hall);
} else {
    echo json_encode(["error" => "Hall not found or currently unavailable."]);
}

mysqli_stmt_close($stmt);
?>
