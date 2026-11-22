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
$edit_sql = "SELECT * FROM hall_edit_requests
             WHERE owner_id = ?
             ORDER BY request_id DESC";

$edit_stmt = mysqli_prepare($conn, $edit_sql);
mysqli_stmt_bind_param($edit_stmt, "i", $owner_id);
mysqli_stmt_execute($edit_stmt);

$edit_requests = mysqli_stmt_get_result($edit_stmt);
$delete_sql = "SELECT * FROM hall_delete_requests
               WHERE owner_id = ?
               ORDER BY request_id DESC";

$delete_stmt = mysqli_prepare($conn, $delete_sql);
mysqli_stmt_bind_param($delete_stmt, "i", $owner_id);
mysqli_stmt_execute($delete_stmt);

$delete_requests = mysqli_stmt_get_result($delete_stmt);
?>

<h2>Welcome, <?php echo htmlspecialchars($owner_name); ?>!</h2>
<a href="owner_add_hall.php">Add Hall</a><br><br>

<a href="owner_bookings.php">View Bookings</a><br><br>

<a href="owner_logout.php">Logout</a>

<h3>Your Halls</h3>



<?php
if (mysqli_num_rows($result) > 0) {
?>

<div style="overflow-x:auto;">

<table border="1" cellpadding="10" cellspacing="0"
       style="width:100%; border-collapse:collapse; text-align:center;">

    <tr style="background:#142d4e; color:white;">
        <th>Hall Name</th>
        <th>Location</th>
        <th>Capacity</th>
        <th>Price</th>
        <th>Status</th>
        <th>Action</th>
    </tr>

    <?php while ($hall = mysqli_fetch_assoc($result)) { ?>

    <tr>
        <td><?php echo htmlspecialchars($hall["hall_name"]); ?></td>

        <td><?php echo htmlspecialchars($hall["location"]); ?></td>

        <td><?php echo htmlspecialchars($hall["capacity"]); ?></td>

        <td>₹<?php echo htmlspecialchars($hall["price"]); ?></td>

        <td><?php echo htmlspecialchars($hall["hall_status"]); ?></td>

        <td>
            <a href="owner_edit_hall.php?hall_id=<?php echo $hall['hall_id']; ?>">
                Edit
            </a>

            |

            <a href="owner_delete_request.php?hall_id=<?php echo $hall['hall_id']; ?>">
                Delete
            </a>
        </td>
    </tr>

    <?php } ?>

</table>
<h3>My Hall Edit Requests</h3>
<?php while ($request = mysqli_fetch_assoc($edit_requests)) { ?>

    <div style="border:1px solid #ccc; padding:15px; margin:15px;">

        <h4><?php echo htmlspecialchars($request["hall_name"]); ?></h4>

        <p>Requested Price: ₹<?php echo htmlspecialchars($request["price"]); ?></p>

        <p>Request Status:
            <strong>
                <?php echo htmlspecialchars($request["request_status"]); ?>
            </strong>
        </p>

    </div>

<?php } ?>

</div>
<h3>My Hall Delete Requests</h3>

<?php while ($request = mysqli_fetch_assoc($delete_requests)) { ?>

    <div style="border:1px solid #ccc; padding:15px; margin:15px;">

        <p>Hall ID:
            <?php echo htmlspecialchars($request["hall_id"]); ?>
        </p>

        <p>Request Status:
            <strong>
                <?php echo htmlspecialchars($request["request_status"]); ?>
            </strong>
        </p>

    </div>

<?php } ?>
<?php
} else {
    echo "You have not added any halls yet.";
}
?>