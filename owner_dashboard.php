
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

$edit_sql = "SELECT hall_edit_requests.*,
                    halls.hall_name AS current_hall_name,
                    halls.location AS current_location,
                    halls.capacity AS current_capacity,
                    halls.price AS current_price,
                    halls.facilities AS current_facilities,
                    halls.description AS current_description,
                    halls.image AS current_image
             FROM hall_edit_requests
             JOIN halls
             ON hall_edit_requests.hall_id = halls.hall_id
             WHERE hall_edit_requests.owner_id = ?
             ORDER BY hall_edit_requests.request_id DESC";

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
<a href="owner_monthly_revenue.php">Monthly Revenue</a><br><br>



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
        <th>Place</th>
        <th>Capacity</th>
        <th>Price</th>
        <th>Status</th>
        <th>Rejection Reason</th>
        <th>View Bookings</th>
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
    <?php
    if ($hall["hall_status"] == "Rejected" && !empty($hall["rejection_reason"])) {
        echo htmlspecialchars($hall["rejection_reason"]);
    } else {
        echo "—";
    }
    ?>
</td>
        <td>
    <a href="owner_bookings.php?hall_id=<?php echo $hall['hall_id']; ?>">
        View Bookings
    </a>
</td>

        <td style="white-space:nowrap; padding:12px;">

    <a href="owner_edit_hall.php?hall_id=<?php echo $hall['hall_id']; ?>"
       style="margin-right:12px;">
        Edit
    </a>

    <a href="owner_delete_request.php?hall_id=<?php echo $hall['hall_id']; ?>"
       style="margin-right:12px;">
        Delete
    </a>

    <a href="owner_maintenance.php?hall_id=<?php echo $hall['hall_id']; ?>">
        Manage Maintenance
    </a>

    <a href="view_feedback.php?hall_id=<?php echo $hall['hall_id']; ?>&amp;source=owner"
        style="margin-left:12px;">
    View Feedback
</a>

</td>
    </tr>

    <?php } ?>

</table>




<h3 style="margin-top:25px; color:#142d4e;">
    My Hall Edit Requests
</h3>

<div style="
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
    gap:15px;
    align-items:start;
">

<?php while ($request = mysqli_fetch_assoc($edit_requests)) { ?>

    <?php
    $status = $request["request_status"];

    $status_color = "#f59e0b";
    $status_bg = "#fef3c7";

    if ($status == "Approved") {
        $status_color = "#15803d";
        $status_bg = "#dcfce7";
    } elseif ($status == "Rejected") {
        $status_color = "#dc2626";
        $status_bg = "#fee2e2";
    }

    $fields = [
        "hall_name" => "Hall Name",
        "location" => "Location",
        "capacity" => "Capacity",
        "price" => "Price",
        "facilities" => "Facilities",
        "description" => "Description"
    ];

    $has_changes = false;
    ?>

    <div style="
        background:white;
        border:1px solid #dce3ec;
        border-radius:10px;
        padding:15px;
        box-shadow:0 2px 8px rgba(0,0,0,0.05);
    ">

        <div style="
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:8px;
            margin-bottom:12px;
        ">

            <strong style="color:#142d4e;">
                Edit Request #<?php echo $request["request_id"]; ?>
            </strong>

            <span style="
                background:<?php echo $status_bg; ?>;
                color:<?php echo $status_color; ?>;
                padding:5px 10px;
                border-radius:20px;
                font-size:12px;
                font-weight:bold;
                white-space:nowrap;
            ">
                <?php echo htmlspecialchars($status); ?>
            </span>

        </div>

        <?php foreach ($fields as $field => $label) { ?>

            <?php
            $previous = $request["previous_" . $field];
            $requested = $request[$field];

            if ($previous !== null &&
                (string)$previous !== (string)$requested) {

                $has_changes = true;
            ?>

                <p style="margin:8px 0; font-size:14px;">
                    <strong><?php echo $label; ?>:</strong>
                    <?php echo htmlspecialchars($requested); ?>
                </p>

            <?php } ?>

        <?php } ?>

        <?php
        $previous_image = $request["previous_image"];
        $requested_image = $request["image"];

        if ($previous_image !== null &&
            (string)$previous_image !== (string)$requested_image) {

            $has_changes = true;
        ?>

            <p style="font-size:14px; margin:8px 0;">
                <strong>Changed Image:</strong>
            </p>

            <?php if (!empty($requested_image)) { ?>
                <img src="<?php echo htmlspecialchars($requested_image); ?>"
                     style="width:110px; height:80px; object-fit:cover;
                            border-radius:7px;">
            <?php } ?>

        <?php } ?>
<?php if ($status == "Rejected" && !empty($request["rejection_reason"])) { ?>

    <div style="
        margin-top:10px;
        padding:10px;
        background:#fee2e2;
        color:#991b1b;
        border-radius:7px;
        font-size:13px;
    ">
        <strong>Admin's Rejection Reason:</strong><br>

        <?php echo nl2br(htmlspecialchars($request["rejection_reason"])); ?>
    </div>

<?php } ?>
        

    </div>

<?php } ?>

</div>


<h3 style="margin-top:25px; color:#142d4e;">
    My Hall Delete Requests
</h3>

<div style="
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
    gap:15px;
    align-items:start;
">

<?php while ($request = mysqli_fetch_assoc($delete_requests)) { ?>

    <?php
    $status = $request["request_status"];

    $status_color = "#f59e0b";
    $status_bg = "#fef3c7";

    if ($status == "Approved") {
        $status_color = "#15803d";
        $status_bg = "#dcfce7";
    } elseif ($status == "Rejected") {
        $status_color = "#dc2626";
        $status_bg = "#fee2e2";
    }
    ?>

    <div style="
        background:white;
        border:1px solid #dce3ec;
        border-radius:10px;
        padding:15px;
        box-shadow:0 2px 8px rgba(0,0,0,0.05);
    ">

        <div style="
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:8px;
            margin-bottom:12px;
        ">

            <strong style="color:#142d4e;">
                Delete Request #<?php echo $request["request_id"]; ?>
            </strong>

            <span style="
                background:<?php echo $status_bg; ?>;
                color:<?php echo $status_color; ?>;
                padding:5px 10px;
                border-radius:20px;
                font-size:12px;
                font-weight:bold;
            ">
                <?php echo htmlspecialchars($status); ?>
            </span>

        </div>

        <p style="font-size:14px; margin:8px 0;">
           <?php if ($status == "Rejected" && !empty($request["rejection_reason"])) { ?>

    <div style="
        margin-top:10px;
        padding:10px;
        background:#fee2e2;
        color:#991b1b;
        border-radius:7px;
        font-size:13px;
    ">
        <strong>Admin's Rejection Reason:</strong><br>

        <?php echo nl2br(htmlspecialchars($request["rejection_reason"])); ?>
    </div>

<?php } ?>
       
    </div>

<?php } ?>

</div>


<?php
} else {
    echo "You have not added any halls yet.";
}

mysqli_stmt_close($stmt);
?>
