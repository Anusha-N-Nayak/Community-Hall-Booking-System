<?php
include "db.php";

$sql = "SELECT *
        FROM halls
        WHERE LOWER(TRIM(COALESCE(hall_status, ''))) = 'approved'
        ORDER BY hall_id ASC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Error fetching halls: " . mysqli_error($conn));
}

while ($hall = mysqli_fetch_assoc($result)):
?>

<div class="hall-card">

    <img
        src="<?php echo htmlspecialchars($hall['image']); ?>"
        alt="<?php echo htmlspecialchars($hall['hall_name']); ?>">

    <div class="hall-info">

        <h3><?php echo htmlspecialchars($hall['hall_name']); ?></h3>

        <p><b>Place:</b>
            <?php echo htmlspecialchars($hall['location']); ?>
        </p>

        <p><b>Capacity:</b>
            <?php echo htmlspecialchars($hall['capacity']); ?> People
        </p>

        <p><b>Price:</b>
            ₹<?php echo htmlspecialchars($hall['price']); ?>
        </p>

        <p><b>Facilities:</b>
            <?php echo htmlspecialchars($hall['facilities']); ?>
        </p>

        <button class="details-btn"
            onclick="viewDetails('<?php echo htmlspecialchars(addslashes($hall['hall_name'])); ?>')">
            View Details
        </button>

    </div>
</div>

<?php endwhile; ?>