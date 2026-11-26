
<?php
session_start();
include "db.php";

header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["error" => "Please login first."]);
    exit;
}

$user_id = (int)$_SESSION["user_id"];

$sql = "SELECT name, phone, email FROM users WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if ($user) {
    echo json_encode($user);
} else {
    echo json_encode(["error" => "Profile not found."]);
}

mysqli_stmt_close($stmt);
?>
