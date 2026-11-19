<?php
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $owner_name = trim($_POST["owner_name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    $sql = "INSERT INTO owners
            (owner_name, email, phone, password)
            VALUES (?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt, "ssss",
        $owner_name, $email, $phone, $password
    );

    if (mysqli_stmt_execute($stmt)) {
        echo "Hall owner registered successfully!";
    } else {
        echo "Registration failed. Email may already exist.";
    }
}
?>

<h2>Register Hall Owner</h2>

<form method="POST">
    Owner Name:
    <input type="text" name="owner_name" required><br><br>

    Email:
    <input type="email" name="email" required><br><br>

    Phone:
    <input type="text" name="phone" required><br><br>

    Password:
    <input type="password" name="password" required><br><br>

    <button type="submit">Register Owner</button>
</form>