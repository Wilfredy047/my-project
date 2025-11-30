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

// Login processing
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $hashed_password);
    $stmt->fetch();

    if ($stmt->num_rows > 0 && password_verify($password, $hashed_password)) {
        $_SESSION['username'] = $username;
        header("Location: dashboard.php");
        exit();
    } else {
        $error = $lang['login_error'] ?? "⚠️ Username au password sio sahihi!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?php echo $lang['login']; ?> - Finance Tracker</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <div class="container" style="max-width:400px; margin-top:100px;">
        <div class="card" style="padding:20px; box-shadow:0 0 10px rgba(0,0,0,0.1); border-radius:10px;">

            <!-- Title -->
            <h2 style="text-align:center; margin-bottom:20px; color:#2c3e50;">
                🔑 <?php echo $lang['login']; ?>
            </h2>

            <!-- Error Message -->
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"
                    style="margin-bottom:15px; padding:10px; background:#f8d7da; color:#721c24; border-radius:5px;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="post" action="">
                <label><?php echo $lang['username']; ?></label>
                <input type="text" name="username" required placeholder="<?php echo $lang['username_placeholder']; ?>"
                    style="width:100%; padding:10px; margin-bottom:10px; border-radius:5px; border:1px solid #ccc;">

                <label><?php echo $lang['password']; ?></label>
                <input type="password" name="password" required
                    placeholder="<?php echo $lang['password_placeholder']; ?>"
                    style="width:100%; padding:10px; margin-bottom:15px; border-radius:5px; border:1px solid #ccc;">

                <button type="submit" name="login"
                    style="width:100%; padding:10px; background:#1abc9c; color:white; font-weight:bold; border:none; border-radius:5px; cursor:pointer;">
                    <?php echo $lang['login']; ?>
                </button>
            </form>

            <!-- Register Link -->
            <p style="text-align:center; margin-top:15px;">
                <?php echo $lang['no_account']; ?>
                <a href="register.php" style="color:#1abc9c; font-weight:bold;">
                    <?php echo $lang['register_here']; ?>
                </a>
            </p>

            <!-- Language Selector -->
            <div style="text-align:center; margin-top:10px;">
                <form method="get">
                    <select name="lang" onchange="this.form.submit()"
                        style="padding:8px; border-radius:5px; border:1px solid #ccc;">
                        <option value="en" <?php if ($_SESSION['lang'] == 'en') echo 'selected'; ?>>English</option>
                        <option value="sw" <?php if ($_SESSION['lang'] == 'sw') echo 'selected'; ?>>Kiswahili</option>
                    </select>
                </form>
            </div>
        </div>
    </div>
</body>

</html>