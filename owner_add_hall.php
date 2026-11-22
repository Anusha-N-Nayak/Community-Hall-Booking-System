<?php
session_start();
include "db.php";

if (!isset($_SESSION["owner_id"])) {
    die("Please login first.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $owner_id = $_SESSION["owner_id"];

    $hall_name = trim($_POST["hall_name"]);
    $location = trim($_POST["location"]);
    $capacity = (int)$_POST["capacity"];
    $price = (float)$_POST["price"];
    $facilities = trim($_POST["facilities"]);
    $image = "";

if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {

    $folder = "uploads/";

    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }

    $imageName = time() . "_" . basename($_FILES["image"]["name"]);
    $target = $folder . $imageName;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target)) {
        $image = $target;
    } else {
        die("Image upload failed.");
    }

} else {
    die("Please select a hall image.");
}
    $description = trim($_POST["description"]);

    $sql = "INSERT INTO halls
    (owner_id, hall_name, location, capacity, price, facilities, image, description)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt, "issidsss",
        $owner_id, $hall_name, $location,
        $capacity, $price, $facilities,
        $image, $description
    );

    if (mysqli_stmt_execute($stmt)) {
    echo "
    <div style='text-align:center; margin-top:100px; font-family:Arial;'>

        <h2 style='color:green;'>Hall submitted successfully!</h2>

        <p>Your hall is waiting for admin approval.</p>
        <p>It will be visible to customers after approval.</p>

        <br>

        <a href='owner_dashboard.php'
           style='background:#2563eb; color:white; padding:12px 20px;
           text-decoration:none; border-radius:6px;'>
           Back to Owner Dashboard
        </a>

    </div>";
    exit;
} else {
    echo "Failed to add hall.";
}
}
?>

<h2>Add Your Community Hall</h2>

<form method="POST" enctype="multipart/form-data">

Hall Name:
<input type="text" name="hall_name" required><br><br>

Place:
<input type="text" name="location" placeholder="Enter place name, e.g.Darbe, Puttur" required><br><br>

Capacity:
<input type="number" name="capacity" required><br><br>

Price:
<input type="number" name="price" required><br><br>

Facilities:
<input type="text" name="facilities"><br><br>

Image:
<input type="file" name="image" accept="image/*" required><br><br>

Description:
<textarea name="description"></textarea><br><br>

<button type="submit">Add Hall</button>

</form>