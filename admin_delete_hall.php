<?php
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $hall_id = (int) $_POST["hall_id"];

    // Check whether the hall has existing bookings
    $check_sql = "SELECT booking_id FROM bookings WHERE hall_id = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);

    mysqli_stmt_bind_param($check_stmt, "i", $hall_id);
    mysqli_stmt_execute($check_stmt);

    $result = mysqli_stmt_get_result($check_stmt);

    if (mysqli_num_rows($result) > 0) {

        echo "Cannot delete this hall because booking records exist.";

    } else {

        // Delete hall details
        $sql = "DELETE FROM halls WHERE hall_id = ?";
        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param($stmt, "i", $hall_id);

        if (mysqli_stmt_execute($stmt)) {

            if (mysqli_stmt_affected_rows($stmt) > 0) {
                echo "Hall deleted successfully!";
            } else {
                echo "Hall not found.";
            }

        } else {
            echo "Failed to delete hall.";
        }
    }

} else {
    echo "Please provide hall ID.";
}
?>