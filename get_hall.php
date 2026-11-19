<?php
include "db.php";

if (isset($_GET["hall_id"])) {

    $hall_id = $_GET["hall_id"];

    $sql = "SELECT * FROM halls WHERE hall_id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $hall_id);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $hall = mysqli_fetch_assoc($result);

    header("Content-Type: application/json");

    echo json_encode($hall);
}
?>