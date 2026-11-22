<?php
include "../db.php";

$message = "";
$status = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);

    $check_sql = "SELECT user_id FROM users WHERE email = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);

    mysqli_stmt_bind_param($check_stmt, "s", $email);
    mysqli_stmt_execute($check_stmt);

    $check_result = mysqli_stmt_get_result($check_stmt);

    if (mysqli_num_rows($check_result) > 0) {

        $message = "This email is already registered! Please login.";
        $status = "error";

    } else {

        $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (name, email, phone, password)
                VALUES (?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt, "ssss",
            $name, $email, $phone, $password
        );

        if (mysqli_stmt_execute($stmt)) {
            $message = "Registration successful! Your account has been created.";
            $status = "success";
        } else {
            $message = "Registration failed. Please try again.";
            $status = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registration Status</title>

<style>
* {
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    background: #f4f6f9;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    padding: 20px;
}

.message-container {
    background: white;
    width: 420px;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    text-align: center;
}

h2 {
    color: #243b55;
    margin-bottom: 15px;
}

.message {
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 25px;
    font-weight: bold;
    line-height: 1.5;
}

.success {
    color: #166534;
    background: #dcfce7;
    border: 1px solid #86efac;
}

.error {
    color: #b91c1c;
    background: #fee2e2;
    border: 1px solid #fecaca;
}

.button {
    display: block;
    background: #243b55;
    color: white;
    padding: 13px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 16px;
    margin-bottom: 15px;
}

.button:hover {
    background: #517fa4;
}

.home-link {
    color: #517fa4;
    text-decoration: none;
}
</style>
</head>

<body>

<div class="message-container">

    <?php if ($status == "success") { ?>

        <h2>Registration Successful!</h2>

    <?php } else { ?>

        <h2>Registration Failed!</h2>

    <?php } ?>

    <div class="message <?php echo $status; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>

    <a href="login.html" class="button">Login Here</a>

    <a href="../index.html" class="home-link">Back to Home</a>

</div>

</body>
</html>