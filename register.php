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

// Register processing
if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if username exists
    $check = $conn->prepare("SELECT id FROM users WHERE username=?");
    $check->bind_param("s", $username);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = $lang['username_taken'] ?? "⚠️ Username tayari imechukuliwa!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $hashed_password);
        if ($stmt->execute()) {
            $_SESSION['username'] = $username;
            header("Location: dashboard.php");
            exit();
        } else {
            $error = $lang['register_error'] ?? "❌ Kuna tatizo, jaribu tena!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?php echo $lang['register']; ?> - Finance Tracker</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <div class="container" style="max-width:400px; margin-top:100px;">
        <div class="card">
            <h2 style="text-align:center; margin-bottom:20px; color:#2c3e50;">📝 <?php echo $lang['register']; ?></h2>

            <!-- Error -->
            <?php if (isset($error)): ?>
                <div class="alert alert-danger" style="margin-bottom:15px;"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="post" action="">
                <label><?php echo $lang['username']; ?></label>
                <input type="text" name="username" required placeholder="<?php echo $lang['username_placeholder']; ?>">

                <label><?php echo $lang['password']; ?></label>
                <input type="password" name="password" required
                    placeholder="<?php echo $lang['password_placeholder']; ?>">

                <button type="submit" name="register" class="btn btn-primary" style="width:100%; margin-top:10px;">
                    <?php echo $lang['register']; ?>
                </button>
            </form>

            <p style="text-align:center; margin-top:15px;">
                <?php echo $lang['have_account']; ?> <a href="index.php"
                    style="color:#1abc9c; font-weight:bold;"><?php echo $lang['login_here']; ?></a>
            </p>

            <!-- Language Selector -->
            <div style="text-align:center; margin-top:10px;">
                <form method="get">
                    <select name="lang" onchange="this.form.submit()">
                        <option value="en" <?php if ($_SESSION['lang'] == 'en') echo 'selected'; ?>>English</option>
                        <option value="sw" <?php if ($_SESSION['lang'] == 'sw') echo 'selected'; ?>>Kiswahili</option>
                    </select>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
<?php include "include/footer.php"; ?>