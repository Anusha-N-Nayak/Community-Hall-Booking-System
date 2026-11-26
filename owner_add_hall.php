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


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Community Hall</title>

    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            margin: 0;
            padding: 35px 15px;
            background: #f1f4f8;
        }

        .form-container {
            max-width: 650px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 18px rgba(0,0,0,0.10);
        }

        h2 {
            text-align: center;
            color: #142d4e;
            margin-bottom: 25px;
        }

        label {
            display: block;
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
        }

        input, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 15px;
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: #2563eb;
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        button {
            width: 100%;
            padding: 13px;
            background: #142d4e;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #2563eb;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #142d4e;
            text-decoration: none;
        }

        @media (max-width: 500px) {
            .form-container {
                padding: 20px;
            }
        }
    </style>
</head>

<body>

<div class="form-container">

    <h2>Add Your Community Hall</h2>

    <form method="POST" enctype="multipart/form-data">

        <label>Hall Name</label>
        <input type="text" name="hall_name" placeholder="Enter hall name" required>

        <label>Place</label>
        <input type="text" name="location"
               placeholder="Enter place name, e.g. Darbe, Puttur" required>

        <label>Capacity</label>
        <input type="number" name="capacity" placeholder="Maximum number of guests" min="1" required>

        <label>Price (₹)</label>
        <input type="number" name="price" placeholder="Enter hall price" min="0" required>

        <label>Facilities</label>
        <input type="text" name="facilities"
               placeholder="AC, Parking, Dining, Stage...">

        <label>Hall Image</label>
        <input type="file" name="image" accept="image/*" required>

        <label>Description</label>
        <textarea name="description"
                  placeholder="Describe your community hall"></textarea>

        <button type="submit">Add Hall</button>

    </form>

    <a href="owner_dashboard.php" class="back-link">
        ← Back to Owner Dashboard
    </a>

</div>

</body>
</html>
