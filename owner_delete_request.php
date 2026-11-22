<?php
session_start();
include "db.php";

if (!isset($_SESSION["owner_id"])) {
    die("Please login first.");
}

$owner_id = $_SESSION["owner_id"];
$hall_id = (int)($_GET["hall_id"] ?? 0);

// Check whether this hall belongs to the logged-in owner
$sql = "SELECT hall_id FROM halls
        WHERE hall_id = ? AND owner_id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $hall_id, $owner_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    die("Hall not found.");
}

// Check for an existing pending request
$sql = "SELECT request_id FROM hall_delete_requests
        WHERE hall_id = ? AND owner_id = ?
        AND request_status = 'Pending'";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $hall_id, $owner_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    die("A deletion request is already pending.");
}

// Submit deletion request
$sql = "INSERT INTO hall_delete_requests
        (hall_id, owner_id)
        VALUES (?, ?)";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $hall_id, $owner_id);

if (mysqli_stmt_execute($stmt)) {
    echo "<h2>Delete Request Submitted Successfully!</h2>";
    echo "<p>Waiting for admin approval.</p>";
    echo "<a href='owner_dashboard.php'>Back to Owner Dashboard</a>";
} else {
    echo "Failed to submit request.";
}
?>