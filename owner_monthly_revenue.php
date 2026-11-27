
<?php
session_start();
include "db.php";

if (!isset($_SESSION["owner_id"])) {
    die("Please login first.");
}

$owner_id = $_SESSION["owner_id"];

// Fetch approved halls belonging to this owner
$hall_sql = "SELECT hall_id, hall_name
             FROM halls
             WHERE owner_id = ?
             AND LOWER(hall_status) = 'approved'
             ORDER BY hall_name ASC";

$hall_stmt = mysqli_prepare($conn, $hall_sql);
mysqli_stmt_bind_param($hall_stmt, "i", $owner_id);
mysqli_stmt_execute($hall_stmt);

$halls = mysqli_stmt_get_result($hall_stmt);

// Get selected hall and month
$selected_hall = isset($_GET["hall_id"])
    ? (int)$_GET["hall_id"]
    : 0;

$selected_month = $_GET["month"] ?? date("Y-m");

$revenue = 0;
$history_result = null;
$chart_labels = [];
$chart_values = [];

if ($selected_hall > 0) {

    // Calculate selected month's revenue
    $revenue_sql = "SELECT COALESCE(SUM(p.amount), 0) AS total_revenue
                    FROM payments p
                    JOIN bookings b ON p.booking_id = b.booking_id
                    JOIN halls h ON b.hall_id = h.hall_id
                    WHERE h.owner_id = ?
                    AND h.hall_id = ?
                    AND LOWER(h.hall_status) = 'approved'
                    AND DATE_FORMAT(p.paid_at, '%Y-%m') = ?
                    AND p.payment_status = 'Successful'";

    $revenue_stmt = mysqli_prepare($conn, $revenue_sql);

    mysqli_stmt_bind_param(
        $revenue_stmt,
        "iis",
        $owner_id,
        $selected_hall,
        $selected_month
    );

    mysqli_stmt_execute($revenue_stmt);

    $revenue_result = mysqli_stmt_get_result($revenue_stmt);
    $revenue_data = mysqli_fetch_assoc($revenue_result);

    $revenue = $revenue_data["total_revenue"];

    // Fetch previous months' revenue
    $history_sql = "SELECT
                        DATE_FORMAT(p.paid_at, '%Y-%m') AS revenue_month,
                        SUM(p.amount) AS monthly_revenue
                    FROM payments p
                    JOIN bookings b ON p.booking_id = b.booking_id
                    JOIN halls h ON b.hall_id = h.hall_id
                    WHERE h.owner_id = ?
                    AND h.hall_id = ?
                    AND LOWER(h.hall_status) = 'approved'
                    AND p.payment_status = 'Successful'
                    GROUP BY DATE_FORMAT(p.paid_at, '%Y-%m')
                    ORDER BY revenue_month DESC";

    $history_stmt = mysqli_prepare($conn, $history_sql);

    mysqli_stmt_bind_param(
        $history_stmt,
        "ii",
        $owner_id,
        $selected_hall
    );

    mysqli_stmt_execute($history_stmt);

    $history_result = mysqli_stmt_get_result($history_stmt);

    // Fetch last 6 months' revenue for chart
    $chart_sql = "SELECT
                      DATE_FORMAT(p.paid_at, '%Y-%m') AS revenue_month,
                      SUM(p.amount) AS monthly_revenue
                  FROM payments p
                  JOIN bookings b ON p.booking_id = b.booking_id
                  JOIN halls h ON b.hall_id = h.hall_id
                  WHERE h.owner_id = ?
                  AND h.hall_id = ?
                  AND LOWER(h.hall_status) = 'approved'
                  AND p.payment_status = 'Successful'
                  AND p.paid_at >= DATE_FORMAT(
                      DATE_SUB(CURDATE(), INTERVAL 5 MONTH),
                      '%Y-%m-01'
                  )
                  GROUP BY DATE_FORMAT(p.paid_at, '%Y-%m')
                  ORDER BY revenue_month ASC";

    $chart_stmt = mysqli_prepare($conn, $chart_sql);

    mysqli_stmt_bind_param(
        $chart_stmt,
        "ii",
        $owner_id,
        $selected_hall
    );

    mysqli_stmt_execute($chart_stmt);

    $chart_result = mysqli_stmt_get_result($chart_stmt);

    // Prepare chart data
   // Prepare all 6 months for chart
$monthly_revenue = [];

// Initialize the last 6 months with zero revenue
for ($i = 5; $i >= 0; $i--) {

    $month = date("Y-m", strtotime("-$i months"));

    $monthly_revenue[$month] = 0;
}

// Add actual revenue from database
while ($chart = mysqli_fetch_assoc($chart_result)) {

    $monthly_revenue[$chart['revenue_month']] =
        (float)$chart['monthly_revenue'];
}

// Prepare chart labels and values
foreach ($monthly_revenue as $month => $amount) {

    $chart_labels[] = date("M Y", strtotime($month . "-01"));

    $chart_values[] = $amount;
}
}
?>


<!DOCTYPE html>
<html lang="en">
 <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Monthly Revenue</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    padding: 30px;
    font-family: Arial, sans-serif;
    background: #f5f3ff;
    color: #292638;
}

.container {
    max-width: 1000px;
    margin: auto;
}

.back-btn {
    display: inline-block;
    padding: 11px 20px;
    background: #6d5bd0;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    margin-bottom: 25px;
}

.back-btn:hover {
    background: #5746b5;
}

.page-heading h2 {
    color: #292638;
    font-size: 28px;
}

.page-heading p {
    color: #77738a;
}

.card {
    background: white;
    padding: 25px;
    border-radius: 14px;
    margin-bottom: 25px;
    box-shadow: 0 4px 15px rgba(40, 30, 80, 0.06);
}

.card h3 {
    color: #292638;
}

.form-row {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.form-group {
    flex: 1;
    min-width: 220px;
}

label {
    display: block;
    margin-bottom: 9px;
    font-weight: bold;
}

select, input[type="month"] {
    width: 100%;
    padding: 12px;
    border: 1px solid #e2def3;
    border-radius: 8px;
    font-size: 15px;
    background: white;
}

.submit-btn {
    margin-top: 22px;
    padding: 12px 25px;
    background: #6d5bd0;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    cursor: pointer;
}

.submit-btn:hover {
    background: #5746b5;
}

.revenue-card {
    background: white;
    color: #292638;
    padding: 30px;
    border-radius: 14px;
    border-left: 5px solid #6d5bd0;
    box-shadow: 0 4px 15px rgba(40, 30, 80, 0.06);
    margin-bottom: 25px;
}

.revenue-card p {
    color: #77738a;
}

.revenue-amount {
    font-size: 34px;
    font-weight: bold;
    color: #6d5bd0;
    margin: 15px 0;
}

.note {
    font-size: 13px;
    color: #77738a;
}


@media(max-width: 600px) {
    .history-table th,
    .history-table td {
        padding: 10px;
        font-size: 13px;
    }
}
@media(max-width: 600px) {
    body {
        padding: 15px;
    }

    .revenue-amount {
        font-size: 27px;
    }
}

</style>
</head>

<body>

<div class="container">

    <a href="owner_dashboard.php" class="back-btn">
        ← Back to Dashboard
    </a>

    <div class="page-heading">
        <h2>Monthly Revenue</h2>
        <p>Track and monitor the monthly earnings of your halls.</p>
    </div>

    <div class="card">

        <h3>Select Revenue Details</h3>

        <form method="GET">

            <div class="form-row">

                <div class="form-group">
                    <label>Select Hall</label>

                    <select name="hall_id" required>
                        <option value="">Choose a Hall</option>

                        <?php while ($hall = mysqli_fetch_assoc($halls)) { ?>

                            <option value="<?php echo $hall['hall_id']; ?>"
                                <?php echo ($selected_hall == $hall['hall_id']) ? "selected" : ""; ?>>

                                <?php echo htmlspecialchars($hall['hall_name']); ?>

                            </option>

                        <?php } ?>

                    </select>
                </div>

                <div class="form-group">
                    <label>Select Month</label>

                    <input type="month"
                           name="month"
                           value="<?php echo htmlspecialchars($selected_month); ?>"
                           required>
                </div>

            </div>

            <button type="submit" class="submit-btn">
                View Revenue
            </button>

        </form>

    </div>

    <?php if ($selected_hall > 0) { ?>

    <div class="revenue-card">

        <p>Monthly Revenue</p>

        <h3>
            <?php echo date("F Y", strtotime($selected_month . "-01")); ?>
        </h3>

        <div class="revenue-amount">
            ₹<?php echo number_format($revenue, 2); ?>
        </div>

        <p class="note">
            Revenue calculated from successful payments only.
        </p>

    </div>

<?php } ?>
<?php if ($selected_hall > 0) { ?>

    <div class="card">

        <h3>Revenue Overview – Last 6 Months</h3>

        <canvas id="revenueChart"></canvas>

    </div>

    <script>
        const labels = <?php echo json_encode($chart_labels); ?>;
        const values = <?php echo json_encode($chart_values); ?>;

        const ctx = document.getElementById('revenueChart');

        new Chart(ctx, {
            type: 'bar',

            data: {
                labels: labels,

                datasets: [{
                    label: 'Monthly Revenue (₹)',
                    data: values,
                    backgroundColor: '#8b7bd8',
                    borderRadius: 6
                }]
            },

            options: {
                responsive: true,

                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Revenue (₹)'
                        }
                    },

                    x: {
                        title: {
                            display: true,
                            text: 'Month'
                        }
                    }
                }
            }
        });
    </script>

<?php } ?>



</div>

</body>
</html>