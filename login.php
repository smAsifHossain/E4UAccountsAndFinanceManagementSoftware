<?php
session_start();
require_once('db.php');

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare and execute query to fetch user details based on email and password
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username =? AND password =?");
    $stmt->execute([$email, $password]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify if user exists
    if ($user) {
        $_SESSION['user'] = [
            'username' => $user['username'],
            'role' => $user['role']
        ];

        if ($user['role'] === 'Admin') {
            header("Location: dashboard.php");
        } else {
            header("Location: students_list.php");
        }
        exit();
    } else {
        $error_message = "Invalid credentials. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Education 4 You</title>
    <link rel="stylesheet" href="styles/login.css">
</head>
<body>
    <div class="welcome-container">
        <div class="welcome-header">
            <h1>Welcome to E4U <br>Accounts Software Login</h1>
        </div>
        <div class="login-container">
            <?php if (!empty($error_message)):?>
                <p class="error-message"><?php echo $error_message;?></p>
            <?php endif;?>
            <form action="login.php" method="post">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required><br><br>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required><br><br>
                <button type="submit">Login</button>
            </form>
        </div>
        <div class="welcome-footer">
            <p>Forgot your password? <a href="forgot_password.php">Click Here</a> to reset!</p>
            <p>Do you want to visit the <a href="index.php">Home Page</a>!</p>
        </div>
    </div>
</body>
</html>