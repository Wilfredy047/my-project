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

$username = $_SESSION['username'];

// Pata user_id
$stmt = $conn->prepare("SELECT id FROM users WHERE username=?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

// Pata total income na expense
$total_income = $conn->query("SELECT SUM(amount) AS total_income FROM income WHERE user_id=$user_id")->fetch_assoc()['total_income'] ?? 0;
$total_expense = $conn->query("SELECT SUM(amount) AS total_expense FROM expenses WHERE user_id=$user_id")->fetch_assoc()['total_expense'] ?? 0;

// Filter timeframe

$filter = $_GET['filter'] ?? 'monthly';

// Prepare chart data & summary
$income_data = [];
$expense_data = [];
$labels = [];
$summary = [];

switch ($filter) {
    case 'daily':
        $income_chart = $conn->query("SELECT DATE(date) AS period, SUM(amount) AS total FROM income WHERE user_id=$user_id GROUP BY period ORDER BY period");
        $expense_chart = $conn->query("SELECT DATE(date) AS period, SUM(amount) AS total FROM expenses WHERE user_id=$user_id GROUP BY period ORDER BY period");
        break;
    case 'weekly':
        $income_chart = $conn->query("SELECT YEAR(date) AS year, WEEK(date) AS week, SUM(amount) AS total FROM income WHERE user_id=$user_id GROUP BY year, week ORDER BY year, week");
        $expense_chart = $conn->query("SELECT YEAR(date) AS year, WEEK(date) AS week, SUM(amount) AS total FROM expenses WHERE user_id=$user_id GROUP BY year, week ORDER BY year, week");
        break;
    case 'monthly':
    default:
        $income_chart = $conn->query("SELECT DATE_FORMAT(date,'%Y-%m') AS period, SUM(amount) AS total FROM income WHERE user_id=$user_id GROUP BY period ORDER BY period");
        $expense_chart = $conn->query("SELECT DATE_FORMAT(date,'%Y-%m') AS period, SUM(amount) AS total FROM expenses WHERE user_id=$user_id GROUP BY period ORDER BY period");
        break;
}

// Prepare chart & summary arrays
$income_array = [];
$expense_array = [];
$labels = [];

while ($row = $income_chart->fetch_assoc()) {
    $label = $row['period'] ?? ($row['year'] . '-W' . $row['week']);
    $labels[] = $label;
    $income_array[$label] = $row['total'];
}
while ($row = $expense_chart->fetch_assoc()) {
    $label = $row['period'] ?? ($row['year'] . '-W' . $row['week']);
    $expense_array[$label] = $row['total'];
}

// Merge labels for summary
$all_labels = array_unique(array_merge(array_keys($income_array), array_keys($expense_array)));
sort($all_labels);
foreach ($all_labels as $label) {
    $summary[] = [
        'period' => $label,
        'income' => $income_array[$label] ?? 0,
        'expense' => $expense_array[$label] ?? 0
    ];
}

// Prepare chart arrays
$chart_labels = array_column($summary, 'period');
$chart_income = array_column($summary, 'income');
$chart_expense = array_column($summary, 'expense');

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard - Finance Tracker</title>
    <link rel="stylesheet" href="assets/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <?php include "include/header.php"; ?>

    <div class="container" style="max-width:900px; margin-top:40px;">
        <div class="card">
            <h3>Welcome, <?php echo $_SESSION['username']; ?></h3>
            <p><b>Total Income:</b> TZS <?php echo number_format($total_income); ?></p>
            <p><b>Total Expense:</b> TZS <?php echo number_format($total_expense); ?></p>
        </div>

        <div class="card">
            <form method="get" action="">
                <label>Filter by:</label>
                <select name="filter" onchange="this.form.submit()">
                    <option value="daily" <?php if ($filter == 'daily') echo 'selected'; ?>>Daily</option>
                    <option value="weekly" <?php if ($filter == 'weekly') echo 'selected'; ?>>Weekly</option>
                    <option value="monthly" <?php if ($filter == 'monthly') echo 'selected'; ?>>Monthly</option>
                </select>
            </form>
            <h3>Income vs Expense</h3>
            <canvas id="chart" width="800" height="300"></canvas>
        </div>

        <div class="card">
            <h3>Summary (<?php echo ucfirst($filter); ?>)</h3>
            <table border="1" style="width:100%; text-align:center; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th>Period</th>
                        <th>Income (TZS)</th>
                        <th>Expense (TZS)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($summary as $row): ?>
                        <tr>
                            <td><?php echo $row['period']; ?></td>
                            <td><?php echo number_format($row['income']); ?></td>
                            <td><?php echo number_format($row['expense']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>

    <script>
        const ctx = document.getElementById('chart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($chart_labels); ?>,
                datasets: [{
                        label: 'Income',
                        data: <?php echo json_encode($chart_income); ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.7)'
                    },
                    {
                        label: 'Expense',
                        data: <?php echo json_encode($chart_expense); ?>,
                        backgroundColor: 'rgba(255, 99, 132, 0.7)'
                    }
                ]
            },
            options: {
                responsive: true,
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