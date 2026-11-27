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
     facilities, description, image,
     previous_hall_name, previous_location, previous_capacity,
     previous_price, previous_facilities, previous_description,
     previous_image)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "iissidsssssidsss",
    $hall_id,
    $owner_id,
    $hall_name,
    $location,
    $capacity,
    $price,
    $facilities,
    $description,
    $image,
    $hall["hall_name"],
    $hall["location"],
    $hall["capacity"],
    $hall["price"],
    $hall["facilities"],
    $hall["description"],
    $hall["image"]
);

if (mysqli_stmt_execute($stmt)) {
?>
<!DOCTYPE html>
<html>
<head>
    <title>Request Submitted</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f3ff;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .success-box {
            background: white;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            max-width: 420px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .success-icon {
            font-size: 55px;
            color: #6d5bd0;
        }

        h2 {
            color: #292638;
        }

        p {
            color: #666;
            line-height: 1.6;
        }

        a {
            display: inline-block;
            margin-top: 18px;
            padding: 12px 22px;
            background: #6d5bd0;
            color: white;
            text-decoration: none;
            border-radius: 7px;
        }

        a:hover {
            background: #5746b5;
        }
    </style>
</head>
<body>
    <div class='success-box'>
        <div class='success-icon'>&#10004;</div>
        <h2>Request Submitted!</h2>
        <p>Your hall edit request has been submitted successfully.</p>
        <p>Please wait while the admin reviews your changes.</p>
        <a href='owner_dashboard.php'>Back to Dashboard</a>
    </div>
</body>
</html>

<?php
exit;

} else {
    echo "Failed to submit edit request.";
}
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Edit Hall Details</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px 15px;
            font-family: Arial, sans-serif;
            background: #f5f3ff;
            color: #292638;
        }

        .container {
            max-width: 700px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 14px;
            box-shadow: 0 4px 18px rgba(40, 30, 80, 0.08);
        }

        h2 {
            text-align: center;
            color: #6d5bd0;
            margin-top: 0;
            margin-bottom: 25px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 7px;
        }

        input, textarea {
            width: 100%;
            padding: 11px;
            margin-bottom: 18px;
            border: 1px solid #d6d1e5;
            border-radius: 7px;
            font-size: 14px;
            font-family: Arial, sans-serif;
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: #6d5bd0;
            box-shadow: 0 0 4px rgba(109, 91, 208, 0.2);
        }

        textarea {
            min-height: 90px;
            resize: vertical;
        }

        input[type="file"] {
            background: #faf9ff;
        }

        .submit-btn {
            width: 100%;
            padding: 13px;
            background: #6d5bd0;
            color: white;
            border: none;
            border-radius: 7px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .submit-btn:hover {
            background: #5746b5;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 18px;
            color: #6d5bd0;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

<div class="container">

    <h2>Edit Hall Details</h2>

    <form method="POST" enctype="multipart/form-data">

        <label>Hall Name</label>
        <input type="text" name="hall_name"
        value="<?php echo htmlspecialchars($hall['hall_name']); ?>" required>

        <label>Place</label>
        <input type="text" name="location"
        value="<?php echo htmlspecialchars($hall['location']); ?>" required>

        <label>Capacity</label>
        <input type="number" name="capacity"
        value="<?php echo htmlspecialchars($hall['capacity']); ?>" required>

        <label>Price (₹)</label>
        <input type="number" step="0.01" name="price"
        value="<?php echo htmlspecialchars($hall['price']); ?>" required>

        <label>Facilities</label>
        <textarea name="facilities" required><?php echo htmlspecialchars($hall['facilities']); ?></textarea>

        <label>Description</label>
        <textarea name="description" required><?php echo htmlspecialchars($hall['description']); ?></textarea>

        <label>Upload New Hall Image (Optional)</label>
        <input type="file" name="image" accept="image/*">

        <button type="submit" class="submit-btn">
            Submit for Admin Approval
        </button>

    </form>

    <a href="owner_dashboard.php" class="back-link">
        ← Back to Owner Dashboard
    </a>

</div>

</body>
</html>
