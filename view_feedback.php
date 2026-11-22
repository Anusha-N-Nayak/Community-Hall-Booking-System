
<?php
session_start();
include "db.php";

$hall_name = trim($_GET["hall"] ?? "");

$feedback_list = [];
$error = "";

// If hall name is not received, get it using hall_id
if ($hall_name == "" && isset($_GET["hall_id"])) {

    $hall_id = (int)$_GET["hall_id"];

    $hall_sql = "SELECT hall_name FROM halls WHERE hall_id = ?";
    $hall_stmt = mysqli_prepare($conn, $hall_sql);

    if ($hall_stmt) {
        mysqli_stmt_bind_param($hall_stmt, "i", $hall_id);
        mysqli_stmt_execute($hall_stmt);

        $hall_result = mysqli_stmt_get_result($hall_stmt);
        $hall = mysqli_fetch_assoc($hall_result);

        if ($hall) {
            $hall_name = $hall["hall_name"];
        }

        mysqli_stmt_close($hall_stmt);
    }
}

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
<!DOCTYPE html><html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0"><title>View Hall Feedback</title><style>
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
    margin-top: 20px;
    border-radius: 8px;
    border-left: 5px solid #1e6091;
}

.feedback-card p {
    margin: 8px 0;
    line-height: 1.5;
}

.error {
    text-align: center;
    color: red;
    margin-top: 20px;
}

.no-feedback {
    text-align: center;
    color: #555;
    margin-top: 25px;
}

.back-btn {
    display: inline-block;
    padding: 9px 18px;
    background: #e8edf3;
    color: #243b55;
    text-decoration: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: bold;
    margin-bottom: 15px;
    transition: 0.3s;
}

.back-btn:hover {
    background: #d5dee8;
}

.back-btn:hover {
    background: #ffb703;
    color: #222;
}

</style>
</head>
<body>


<div style="max-width:750px; margin:0 auto 15px;">

    <?php if (($_GET["source"] ?? "") == "owner") { ?>

    <a href="owner_dashboard.php" class="back-btn">
        ← Back to Owner Dashboard
    </a>

<?php } else { ?>

    <a href="halldetails.php?hall=<?php echo urlencode($hall_name); ?>" class="back-btn">
        ← Back to Hall Details
    </a>

<?php } ?>

</div>
<div class="container">
    <h1>Customer Feedback</h1><h3><?php echo htmlspecialchars($hall_name); ?></h3><?php if ($error != "") { ?><p class="error">
    <?php echo htmlspecialchars($error); ?>
</p>

<?php } elseif (count($feedback_list) > 0) { ?><?php foreach ($feedback_list as $feedback) { ?>

    <div class="feedback-card">

        <p>
            <b>Customer:</b>
            <?php echo htmlspecialchars($feedback["name"]); ?>
        </p>

        <p>
            <b>Feedback:</b>
            <?php echo nl2br(htmlspecialchars($feedback["comments"])); ?>
        </p>

        <p>
            <small>
                <?php echo htmlspecialchars($feedback["created_at"]); ?>
            </small>
        </p>

    </div>

<?php } ?>

<?php } else { ?><p class="no-feedback">
    No feedback available for this hall yet.
</p>

<?php } ?>



</div></body>
</html>