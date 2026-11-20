<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"])) {
    die("Please login first.");
}

$user_id = $_SESSION["user_id"];
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);

    if ($name == "" || $email == "" || $phone == "") {
        $message = "Please fill all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } else {

        $sql = "UPDATE users SET name = ?, email = ?, phone = ?
                WHERE user_id = ?";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssi", $name, $email, $phone, $user_id);

        if (mysqli_stmt_execute($stmt)) {
            $message = "Profile updated successfully!";
            $_SESSION["name"] = $name;
        } else {
            $message = "Unable to update profile. Please check whether the email is already registered.";
        }

        mysqli_stmt_close($stmt);
    }
}

$sql = "SELECT name, email, phone FROM users WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    die("User profile not found.");
}

mysqli_stmt_close($stmt);
?><!DOCTYPE html><html lang="en">
<head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0"><title>My Profile</title><style>

* {
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    background: #f2f4f8;
    padding: 30px 15px;
}

.container {
    max-width: 500px;
    margin: auto;
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 15px #ddd;
}

h2 {
    text-align: center;
    color: #142d4e;
    margin-bottom: 25px;
}

label {
    display: block;
    margin-top: 18px;
    font-weight: bold;
}

input {
    width: 100%;
    padding: 12px;
    margin-top: 8px;
    border: 1px solid #ccc;
    border-radius: 6px;
}

button {
    width: 100%;
    padding: 13px;
    margin-top: 25px;
    background: #1e6091;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    cursor: pointer;
}

button:hover {
    background: #142d4e;
}

.message {
    text-align: center;
    color: green;
    margin-bottom: 15px;
}

.back {
    display: block;
    text-align: center;
    margin-top: 20px;
    color: #1e6091;
    text-decoration: none;
}

</style></head><body><div class="container"><h2>👤 My Profile</h2><?php if ($message != "") { ?><p class="message">
    <?php echo htmlspecialchars($message); ?>
</p>

<?php } ?><form method="POST" action="profile.php"><label>Full Name</label>
<input type="text" name="name"
       value="<?php echo htmlspecialchars($user["name"]); ?>"
       required>

<label>Email Address</label>
<input type="email" name="email"
       value="<?php echo htmlspecialchars($user["email"]); ?>"
       required>

<label>Phone Number</label>
<input type="tel" name="phone"
       value="<?php echo htmlspecialchars($user["phone"]); ?>"
       required>

<button type="submit">Update Profile</button>

</form><a href="dashboard.php" class="back">Back to Dashboard</a>

</div></body>
</html>