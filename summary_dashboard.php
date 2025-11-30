<?php
session_start();
include "include/db.php";

// Multi-language setup
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
} elseif (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en';
}
$lang = include "lang/{$_SESSION['lang']}.php";

// Check login
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Get user_id
$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT id FROM users WHERE username=?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

// Default filter
$filter = $_GET['filter'] ?? 'daily';

// Build SQL based on filter
switch ($filter) {
    case 'weekly':
        $groupBy = "YEAR(date), WEEK(date)";
        $labelFormat = "CONCAT(YEAR(date), '-W', WEEK(date))";
        break;
    case 'monthly':
        $groupBy = "YEAR(date), MONTH(date)";
        $labelFormat = "DATE_FORMAT(date, '%Y-%m')";
        break;
    default:
        $groupBy = "DATE(date)";
        $labelFormat = "DATE(date)";
}

// Income query
$income_query = $conn->query("
    SELECT $labelFormat AS period, SUM(amount) AS total 
    FROM income 
    WHERE user_id=$user_id 
    GROUP BY $groupBy 
    ORDER BY MIN(date)
");

// Expense query
$expense_query = $conn->query("
    SELECT $labelFormat AS period, SUM(amount) AS total 
    FROM expenses 
    WHERE user_id=$user_id 
    GROUP BY $groupBy 
    ORDER BY MIN(date)
");

// Format data for chart and table
$labels = [];
$income_data = [];
$expense_data = [];
$table_data = [];

while ($row = $income_query->fetch_assoc()) {
    $labels[] = $row['period'];
    $income_data[$row['period']] = $row['total'];
}
while ($row = $expense_query->fetch_assoc()) {
    $expense_data[$row['period']] = $row['total'];
}

// Align datasets
$final_income = [];
$final_expense = [];
foreach ($labels as $label) {
    $inc = $income_data[$label] ?? 0;
    $exp = $expense_data[$label] ?? 0;
    $final_income[] = $inc;
    $final_expense[] = $exp;

    // Prepare table row
    $table_data[] = [
        'period' => $label,
        'income' => $inc,
        'expense' => $exp,
        'balance' => $inc - $exp
    ];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?php echo $lang['summary']; ?> - Finance Tracker</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <div class="navbar">
        <h2>Finance Tracker</h2>
        <div>
            <a href="dashboard.php" class="btn btn-primary"><?php echo $lang['dashboard']; ?></a>
            <a href="logout.php" class="btn btn-danger"><?php echo $lang['logout']; ?></a>
        </div>
    </div>

    <!-- Container -->
    <div class="container">
        <div class="card">
            <h2 style="text-align:center;"><?php echo $lang['summary']; ?> (<?php echo ucfirst($filter); ?>)</h2>

            <!-- Filter -->
            <form method="get" style="text-align:center; margin-bottom:20px;">
                <label><b><?php echo $lang['filter_by']; ?></b></label>
                <select name="filter" onchange="this.form.submit()">
                    <option value="daily" <?php if ($filter == 'daily') echo 'selected'; ?>>
                        <?php echo $lang['daily']; ?></option>
                    <option value="weekly" <?php if ($filter == 'weekly') echo 'selected'; ?>>
                        <?php echo $lang['weekly']; ?></option>
                    <option value="monthly" <?php if ($filter == 'monthly') echo 'selected'; ?>>
                        <?php echo $lang['monthly']; ?></option>
                </select>
            </form>

            <!-- Chart -->
            <canvas id="reportChart" width="600" height="300"></canvas>

            <!-- Table -->
            <table>
                <thead>
                    <tr>
                        <th><?php echo $lang['period']; ?></th>
                        <th><?php echo $lang['income']; ?></th>
                        <th><?php echo $lang['expense']; ?></th>
                        <th><?php echo $lang['balance']; ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total_income_sum = 0;
                    $total_expense_sum = 0;
                    $total_balance_sum = 0;

                    foreach ($table_data as $row):
                        $total_income_sum += $row['income'];
                        $total_expense_sum += $row['expense'];
                        $total_balance_sum += $row['balance'];
                    ?>
                        <tr>
                            <td><?php echo $row['period']; ?></td>
                            <td><?php echo number_format($row['income']); ?></td>
                            <td><?php echo number_format($row['expense']); ?></td>
                            <td><?php echo number_format($row['balance']); ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <!-- Total row -->
                    <tr style="font-weight:bold; background-color:#f2f2f2;">
                        <td>Total</td>
                        <td><?php echo number_format($total_income_sum); ?></td>
                        <td><?php echo number_format($total_expense_sum); ?></td>
                        <td><?php echo number_format($total_balance_sum); ?></td>
                    </tr>
                </tbody>
            </table>

        </div>
    </div>

    <script>
        const ctx = document.getElementById('reportChart').getContext('2d');
        const reportChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                        label: '<?php echo $lang['income']; ?>',
                        data: <?php echo json_encode($final_income); ?>,
                        borderColor: 'rgba(54, 162, 235, 1)',
                        backgroundColor: 'rgba(54, 162, 235, 0.3)',
                        fill: true
                    },
                    {
                        label: '<?php echo $lang['expense']; ?>',
                        data: <?php echo json_encode($final_expense); ?>,
                        borderColor: 'rgba(255, 99, 132, 1)',
                        backgroundColor: 'rgba(255, 99, 132, 0.3)',
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>

</html>
<?php include "include/footer.php"; ?>