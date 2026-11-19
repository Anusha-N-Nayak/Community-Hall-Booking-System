<?php
session_start();
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $sql = "SELECT * FROM owners WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if ($owner = mysqli_fetch_assoc($result)) {

        if (password_verify($password, $owner["password"])) {

            $_SESSION["owner_id"] = $owner["owner_id"];
            $_SESSION["owner_name"] = $owner["owner_name"];

            header("Location: owner_dashboard.php");
exit();
        } else {
            echo "Incorrect password!";
        }

    } else {
        echo "Owner not found!";
    }
}
?>

<h2>Hall Owner Login</h2>

<form method="POST">

    Email:
    <input type="email" name="email" required><br><br>

    Password:
    <input type="password" name="password" required><br><br>

    <button type="submit">Login</button>

</form>