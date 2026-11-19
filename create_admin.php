<?php
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    $sql = "INSERT INTO admins (username, email, password)
            VALUES (?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt, "sss",
        $username, $email, $password
    );

    if (mysqli_stmt_execute($stmt)) {
        echo "Admin account created successfully!";
    } else {
        echo "Admin account creation failed. " . mysqli_error($conn);
    }
}
?>

<form method="POST">
    <h2>Create Admin Account</h2>

    Username:
    <input type="text" name="username" required><br><br>

    Email:
    <input type="email" name="email" required><br><br>

    Password:
    <input type="password" name="password" required><br><br>

    <button type="submit">Create Admin</button>
</form>