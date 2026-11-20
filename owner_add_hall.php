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
        echo "Hall added successfully!";
    } else {
        echo "Failed to add hall.";
    }
}
?>

<h2>Add Your Community Hall</h2>

<form method="POST" enctype="multipart/form-data">

Hall Name:
<input type="text" name="hall_name" required><br><br>

Location:
<input type="text" name="location" required><br><br>

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