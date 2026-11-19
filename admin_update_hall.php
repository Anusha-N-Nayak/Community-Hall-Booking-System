<?php
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $hall_id = $_POST["hall_id"];
    $hall_name = $_POST["hall_name"];
    $location = $_POST["location"];
    $capacity = $_POST["capacity"];
    $price = $_POST["price"];
    $facilities = $_POST["facilities"];
    $image = $_POST["image"];
    $description = $_POST["description"];

    $sql = "UPDATE halls SET
            hall_name = ?,
            location = ?,
            capacity = ?,
            price = ?,
            facilities = ?,
            image = ?,
            description = ?
            WHERE hall_id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "ssidsssi",
        $hall_name,
        $location,
        $capacity,
        $price,
        $facilities,
        $image,
        $description,
        $hall_id
    );

    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_stmt_affected_rows($stmt) > 0) {
            echo "Hall details updated successfully!";
        } else {
            echo "No changes made or hall not found.";
        }
    } else {
        echo "Update failed.";
    }

} else {
    echo "Please submit hall details.";
}
?>