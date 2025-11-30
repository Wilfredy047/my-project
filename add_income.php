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

// Pata user_id
$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT id FROM users WHERE username=?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

// Handle form submission
if (isset($_POST['submit'])) {
    $amount = $_POST['amount'];
    $category = $_POST['category'];
    $date = $_POST['date'];
    $timeframe = $_POST['timeframe']; // daily, weekly, monthly

    $stmt = $conn->prepare("INSERT INTO income (user_id, amount, category, date, timeframe) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("idsss", $user_id, $amount, $category, $date, $timeframe);
    if ($stmt->execute()) {
        $success = $lang['income_added'] ?? "✅ Income imeongezwa!";
    } else {
        $error = $lang['income_error'] ?? "❌ Kuna tatizo, jaribu tena!";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?php echo $lang['add_income']; ?> - Finance Tracker</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <!-- Navbar -->
    <div class="navbar">
        <h2>Finance Tracker</h2>
        <div>
            <a href="dashboard.php" class="btn btn-primary">🏠 <?php echo $lang['dashboard']; ?></a>
            <a href="logout.php" class="btn btn-danger"><?php echo $lang['logout']; ?></a>

            <!-- Language Selector -->
            <form method="get" style="display:inline-block;">
                <select name="lang" onchange="this.form.submit()">
                    <option value="en" <?php if ($_SESSION['lang'] == 'en') echo 'selected'; ?>>English</option>
                    <option value="sw" <?php if ($_SESSION['lang'] == 'sw') echo 'selected'; ?>>Kiswahili</option>
                </select>
            </form>
        </div>
    </div>

    <!-- Main Container -->
    <div class="container" style="max-width:500px; margin-top:40px;">
        <div class="card">
            <h2 style="text-align:center; margin-bottom:20px; color:#2c3e50;">💰 <?php echo $lang['add_income']; ?></h2>

            <!-- Alerts -->
            <?php if (isset($success)) echo "<div class='alert alert-success'>{$success}</div>"; ?>
            <?php if (isset($error)) echo "<div class='alert alert-danger'>{$error}</div>"; ?>

            <!-- Income Form -->
            <form method="post" action="">
                <label><?php echo $lang['amount']; ?>:</label>
                <input type="number" step="0.01" name="amount" required placeholder="Add amount">

                <label><?php echo $lang['category']; ?>:</label>
                <input type="text" name="category" required placeholder="Example:Food,Rent,Bonus">

                <label><?php echo $lang['date']; ?>:</label>
                <input type="date" name="date" required>

                <label><?php echo $lang['timeframe']; ?>:</label>
                <select name="timeframe" required>
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly" selected>Monthly</option>
                </select>

                <button type="submit" name="submit" class="btn btn-primary" style="width:100%; margin-top:15px;">
                    <?php echo $lang['submit']; ?>
                </button>
            </form>
        </div>
    </div>
</body>

</html>
<?php include "include/footer.php"; ?>