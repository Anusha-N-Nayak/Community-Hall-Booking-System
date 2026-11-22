<?php
session_start();
include "db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $sql = "SELECT * FROM admins WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if ($admin = mysqli_fetch_assoc($result)) {

        if (password_verify($password, $admin["password"])) {

            session_regenerate_id(true);

            $_SESSION["admin_id"] = $admin["admin_id"];
            $_SESSION["admin_email"] = $admin["email"];

            header("Location: admin_dashboard.php");
            exit();

        } else {
            $error = "Invalid email or password!";
        }

    } else {
        $error = "Invalid email or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Login</title>

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

        .login-container {
            background: white;
            width: 400px;
            padding: 40px;
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
            margin-bottom: 30px;
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
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
        }

        input:focus {
            outline: none;
            border-color: #517fa4;
        }

        button {
            width: 100%;
            padding: 13px;
            background: #243b55;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 17px;
            cursor: pointer;
        }

        button:hover {
            background: #517fa4;
        }

        .error {
    color: #b91c1c;
    background: #fee2e2;
    border: 1px solid #fecaca;
    padding: 12px;
    border-radius: 6px;
    text-align: center;
    margin-top: 18px;
    font-size: 14px;
    font-weight: bold;
}

        .home-link {
            text-align: center;
            margin-top: 22px;
        }

        .home-link a {
            color: #517fa4;
            text-decoration: none;
        }
    </style>
</head>

<body>

<div class="login-container">

    <h2>Admin Login</h2>

    <p class="subtitle">Welcome Back! Login to Admin Account</p>

    <form method="POST">

        <label>Email Address</label>

        <input type="email"
               name="email"
               placeholder="Enter your email"
               required>

        <label>Password</label>

        <input type="password"
               name="password"
               placeholder="Enter your password"
               required>

        <button type="submit">Login</button>

    </form>

    <?php if ($error != "") { ?>
    <p class="error">
        <?php echo htmlspecialchars($error); ?>
    </p>
<?php } ?>

    <div class="home-link">
        <a href="index.html">← Back to Home</a>
    </div>

</div>

</body>
</html>