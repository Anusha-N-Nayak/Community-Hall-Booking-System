<?php
include "db.php";

$hall_name = $_GET["hall"] ?? "";
if ($hall_name == "Royal Palace Community Hall") {
    $hall_name = "Royal Community Hall";
}
$feedback_list = [];
$error = "";

if ($hall_name != "") {

    $sql = "SELECT users.name, feedback.comments, feedback.created_at
            FROM feedback
            JOIN users ON feedback.user_id = users.user_id
            JOIN bookings ON feedback.booking_id = bookings.booking_id
            JOIN halls ON bookings.hall_id = halls.hall_id
            WHERE halls.hall_name = ?
            ORDER BY feedback.created_at DESC";

    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $hall_name);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            $feedback_list[] = $row;
        }

        mysqli_stmt_close($stmt);
    } else {
        $error = "Unable to retrieve feedback.";
    }

} else {
    $error = "Please select a hall.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>View Hall Feedback</title>

<style>
* {
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    margin: 0;
    padding: 30px 15px;
    background: #f2f5f9;
}

.container {
    max-width: 750px;
    margin: auto;
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 3px 12px #ddd;
}

h1 {
    color: #142d4e;
    text-align: center;
}

h3 {
    color: #1e6091;
    text-align: center;
}

.feedback-card {
    background: #f2f5f9;
    padding: 20px;