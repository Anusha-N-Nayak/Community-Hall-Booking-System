<?php
include "db.php";

// Get all feedback details
$sql = "SELECT
        feedback.feedback_id,
        users.name,
        feedback.booking_id,
        feedback.comments,
        feedback.created_at
        FROM feedback
        JOIN users ON feedback.user_id = users.user_id
        ORDER BY feedback.created_at DESC";

$result = mysqli_query($conn, $sql);

$feedback_list = [];

while ($row = mysqli_fetch_assoc($result)) {
    $feedback_list[] = $row;
}

// Send feedback details to frontend
header("Content-Type: application/json");
echo json_encode($feedback_list);
?>