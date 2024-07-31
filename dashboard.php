<!-- Filename: dashboard.php -->
<?php
session_start();
require_once('db.php');

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Get user details
$username = $_SESSION['user']['username'];
$role = $_SESSION['user']['role'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    // If user details are not found, log out and redirect to login
    session_destroy();
    header("Location: login.php");
    exit();
}

// User details
$name = $user['name']; // Assuming 'username' is the 'name' here
$email = $user['username']; // Assuming 'username' is the 'email' here (adjust based on your database structure)
$contact_no = isset($user['contact_no']) ? $user['contact_no'] : 'N/A'; // Assuming you have 'contact_no' in your table
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Education 4 You</title>
    <link rel="stylesheet" href="styles/dashboard.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Dashboard</h1>
        </div>
        <div class="user-profile">
            <h2>User Profile</h2>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($name); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
            <p><strong>Contact No:</strong> <?php echo htmlspecialchars($contact_no); ?></p>
        </div>
        <div class="dashboard-links">
            <a href="students_list.php" class="btn">Student List</a>
            <a href="add_student.php" class="btn">Add Student</a>
            <a href="office_finance.php" class="btn">Office Finance</a>
            <a href="logout.php" class="btn">Logout</a>
        </div>
    </div>
</body>
</html>
