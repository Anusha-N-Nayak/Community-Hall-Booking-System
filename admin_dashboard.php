<?php
session_start();
include "db.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
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

                $sql = "UPDATE hall_edit_requests
                        SET request_status = ?
                        WHERE request_id = ?";

                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "si", $action, $request_id);
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

            if ($action == "Approved") {

                $sql = "UPDATE halls
                        SET hall_status = 'Deleted'
                        WHERE hall_id = ?";

                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "i", $request["hall_id"]);
                mysqli_stmt_execute($stmt);
            }

            $sql = "UPDATE hall_delete_requests
                    SET request_status = ?
                    WHERE request_id = ?";

            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "si", $action, $request_id);
            mysqli_stmt_execute($stmt);
        }
    }
}

    // Approve or reject newly added halls
    elseif (isset($_POST["hall_id"], $_POST["status"])) {

        $hall_id = (int)$_POST["hall_id"];
        $status = $_POST["status"];

        if (in_array($status, ["Approved", "Rejected"])) {

            $sql = "UPDATE halls
                    SET hall_status = ?
                    WHERE hall_id = ?";

            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "si", $status, $hall_id);
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
        <th>Location</th>
        <th>Owner Mobile</th>
        <th>Capacity</th>
        <th>Price</th>
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
        <td><?php echo htmlspecialchars($hall["hall_status"]); ?></td>

        <td>
            <?php if ($hall["hall_status"] == "Pending") { ?>

                <form method="POST">
                    <input type="hidden" name="hall_id"
                           value="<?php echo $hall["hall_id"]; ?>">

                    <button type="submit" name="status"
                            value="Approved">Approve</button>

                    <button type="submit" name="status"
                            value="Rejected">Reject</button>
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
            <?php echo htmlspecialchars($request["hall_name"]); ?>
        </p>
        <p>Owner Mobile Number:
    <a href="tel:<?php echo htmlspecialchars($request["owner_phone"]); ?>">
        <?php echo htmlspecialchars($request["owner_phone"]); ?>
    </a>
</p>

        <p>Requested Location:
            <?php echo htmlspecialchars($request["location"]); ?>
        </p>

        <p>Requested Capacity:
            <?php echo htmlspecialchars($request["capacity"]); ?>
        </p>

        <p>Requested Price:
            ₹<?php echo htmlspecialchars($request["price"]); ?>
        </p>

        <p>Requested Facilities:
            <?php echo htmlspecialchars($request["facilities"]); ?>
        </p>

        <p>Requested Description:
            <?php echo htmlspecialchars($request["description"]); ?>
        </p>
        <form method="POST">
    <input type="hidden" name="request_id"
           value="<?php echo $request['request_id']; ?>">

    <button type="submit" name="edit_action"
            value="Approved">Approve Changes</button>

    <button type="submit" name="edit_action"
            value="Rejected">Reject Changes</button>
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

            <button type="submit" name="delete_action"
                    value="Rejected">Reject Delete</button>
        </form>

    </div>

<?php } ?>



</body>
</html>