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
    $image = trim($_POST["image"]);
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

<form method="POST">

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
<input type="text" name="image"><br><br>

Description:
<textarea name="description"></textarea><br><br>

<button type="submit">Add Hall</button>

</form>