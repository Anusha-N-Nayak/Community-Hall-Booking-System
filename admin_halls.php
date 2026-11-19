<?php
include "db.php";

// Get all hall details
$sql = "SELECT * FROM halls ORDER BY hall_id DESC";

$result = mysqli_query($conn, $sql);

$halls = [];

while ($row = mysqli_fetch_assoc($result)) {
    $halls[] = $row;
}

// Send hall details to frontend
header("Content-Type: application/json");
echo json_encode($halls);
?>