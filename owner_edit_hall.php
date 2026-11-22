<?php
session_start();
include "db.php";

if (!isset($_SESSION["owner_id"])) {
    die("Please login first.");
}

$owner_id = $_SESSION["owner_id"];
$hall_id = (int)($_GET["hall_id"] ?? 0);

$sql = "SELECT * FROM halls WHERE hall_id = ? AND owner_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $hall_id, $owner_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$hall = mysqli_fetch_assoc($result);

if (!$hall) {
    die("Hall not found.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $hall_name = trim($_POST["hall_name"]);
    $location = trim($_POST["location"]);
    $capacity = (int)$_POST["capacity"];
    $price = (float)$_POST["price"];
    $facilities = trim($_POST["facilities"]);
    $description = trim($_POST["description"]);
    $image = $hall["image"];

    if (!empty($_FILES["image"]["name"])) {
        $image = "uploads/" . basename($_FILES["image"]["name"]);

        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $image)) {
            die("Image upload failed.");
        }
    }

    $sql = "INSERT INTO hall_edit_requests
    (hall_id, owner_id, hall_name, location, capacity, price,
     facilities, description, image)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param(
        $stmt, "iissidsss",
        $hall_id, $owner_id, $hall_name, $location,
        $capacity, $price, $facilities, $description, $image
    );

    if (mysqli_stmt_execute($stmt)) {
        echo "<h2>Hall edit request submitted successfully!</h2>";
        echo "<p>Waiting for admin approval.</p>";
        echo "<a href='owner_dashboard.php'>Back to Owner Dashboard</a>";
        exit;
    } else {
        echo "Failed to submit edit request.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Hall</title>
</head>
<body>

<h2>Edit Hall Details</h2>

<form method="POST" enctype="multipart/form-data">

    Hall Name:<br>
    <input type="text" name="hall_name"
    value="<?php echo htmlspecialchars($hall['hall_name']); ?>" required><br><br>

    Location:<br>
    <input type="text" name="location"
    value="<?php echo htmlspecialchars($hall['location']); ?>" required><br><br>

    Capacity:<br>
    <input type="number" name="capacity"
    value="<?php echo htmlspecialchars($hall['capacity']); ?>" required><br><br>

    Price:<br>
    <input type="number" step="0.01" name="price"
    value="<?php echo htmlspecialchars($hall['price']); ?>" required><br><br>

    Facilities:<br>
    <textarea name="facilities" required><?php echo htmlspecialchars($hall['facilities']); ?></textarea><br><br>

    Description:<br>
    <textarea name="description" required><?php echo htmlspecialchars($hall['description']); ?></textarea><br><br>

    Hall Image:<br>
    <input type="file" name="image" accept="image/*"><br><br>

    <button type="submit">Submit for Admin Approval</button>

</form>

</body>
</html>