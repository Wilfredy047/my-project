<?php
session_start();
include "include/db.php";

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Pata user_id
$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT id FROM users WHERE username=?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

// Pata data zote
$income = $conn->query("SELECT * FROM income WHERE user_id=$user_id ORDER BY date DESC");
$expenses = $conn->query("SELECT * FROM expenses WHERE user_id=$user_id ORDER BY date DESC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>View Records - Finance Tracker</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <!-- Navbar -->
    <div class="navbar">
        <h2>Finance Tracker</h2>
        <div>
            <a href="dashboard.php" class="btn btn-primary">🏠 Dashboard</a>
            <a href="export_excel.php" class="btn btn-success" style="margin-bottom:15px;">
                📥 Download Excel
            </a>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>

    <div class="container">
        <h2 style="text-align:center; margin:20px 0; color:#2c3e50;">📊 My Financial Records</h2>

        <!-- Income Table -->
        <div class="card">
            <h3 style="color:#27ae60; margin-bottom:15px;">💰 Income</h3>
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Amount (TZS)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($income->num_rows > 0): ?>
                    <?php while ($row = $income->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['date']; ?></td>
                        <td><?php echo $row['category']; ?></td>
                        <td style="color:green;"><b><?php echo number_format($row['amount'], 2); ?></b></td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align:center;">No Income</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Expenses Table -->
        <div class="card" style="margin-top:30px;">
            <h3 style="color:#c0392b; margin-bottom:15px;">💸 Expenses</h3>
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Amount (TZS)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($expenses->num_rows > 0): ?>
                    <?php while ($row = $expenses->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['date']; ?></td>
                        <td><?php echo $row['category']; ?></td>
                        <td style="color:red;"><b><?php echo number_format($row['amount'], 2); ?></b></td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align:center;">No expenses</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>
<?php include "include/footer.php"; ?>