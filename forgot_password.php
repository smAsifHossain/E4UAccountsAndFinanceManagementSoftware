<?php
session_start();
require_once('db.php');

$error_message = '';
$success_message = '';
$show_new_password_form = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email']) && isset($_POST['magic_word'])) {
        $email = $_POST['email'];
        $magic_word = $_POST['magic_word'];

        // Prepare and execute query to fetch user details based on email and magic word
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username =? AND magic =?");
        $stmt->execute([$email, $magic_word]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify if user exists
        if ($user) {
            $show_new_password_form = true;
            $_SESSION['reset_email'] = $email;
        } else {
            $error_message = "Invalid email or magical word. Please try again.";
        }
    } elseif (isset($_POST['new_password']) && isset($_SESSION['reset_email'])) {
        $new_password = $_POST['new_password'];
        $email = $_SESSION['reset_email'];

        // Update the password in the database
        $stmt = $pdo->prepare("UPDATE users SET password =? WHERE username =?");
        $stmt->execute([$new_password, $email]);

        echo "<script>alert('Password successfully updated. Redirecting to login page.'); window.location.href = 'login.php';</script>";
        unset($_SESSION['reset_email']);
        exit(); // Ensure no further code is executed
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Education 4 You</title>
    <link rel="stylesheet" href="styles/forgot_password.css">
</head>
<body>
    <div class="reset-container">
        <h1>Forgot Password</h1>
        <?php if (!empty($error_message)): ?>
            <p class="error-message"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <?php if (!$show_new_password_form): ?>
            <form action="forgot_password.php" method="post">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required><br><br>
                <label for="magic_word">Magical Word:</label>
                <input type="text" id="magic_word" name="magic_word" required><br><br>
                <button type="submit">Submit</button>
            </form>
        <?php else: ?>
            <form action="forgot_password.php" method="post">
                <label for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password" required><br><br>
                <button type="submit">Reset Password</button>
            </form>
        <?php endif; ?>
        <div class="welcome-footer">
            <p>Want to go back to the <a href="login.php">Login page</a>?</p>
        </div>
    </div>
</body>
</html>
