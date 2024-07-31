<?php
session_start();
require_once('db.php');

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = $_POST['name'];
    $dob = $_POST['dob'];
    $sex = $_POST['sex'];
    $email = $_POST['email'];
    $contact_no = $_POST['contact_no'];
    $preferred_country = $_POST['preferred_country'];
    $visa_status = "Not submitted"; // Default status

    // Generate unique student ID
    $year_month = date('ym');
    $stmt = $pdo->prepare("SELECT MAX(student_id) AS max_id FROM students WHERE student_id LIKE?");
    $stmt->execute([$year_month. '%']);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $max_id = $result['max_id'];
    if ($max_id) {
        $sequence = (int)substr($max_id, 4) + 1;
    } else {
        $sequence = 1;
    }
    $student_id = $year_month. str_pad($sequence, 3, '0', STR_PAD_LEFT);

    // Insert student details into database
    $stmt = $pdo->prepare("INSERT INTO students (student_id, name, dob, sex, email, contact_no, preferred_country, visa_status) VALUES (?,?,?,?,?,?,?,?)");
    $stmt->execute([$student_id, $name, $dob, $sex, $email, $contact_no, $preferred_country, $visa_status]);

    // Redirect to student list
    header("Location: students_list.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student | Education 4 You</title>
    <link rel="stylesheet" href="styles/add_student.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Add Student</h1>
        </div>
        <form method="post" action="add_student.php">
            <div class="search-form">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required><br><br>
                <label for="dob">Date of Birth:</label>
                <input type="date" id="dob" name="dob" required><br><br>
                <label for="sex">Sex:</label>
                <select id="sex" name="sex" required>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select><br><br>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required><br><br>
                <label for="contact_no">Contact Number:</label>
                <input type="text" id="contact_no" name="contact_no" required><br><br>
                <label for="preferred_country">Preferred Country:</label>
                <input type="text" id="preferred_country" name="preferred_country" required><br><br>
                <button type="submit" class="btn">Submit</button>
            </div>
        </form>
        <div class="dashboard-links">
            <a href="dashboard.php" class="btn">Back to Dashboard</a>
            <a href="students_list.php" class="btn">Back to Student List</a>
            <a href="logout.php" class="btn">Logout</a>
        </div>
    </div>
</body>
</html>