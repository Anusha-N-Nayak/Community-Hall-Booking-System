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

    echo "
    <!DOCTYPE html>
    <html>
    <head>
        <title>Delete Request Submitted</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background: #f5f3ff;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                margin: 0;
            }

            .success-box {
                background: white;
                padding: 40px;
                border-radius: 15px;
                text-align: center;
                max-width: 420px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            }

            .success-icon {
                font-size: 55px;
                color: #6d5bd0;
            }

            h2 {
                color: #292638;
            }

            p {
                color: #666;
                line-height: 1.6;
            }

            a {
                display: inline-block;
                margin-top: 18px;
                padding: 12px 22px;
                background: #6d5bd0;
                color: white;
                text-decoration: none;
                border-radius: 7px;
            }

            a:hover {
                background: #5746b5;
            }
        </style>
    </head>
    <body>
        <div class='success-box'>
            <div class='success-icon'>&#10004;</div>
            <h2>Delete Request Submitted!</h2>
            <p>Your hall deletion request has been submitted successfully.</p>
            <p>Please wait while the admin reviews your request.</p>
            <a href='owner_dashboard.php'>Back to Dashboard</a>
        </div>
    </body>
    </html>
    ";

} else {
    echo "Failed to submit request.";
}
?>