
<?php
session_start();
include "db.php";

if (!isset($_SESSION["owner_id"])) {
    die("Please login first.");
}

$owner_id = $_SESSION["owner_id"];
$owner_name = $_SESSION["owner_name"];

$sql = "SELECT * FROM halls WHERE owner_id = ?";
$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $owner_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
?>

<h2>Welcome, <?php echo htmlspecialchars($owner_name); ?>!</h2>

<a href="owner_add_hall.php">Add Hall</a><br><br>

<a href="owner_bookings.php">View Bookings</a><br><br>

<a href="owner_logout.php">Logout</a>

<h3>Your Halls</h3>

<?php
if (mysqli_num_rows($result) > 0) {

    while ($hall = mysqli_fetch_assoc($result)) {

        echo "<p>";

        echo htmlspecialchars($hall["hall_name"]);
        echo " - ";
        echo htmlspecialchars($hall["location"]);

        if ($hall["hall_status"] == "Inactive") {
            echo " (Inactive)";
        }

        echo ' | <a href="owner_maintenance.php?hall_id='
             . (int)$hall["hall_id"]
             . '">Manage Maintenance</a>';


        echo "</p><hr>";
    }

} else {
    echo "You have not added any halls yet.";
}

mysqli_stmt_close($stmt);
?>
