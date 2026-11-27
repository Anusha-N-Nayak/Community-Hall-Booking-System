<?php
session_start();
include "db.php";

if (!isset($_SESSION["admin_id"])) {
    header("Place: admin_login.php");
    exit();
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Approve or reject hall edit request
    if (isset($_POST["request_id"], $_POST["edit_action"])) {

        $request_id = (int)$_POST["request_id"];
        $action = $_POST["edit_action"];

        if (in_array($action, ["Approved", "Rejected"])) {

            $sql = "SELECT * FROM hall_edit_requests
                    WHERE request_id = ?
                    AND request_status = 'Pending'";

            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $request_id);
            mysqli_stmt_execute($stmt);

            $edit_result = mysqli_stmt_get_result($stmt);
            $request = mysqli_fetch_assoc($edit_result);

            if ($request) {

                if ($action == "Approved") {

                    $sql = "UPDATE halls SET
                            hall_name = ?,
                            location = ?,
                            capacity = ?,
                            price = ?,
                            facilities = ?,
                            description = ?,
                            image = ?
                            WHERE hall_id = ?";

                    $stmt = mysqli_prepare($conn, $sql);

                    mysqli_stmt_bind_param(
                        $stmt,
                        "ssidsssi",
                        $request["hall_name"],
                        $request["location"],
                        $request["capacity"],
                        $request["price"],
                        $request["facilities"],
                        $request["description"],
                        $request["image"],
                        $request["hall_id"]
                    );

                    if (!mysqli_stmt_execute($stmt)) {
                        die("Hall Update Error: " . mysqli_error($conn));
                    }
                }

                $rejection_reason = null;

if ($action == "Rejected") {
    $rejection_reason = trim($_POST["edit_rejection_reason"] ?? "");

    if ($rejection_reason == "") {
        die("Please enter a reason for rejecting the edit request.");
    }
}

$sql = "UPDATE hall_edit_requests
        SET request_status = ?, rejection_reason = ?
        WHERE request_id = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ssi",
    $action,
    $rejection_reason,
    $request_id
);

mysqli_stmt_execute($stmt);
            }
        }
    }
    // Approve or reject hall deletion requests
elseif (isset($_POST["delete_request_id"], $_POST["delete_action"])) {

    $request_id = (int)$_POST["delete_request_id"];
    $action = $_POST["delete_action"];

    if (in_array($action, ["Approved", "Rejected"])) {

        $sql = "SELECT * FROM hall_delete_requests
                WHERE request_id = ?
                AND request_status = 'Pending'";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $request_id);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $request = mysqli_fetch_assoc($result);

        if ($request) {

            $rejection_reason = null;

            if ($action == "Rejected") {

                $rejection_reason = trim(
                    $_POST["delete_rejection_reason"] ?? ""
                );

                if ($rejection_reason == "") {
                    die("Please enter a reason for rejecting the delete request.");
                }
            }

            if ($action == "Approved") {

                $sql = "UPDATE halls
                        SET hall_status = 'Deleted'
                        WHERE hall_id = ?";

                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param(
                    $stmt,
                    "i",
                    $request["hall_id"]
                );
                mysqli_stmt_execute($stmt);
            }

            $sql = "UPDATE hall_delete_requests
                    SET request_status = ?,
                        rejection_reason = ?
                    WHERE request_id = ?";

            $stmt = mysqli_prepare($conn, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "ssi",
                $action,
                $rejection_reason,
                $request_id
            );

            mysqli_stmt_execute($stmt);
        }
    }
}

    // Approve or reject newly added halls
   
// Approve or reject newly added halls
elseif (isset($_POST["hall_id"], $_POST["status"])) {

    $hall_id = (int)$_POST["hall_id"];
    $status = $_POST["status"];
    $reason = trim($_POST["rejection_reason"] ?? "");

    if (in_array($status, ["Approved", "Rejected"])) {

        if ($status == "Rejected" && $reason == "") {
            die("Please enter a rejection reason.");
        }

        if ($status == "Approved") {
            $reason = null;
        }

        $sql = "UPDATE halls
                SET hall_status = ?, rejection_reason = ?
                WHERE hall_id = ?";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssi", $status, $reason, $hall_id);
        mysqli_stmt_execute($stmt);
    }
}


    header("Location: admin_dashboard.php");
    exit();
}


$sql = "SELECT halls.*, owners.phone AS owner_phone
        FROM halls
        JOIN owners ON halls.owner_id = owners.owner_id
        ORDER BY halls.hall_id DESC";

$result = mysqli_query($conn, $sql);
if (!$result) {
    die("Database Error: " . mysqli_error($conn));
}
$edit_sql = "SELECT hall_edit_requests.*,
                    halls.hall_name AS current_hall_name,
                    halls.location AS current_location,
                    halls.capacity AS current_capacity,
                    halls.price AS current_price,
                    halls.facilities AS current_facilities,
                    halls.description AS current_description,
                    halls.image AS current_image,
                    owners.phone AS owner_phone
             FROM hall_edit_requests
             JOIN halls ON hall_edit_requests.hall_id = halls.hall_id
             JOIN owners ON hall_edit_requests.owner_id = owners.owner_id
             WHERE hall_edit_requests.request_status = 'Pending'
             ORDER BY hall_edit_requests.request_id DESC";

$edit_result = mysqli_query($conn, $edit_sql);

if (!$edit_result) {
    die("Edit Request Error: " . mysqli_error($conn));
}
$delete_sql = "SELECT hall_delete_requests.*,
                      halls.hall_name,
                      owners.phone AS owner_phone
               FROM hall_delete_requests
               JOIN halls ON hall_delete_requests.hall_id = halls.hall_id
               JOIN owners ON hall_delete_requests.owner_id = owners.owner_id
               WHERE hall_delete_requests.request_status = 'Pending'
               ORDER BY hall_delete_requests.request_id DESC";

$delete_result = mysqli_query($conn, $delete_sql);

if (!$delete_result) {
    die("Delete Request Error: " . mysqli_error($conn));
}

echo "Total Halls: " . mysqli_num_rows($result);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>

    <style>
        .section-heading {
            background: #1e3a8a;
            color: white;
            padding: 14px 20px;
            margin: 30px 0 20px;
            border-radius: 8px;
            font-size: 21px;
            font-weight: bold;
            border-left: 5px solid #60a5fa;
        }
    </style>

</head>
<body>

<div style="display:flex; justify-content:space-between;
            align-items:center; margin:20px;">

    <h1>Admin Dashboard</h1>

    <a href="admin_logout.php"
       style="background:#dc2626;
              color:white;
              padding:12px 22px;
              text-decoration:none;
              border-radius:6px;
              font-weight:bold;">
        Logout
    </a>

</div>

<h2>All Community Halls</h2>

<table border="1" cellpadding="10" cellspacing="0"
       style="width:100%; border-collapse:collapse; text-align:center;">

    <tr>
        <th>Hall Name</th>
        <th>Place</th>
        <th>Owner Mobile</th>
        <th>Capacity</th>
        <th>Price</th>
        <th>View Bookings</th>
        <th>Status</th>
        <th>Action</th>
        
    </tr>

    <?php while ($hall = mysqli_fetch_assoc($result)) { ?>

    <tr>
        <td><?php echo htmlspecialchars($hall["hall_name"]); ?></td>
        <td><?php echo htmlspecialchars($hall["location"]); ?></td>
        <td><?php echo htmlspecialchars($hall["owner_phone"]); ?></td>
        <td><?php echo htmlspecialchars($hall["capacity"]); ?></td>
        <td>₹<?php echo htmlspecialchars($hall["price"]); ?></td>

<td>
    <a href="view_hall_bookings.php?hall_id=<?php echo $hall['hall_id']; ?>">
        View Bookings
    </a>
</td>

<td><?php echo htmlspecialchars($hall["hall_status"]); ?></td>

        <td>
    <?php if ($hall["hall_status"] == "Pending") { ?>        
            
<form method="POST">
    <input type="hidden" name="hall_id"
           value="<?php echo $hall["hall_id"]; ?>">

    <button type="submit" name="status"
            value="Approved">Approve</button>

    <button type="button"
            onclick="document.getElementById('rejectBox<?php echo $hall['hall_id']; ?>').style.display='block';">
        Reject
    </button>

    <div id="rejectBox<?php echo $hall['hall_id']; ?>"
         style="display:none; margin-top:8px;">
<textarea name="edit_rejection_reason"
          placeholder="Enter rejection reason"
          rows="2"
          required
          style="width:180px; padding:6px;"></textarea>

<br><br>

        <button type="submit" name="status" value="Rejected">
            Confirm Rejection
        </button>
    </div>
</form>

            <?php } else { ?>
                —
            <?php } ?>
        </td>
    </tr>

    <?php } ?>

</table>
<h2 class="section-heading">Pending Hall Edit Requests</h2>

<?php while ($request = mysqli_fetch_assoc($edit_result)) { ?>

    <div style="border:1px solid #ccc; padding:15px; margin:15px;">

        <h3><?php echo htmlspecialchars($request["current_hall_name"]); ?></h3>

        <p>Requested Hall Name:
    <?php if ($request["hall_name"] != $request["current_hall_name"]) { ?>
        <span style="background:#fff3a6; padding:5px; border-radius:4px; font-weight:bold;">
            <?php echo htmlspecialchars($request["hall_name"]); ?>
        </span>
    <?php } else { ?>
        <?php echo htmlspecialchars($request["hall_name"]); ?>
    <?php } ?>
</p>

        <p>Owner Mobile Number:
            <a href="tel:<?php echo htmlspecialchars($request["owner_phone"]); ?>">
                <?php echo htmlspecialchars($request["owner_phone"]); ?>
            </a>
        </p>

       <p>Requested Place:
    <?php if ($request["location"] != $request["current_location"]) { ?>
        <span style="background:#fff3a6; padding:5px; border-radius:4px; font-weight:bold;">
            <?php echo htmlspecialchars($request["location"]); ?>
        </span>
    <?php } else { ?>
        <?php echo htmlspecialchars($request["location"]); ?>
    <?php } ?>
</p>

        <p>Requested Capacity:
    <?php if ($request["capacity"] != $request["current_capacity"]) { ?>
        <span style="background:#fff3a6; padding:5px; border-radius:4px; font-weight:bold;">
            <?php echo htmlspecialchars($request["capacity"]); ?>
        </span>
    <?php } else { ?>
        <?php echo htmlspecialchars($request["capacity"]); ?>
    <?php } ?>
</p>

        <p>Requested Price:
    <?php if ($request["price"] != $request["current_price"]) { ?>
        <span style="background:#fff3a6; padding:5px; border-radius:4px; font-weight:bold;">
            ₹<?php echo htmlspecialchars($request["price"]); ?>
        </span>
    <?php } else { ?>
        ₹<?php echo htmlspecialchars($request["price"]); ?>
    <?php } ?>
</p>

        <p>Requested Facilities:
    <?php if ($request["facilities"] != $request["current_facilities"]) { ?>
        <span style="background:#fff3a6; padding:5px; border-radius:4px; font-weight:bold;">
            <?php echo htmlspecialchars($request["facilities"]); ?>
        </span>
    <?php } else { ?>
        <?php echo htmlspecialchars($request["facilities"]); ?>
    <?php } ?>
</p>

       <p>Requested Description:
    <?php if ($request["description"] != $request["current_description"]) { ?>
        <span style="background:#fff3a6; padding:5px; border-radius:4px; font-weight:bold;">
            <?php echo htmlspecialchars($request["description"]); ?>
        </span>
    <?php } else { ?>
        <?php echo htmlspecialchars($request["description"]); ?>
    <?php } ?>
</p>
<p>Requested Hall Image:</p>

<?php if ($request["image"] != $request["current_image"]) { ?>

    <div style="background:#fff3a6; padding:10px;
                border:2px solid #eab308; border-radius:8px;
                display:inline-block;">

        <strong>Updated Image</strong><br>

        <img src="<?php echo htmlspecialchars($request["image"]); ?>"
             width="220"
             style="margin-top:8px; border-radius:6px;">

    </div>

<?php } else { ?>

    <img src="<?php echo htmlspecialchars($request["image"]); ?>"
         width="220"
         style="border-radius:6px;">

    <span> (No image changes)</span>

<?php } ?>

        <form method="POST">

    <input type="hidden" name="request_id"
           value="<?php echo $request["request_id"]; ?>">

    <button type="submit" name="edit_action"
            value="Approved" formnovalidate>
        Approve Edit
    </button>

    <br><br>

    <label>Reason for rejection:</label>

    <textarea name="edit_rejection_reason"
              placeholder="Enter rejection reason"
              rows="2"
              required
              style="width:180px; padding:6px;"></textarea>

    <br><br>

    <button type="submit" name="edit_action"
            value="Rejected">
        Reject Edit
    </button>

</form>

    </div>

<?php } ?>
<h2 class="section-heading">Pending Hall Delete Requests</h2>

<?php while ($request = mysqli_fetch_assoc($delete_result)) { ?>

    <div style="border:1px solid #ccc; padding:15px; margin:15px;">

        <h3><?php echo htmlspecialchars($request["hall_name"]); ?></h3>

        <p>Owner Mobile:
            <?php echo htmlspecialchars($request["owner_phone"]); ?>
        </p>

        <p>Request Status: Pending</p>

        <form method="POST">
    <input type="hidden" name="delete_request_id"
           value="<?php echo $request["request_id"]; ?>">

    <button type="submit" name="delete_action"
            value="Approved">Approve Delete</button>

    <br><br>

    <label>Reason for rejection:</label>
    <textarea name="delete_rejection_reason"
          placeholder="Enter rejection reason"
          rows="2"
          required
          style="width:180px; padding:6px;"></textarea>

<br><br>

    <button type="submit" name="delete_action"
            value="Rejected">Reject Delete</button>
</form>

    </div>

<?php } ?>



</body>
</html>