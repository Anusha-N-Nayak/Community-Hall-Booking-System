<?php
include "db.php";

$hall_name = $_GET["hall"] ?? "";

$sql = "SELECT * FROM halls WHERE hall_name = ?";
$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "s", $hall_name);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$hall = mysqli_fetch_assoc($result);

if (!$hall) {
    die("<h2>Hall not found!</h2><a href='dashboard.php'>Back to Dashboard</a>");
}

$hall_id = (int)$hall["hall_id"];
$name = $hall["hall_name"];
$location = $hall["location"];
$capacity = $hall["capacity"];
$price = $hall["price"];
$image = $hall["image"];
$about = $hall["description"];
$facilities = $hall["facilities"];

$facility_list = array_filter(array_map("trim", explode(",", $facilities)));
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hall Details</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    background: #f2f5f9;
}

header {
    background: #142d4e;
    color: white;
    padding: 20px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

header a {
    color: white;
    text-decoration: none;
}

.container {
    max-width: 1000px;
    margin: 35px auto;
    padding: 25px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 3px 12px #ddd;
}

.container h1 {
    color: #142d4e;
    margin-bottom: 25px;
    text-align: center;
}

.hall-image {
    width: 100%;
    height: 350px;
    object-fit: cover;
    border-radius: 10px;
}

.details {
    padding: 25px 10px;
}

.details h2 {
    color: #142d4e;
    margin-bottom: 20px;
}

.details p {
    font-size: 16px;
    color: #444;
    margin-bottom: 15px;
    line-height: 1.6;
}

.details strong {
    color: #142d4e;
}

.facilities {
    margin-top: 20px;
}

.facilities h3 {
    color: #142d4e;
    margin-bottom: 15px;
}

.facilities ul {
    padding-left: 25px;
}

.facilities li {
    margin-bottom: 10px;
    color: #444;
}

.buttons {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    margin-top: 30px;
}

.buttons button {
    flex: 1;
    min-width: 150px;
    padding: 14px;
    border: none;
    border-radius: 6px;
    color: white;
    font-size: 15px;
    cursor: pointer;
}

.book { background: #e67e22; }
.availability { background: #1e6091; }
.feedback { background: #239b56; }
.back { background: #555; }

button:hover {
    opacity: 0.85;
}

footer {
    background: #142d4e;
    color: white;
    text-align: center;
    padding: 20px;
    margin-top: 40px;
}

@media(max-width: 600px) {
    header { padding: 18px; }
    .container {
        margin: 15px;
        padding: 15px;
    }
    .hall-image { height: 230px; }
    .buttons { flex-direction: column; }
}
</style>
</head>

<body>

<header>
    <h2>Community Hall Booking</h2>
    <a href="dashboard.php">Home</a>
</header>

<div class="container">

    <h1>Community Hall Details</h1>

    <img
        class="hall-image"
        src="<?php echo htmlspecialchars($image); ?>"
        alt="<?php echo htmlspecialchars($name); ?>"
    >

    <div class="details">

        <h2><?php echo htmlspecialchars($name); ?></h2>

        <p><strong>Location:</strong>
            <?php echo htmlspecialchars($location); ?>
        </p>

        <p><strong>Capacity:</strong>
            <?php echo htmlspecialchars($capacity); ?> People
        </p>

        <p><strong>Price:</strong>
            ₹<?php echo htmlspecialchars($price); ?>
        </p>

        <p><strong>About:</strong>
            <?php echo htmlspecialchars($about); ?>
        </p>

        <div class="facilities">
            <h3>Facilities Available</h3>

            <ul>
                <?php foreach ($facility_list as $item) { ?>
                    <li><?php echo htmlspecialchars($item); ?></li>
                <?php } ?>
            </ul>
        </div>

        <div class="buttons">

            <button class="book" onclick="bookHall()">
                Book Here
            </button>

            <button class="availability" onclick="checkAvailability()">
                Check Availability
            </button>

            <button class="feedback" onclick="viewFeedback()">
                View Feedback
            </button>

            <button class="back"
                onclick="location.href='dashboard.php'">
                Back to Dashboard
            </button>

        </div>
    </div>
</div>

<footer>
    <p>© 2026 Community Hall Booking and Management System</p>
</footer>

<script>
const hallName = <?php echo json_encode($name); ?>;
const hallId = <?php echo $hall_id; ?>;

function bookHall() {
    localStorage.setItem("selectedHall", hallName);
    localStorage.setItem("selectedHallId", hallId);

    window.location.href =
        "booking.html?hall=" + encodeURIComponent(hallName) +
        "&hall_id=" + hallId;
}

function checkAvailability() {
    window.location.href =
        "availability.html?hall=" + encodeURIComponent(hallName) +
        "&hall_id=" + hallId;
}

function viewFeedback() {
    window.location.href =
        "view_feedback.php?hall=" + encodeURIComponent(hallName);
}
</script>

</body>
</html>