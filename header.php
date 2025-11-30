<!-- header.php -->
<?php
session_start();
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en';
}
$lang = include "lang/{$_SESSION['lang']}.php";
?>

<div class="navbar">
    <h2>Finance Tracker</h2>
    <div>
        <a href="add_income.php" class="btn btn-primary"><?php echo $lang['add_income']; ?></a>
        <a href="add_expense.php" class="btn btn-primary"><?php echo $lang['add_expense']; ?></a>
        <a href="view_records.php" class="btn btn-primary"><?php echo $lang['view_records']; ?></a>
        <a href="summary_dashboard.php" class="btn btn-primary"><?php echo $lang['summary_dashboard']; ?></a>

        <a href="logout.php" class="btn btn-danger"><?php echo $lang['logout']; ?></a>
        <form method="get" style="display:inline-block;">
            <select name="lang" onchange="this.form.submit()">
                <option value="en" <?php if ($_SESSION['lang'] == 'en') echo 'selected'; ?>>English</option>
                <option value="sw" <?php if ($_SESSION['lang'] == 'sw') echo 'selected'; ?>>Kiswahili</option>
            </select>
        </form>
    </div>
</div>