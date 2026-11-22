<?php
include "db.php";

$message = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $owner_name = trim($_POST["owner_name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    // Check duplicate email
    $check_sql = "SELECT owner_id FROM owners WHERE email = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);

    mysqli_stmt_bind_param($check_stmt, "s", $email);
    mysqli_stmt_execute($check_stmt);

    $check_result = mysqli_stmt_get_result($check_stmt);

    if (mysqli_num_rows($check_result) > 0) {

        $message = "This email is already registered! Please login.";

    } else {

        $sql = "INSERT INTO owners
                (owner_name, email, phone, password)
                VALUES (?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt, "ssss",
            $owner_name, $email, $phone, $password
        );

        try {
            if (mysqli_stmt_execute($stmt)) {
                $message = "Owner Registration Successful!";
                $success = true;
            }
        } catch (mysqli_sql_exception $e) {
            $message = "Registration failed. Please try again.";
        }

        mysqli_stmt_close($stmt);
    }

    mysqli_stmt_close($check_stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Owner Registration</title>

<style>
* {
    margin: 0;
    padding: 0;
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

.register-container {
    background: white;
    width: 420px;
    padding: 35px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

h2 {
    text-align: center;
    color: #243b55;
    margin-bottom: 10px;
}

.subtitle {
    text-align: center;
    color: #666;
    margin-bottom: 25px;
}

label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    color: #333;
}

input {
    width: 100%;
    padding: 12px;
    margin-bottom: 18px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 15px;
}

input:focus {
    outline: none;
    border-color: #517fa4;
}

button, .action-button {
    display: block;
    width: 100%;
    padding: 13px;
    background: #243b55;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    text-align: center;
    text-decoration: none;
    cursor: pointer;
}

button:hover, .action-button:hover {
    background: #517fa4;
}

.message {
    text-align: center;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 18px;
    font-weight: bold;
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

.login-link, .home-link {
    text-align: center;
    margin-top: 20px;
}

.login-link a, .home-link a {
    color: #243b55;
    text-decoration: none;
    font-weight: bold;
}
</style>
</head>

<body>

<div class="register-container">

<h2>Register as Hall Owner</h2>
<p class="subtitle">Create Your Owner Account</p>

<?php if ($message != "") { ?>

    <div class="message <?php echo $success ? 'success' : 'error'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>

    <?php if ($success) { ?>

        <a href="owner_login.html" class="action-button">
            Login Here
        </a>

        <div class="home-link">
            <a href="index.html">← Back to Home</a>
        </div>

    <?php } else { ?>

        <a href="owner_login.html" class="action-button">
            Login Here
        </a>

        <div class="home-link">
            <a href="index.html">← Back to Home</a>
        </div>

    <?php } ?>

<?php } else { ?>

<form method="POST">

    <label>Owner Name</label>
    <input type="text" name="owner_name"
           placeholder="Enter your name" required>

    <label>Email Address</label>
    <input type="email" name="email"
           placeholder="Enter your email" required>

    <label>Phone Number</label>
    <input type="tel" name="phone"
           placeholder="Enter your phone number" required>

    <label>Password</label>
    <input type="password" name="password"
           placeholder="Create a password" required>

    <button type="submit">Register Owner</button>

</form>

<div class="login-link">
    Already have an account?
    <a href="owner_login.html">Login Here</a>
</div>

<div class="home-link">
    <a href="index.html">← Back to Home</a>
</div>

<?php } ?>

</div>
</body>
</html>