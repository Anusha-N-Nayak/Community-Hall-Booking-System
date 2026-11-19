<?php
session_start();
include "db.php";

// Check whether the user is logged in
if (!isset($_SESSION["user_id"])) {
    die("Please login first.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = $_SESSION["user_id"];
    $booking_id = (int) $_POST["booking_id"];
    $comments = trim($_POST["comments"]);

    if (empty($comments)) {
        die("Please enter your feedback.");
    }

    // Check whether this booking belongs to the user
    $check_sql = "SELECT booking_id FROM bookings
                  WHERE booking_id = ? AND user_id = ?";

    $check_stmt = mysqli_prepare($conn, $check_sql);

    mysqli_stmt_bind_param($check_stmt, "ii", $booking_id, $user_id);

    mysqli_stmt_execute($check_stmt);

    $result = mysqli_stmt_get_result($check_stmt);

    if (mysqli_num_rows($result) == 0) {
        die("Invalid booking details.");
    }

    // Store feedback
    $sql = "INSERT INTO feedback (user_id, booking_id, comments)
            VALUES (?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "iis", $user_id, $booking_id, $comments);

    if (mysqli_stmt_execute($stmt)) {
        echo "Feedback submitted successfully!";
    } else {
        echo "Feedback submission failed.";
    }
}
?>