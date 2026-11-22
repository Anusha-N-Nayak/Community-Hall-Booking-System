
<?php
session_start();
include "db.php";

// Only logged-in owners can access this page
if (!isset($_SESSION["owner_id"])) {
    header("Location: owner_login.php");
    exit;
}

$owner_id = (int)$_SESSION["owner_id"];

$hall_id = (int)($_POST["hall_id"] ?? $_GET["hall_id"] ?? 0);

if ($hall_id <= 0) {
    die("Invalid hall selected.");
}

// Verify that this hall belongs to the logged-in owner
$hall_sql = "SELECT hall_id, hall_name, location
             FROM halls
             WHERE hall_id = ? AND owner_id = ?";

$hall_stmt = mysqli_prepare($conn, $hall_sql);

mysqli_stmt_bind_param(
    $hall_stmt,
    "ii",
    $hall_id,
    $owner_id
);

mysqli_stmt_execute($hall_stmt);

$hall_result = mysqli_stmt_get_result($hall_stmt);

$hall = mysqli_fetch_assoc($hall_result);

mysqli_stmt_close($hall_stmt);

if (!$hall) {
    die("You are not authorized to manage this hall.");
}


// CSRF protection
if (empty($_SESSION["maintenance_token"])) {
    $_SESSION["maintenance_token"] = bin2hex(random_bytes(32));
}

$token = $_SESSION["maintenance_token"];

$message = "";
$messageType = "error";


// Check whether this hall already has an active booking
function hasActiveBooking($conn, $hall_id, $date) {

    $sql = "SELECT booking_id
            FROM bookings
            WHERE hall_id = ?
            AND event_date = ?
            AND LOWER(TRIM(COALESCE(booking_status, '')))
            NOT IN ('cancelled', 'canceled', 'expired', 'rejected')
            LIMIT 1";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "is",
        $hall_id,
        $date
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $exists = mysqli_num_rows($result) > 0;

    mysqli_stmt_close($stmt);

    return $exists;
}


// Validate date format
function validMaintenanceDate($date) {

    $dateObject = DateTime::createFromFormat("!Y-m-d", $date);

    return $dateObject &&
           $dateObject->format("Y-m-d") === $date;
}


// Process owner actions
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? "";

    if (!hash_equals($token, $_POST["csrf_token"] ?? "")) {

        $message = "Invalid request. Please refresh the page.";

    } else {

        $date = trim($_POST["maintenance_date"] ?? "");
        $reason = trim($_POST["reason"] ?? "");

        // ADD MAINTENANCE
        if ($action === "add") {

            if (!validMaintenanceDate($date)) {

                $message = "Please select a valid maintenance date.";

            } elseif (hasActiveBooking($conn, $hall_id, $date)) {

                $message = "Date Unavailable. This hall already has an existing booking on "
                         . $date . ". Please choose another date for maintenance.";

            } else {

                $sql = "INSERT INTO hall_maintenance
                        (hall_id, maintenance_date, reason)
                        VALUES (?, ?, ?)";

                $stmt = mysqli_prepare($conn, $sql);

                mysqli_stmt_bind_param(
                    $stmt,
                    "iss",
                    $hall_id,
                    $date,
                    $reason
                );

                if (mysqli_stmt_execute($stmt)) {

                    $message = "Maintenance date added successfully.";
                    $messageType = "success";

                } else {

                    $message = "This maintenance date may already exist. Please check your list.";
                }

                mysqli_stmt_close($stmt);
            }
        }


        // EDIT MAINTENANCE
        elseif ($action === "update") {

            $maintenance_id = (int)($_POST["maintenance_id"] ?? 0);

            $check_sql = "SELECT maintenance_id
                          FROM hall_maintenance
                          WHERE maintenance_id = ? AND hall_id = ?";

            $check_stmt = mysqli_prepare($conn, $check_sql);

            mysqli_stmt_bind_param(
                $check_stmt,
                "ii",
                $maintenance_id,
                $hall_id
            );

            mysqli_stmt_execute($check_stmt);

            $check_result = mysqli_stmt_get_result($check_stmt);

            $recordExists = mysqli_num_rows($check_result) > 0;

            mysqli_stmt_close($check_stmt);

            if (!$recordExists) {

                $message = "Maintenance record not found.";

            } elseif (!validMaintenanceDate($date)) {

                $message = "Please select a valid maintenance date.";

            } elseif (hasActiveBooking($conn, $hall_id, $date)) {

                $message = "Date Unavailable. This hall already has an existing booking on "
                         . $date . ". Please choose another date for maintenance.";

            } else {

                $update_sql = "UPDATE hall_maintenance
                               SET maintenance_date = ?, reason = ?
                               WHERE maintenance_id = ? AND hall_id = ?";

                $update_stmt = mysqli_prepare($conn, $update_sql);

                mysqli_stmt_bind_param(
                    $update_stmt,
                    "ssii",
                    $date,
                    $reason,
                    $maintenance_id,
                    $hall_id
                );

                if (mysqli_stmt_execute($update_stmt)) {

                    $message = "Maintenance details updated successfully.";
                    $messageType = "success";

                } else {

                    $message = "Unable to update. Please check whether this date already exists.";
                }

                mysqli_stmt_close($update_stmt);
            }
        }


        // DELETE MAINTENANCE
        elseif ($action === "delete") {

            $maintenance_id = (int)($_POST["maintenance_id"] ?? 0);

            $delete_sql = "DELETE FROM hall_maintenance
                           WHERE maintenance_id = ? AND hall_id = ?";

            $delete_stmt = mysqli_prepare($conn, $delete_sql);

            mysqli_stmt_bind_param(
                $delete_stmt,
                "ii",
                $maintenance_id,
                $hall_id
            );

            if (mysqli_stmt_execute($delete_stmt) &&
                mysqli_stmt_affected_rows($delete_stmt) > 0) {

                $message = "Maintenance date deleted successfully.";
                $messageType = "success";

            } else {

                $message = "Unable to delete this maintenance record.";
            }

            mysqli_stmt_close($delete_stmt);
        }

    }
}


// Fetch maintenance records for this hall
$list_sql = "SELECT *
             FROM hall_maintenance
             WHERE hall_id = ?
             ORDER BY maintenance_date ASC";

$list_stmt = mysqli_prepare($conn, $list_sql);

mysqli_stmt_bind_param($list_stmt, "i", $hall_id);

mysqli_stmt_execute($list_stmt);

$list_result = mysqli_stmt_get_result($list_stmt);


// Load a record when the owner clicks Edit
$editRecord = null;

$edit_id = (int)($_GET["edit"] ?? 0);

if ($edit_id > 0) {

    $edit_sql = "SELECT *
                 FROM hall_maintenance
                 WHERE maintenance_id = ? AND hall_id = ?";

    $edit_stmt = mysqli_prepare($conn, $edit_sql);

    mysqli_stmt_bind_param(
        $edit_stmt,
        "ii",
        $edit_id,
        $hall_id
    );

    mysqli_stmt_execute($edit_stmt);

    $edit_result = mysqli_stmt_get_result($edit_stmt);

    $editRecord = mysqli_fetch_assoc($edit_result);

    mysqli_stmt_close($edit_stmt);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Manage Hall Maintenance</title>

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    background: #f2f5f9;
    padding: 25px;
}

.container {
    max-width: 750px;
    background: white;
    margin: 20px auto;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 3px 12px #ddd;
}

h1 {
    color: #142d4e;
    text-align: center;
    margin-bottom: 12px;
}

.hall-name {
    text-align: center;
    color: #e67e22;
    font-size: 19px;
    margin-bottom: 25px;
}

h3 {
    color: #142d4e;
    margin-bottom: 18px;
}

label {
    display: block;
    margin-top: 15px;
    margin-bottom: 7px;
    font-weight: bold;
}

input, textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 15px;
}

button, .back {
    display: inline-block;
    padding: 11px 17px;
    border: none;
    border-radius: 6px;
    color: white;
    cursor: pointer;
    text-decoration: none;
    margin-top: 15px;
}

.submit {
    background: #239b56;
}

.edit {
    background: #2878d0;
}

.delete {
    background: #c0392b;
}

.back {
    background: #555;
}

.record {
    background: #f5f7fa;
    border-left: 4px solid #e67e22;
    padding: 16px;
    margin-top: 15px;
    border-radius: 6px;
}

.message {
    padding: 13px;
    margin-bottom: 20px;
    border-radius: 6px;
    text-align: center;
}

.success {
    background: #d4edda;
    color: #155724;
}

.error {
    background: #f8d7da;
    color: #721c24;
}

.empty {
    text-align: center;
    color: #666;
    padding: 20px;
}

</style>

</head>

<body>

<div class="container">

<h1>Manage Hall Maintenance</h1>

<div class="hall-name">
    <?php echo htmlspecialchars($hall["hall_name"]); ?>
</div>


<?php if ($message !== "") { ?>

    <div class="message <?php echo $messageType; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>

<?php } ?>


<h3>
    <?php echo $editRecord ? "Edit Maintenance Details" : "Add Maintenance Date"; ?>
</h3>

<p>
    Adding maintenance dates is optional.
    Leave this section unused when no maintenance is scheduled.
</p>


<form method="POST" action="owner_maintenance.php">

    <input type="hidden" name="hall_id"
           value="<?php echo $hall_id; ?>">

    <input type="hidden" name="csrf_token"
           value="<?php echo htmlspecialchars($token); ?>">

    <input type="hidden" name="action"
           value="<?php echo $editRecord ? "update" : "add"; ?>">

    <?php if ($editRecord) { ?>

        <input type="hidden" name="maintenance_id"
               value="<?php echo (int)$editRecord["maintenance_id"]; ?>">

    <?php } ?>


    <label>Maintenance Date</label>

    <input type="date"
           name="maintenance_date"
           required
           value="<?php echo htmlspecialchars($editRecord["maintenance_date"] ?? ""); ?>">


    <label>Reason (Optional)</label>

    <textarea name="reason" rows="3"
              placeholder="Example: Building repairs"><?php
        echo htmlspecialchars($editRecord["reason"] ?? "");
    ?></textarea>


    <button type="submit" class="submit">
        <?php echo $editRecord ? "Save Changes" : "Add Maintenance Date"; ?>
    </button>

    <?php if ($editRecord) { ?>

        <a class="back"
           href="owner_maintenance.php?hall_id=<?php echo $hall_id; ?>">
            Cancel
        </a>

    <?php } ?>

</form>


<hr style="margin:30px 0;">


<h3>Scheduled Maintenance</h3>


<?php if (mysqli_num_rows($list_result) > 0) { ?>

    <?php while ($record = mysqli_fetch_assoc($list_result)) { ?>

        <div class="record">

            <strong>
                <?php echo htmlspecialchars($record["maintenance_date"]); ?>
            </strong>

            <p style="margin-top:8px;">
                <?php
                echo htmlspecialchars(
                    $record["reason"] ?: "No reason specified"
                );
                ?>
            </p>


            <a class="edit"
               href="owner_maintenance.php?hall_id=<?php echo $hall_id; ?>&edit=<?php echo (int)$record["maintenance_id"]; ?>">
                Edit
            </a>


            <form method="POST"
                  action="owner_maintenance.php"
                  style="display:inline;">

                <input type="hidden" name="hall_id"
                       value="<?php echo $hall_id; ?>">

                <input type="hidden" name="csrf_token"
                       value="<?php echo htmlspecialchars($token); ?>">

                <input type="hidden" name="action"
                       value="delete">

                <input type="hidden" name="maintenance_id"
                       value="<?php echo (int)$record["maintenance_id"]; ?>">

                <button type="submit"
                        class="delete"
                        onclick="return confirm('Are you sure you want to delete this maintenance date?');">

                    Delete

                </button>

            </form>

        </div>

    <?php } ?>

<?php } else { ?>

    <div class="empty">
        No maintenance dates scheduled for this hall.
    </div>

<?php } ?>


<br>

<a href="owner_dashboard.php" class="back">
    Back to Owner Dashboard
</a>

</div>

</body>
</html>
