<!-- Filename: edit_student.php -->
<?php
session_start();
require_once('db.php');

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['student_id'])) {
    $student_id = $_GET['student_id'];

    // Fetch student details
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        die("Student not found.");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];
    $name = $_POST['name'];
    $dob = $_POST['dob'];
    $sex = $_POST['sex'];
    $email = $_POST['email'];
    $contact_no = $_POST['contact_no'];
    $preferred_country = $_POST['preferred_country'];

    // Update student details
    $stmt = $pdo->prepare("UPDATE students SET name = ?, dob = ?, sex = ?, email = ?, contact_no = ?, preferred_country = ? WHERE student_id = ?");
    $stmt->execute([$name, $dob, $sex, $email, $contact_no, $preferred_country, $student_id]);

    header("Location: individual_student.php?student_id=$student_id");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student | Education 4 You</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Edit Student</h1>
        <form action="edit_student.php" method="post">
            <input type="hidden" name="student_id" value="<?php echo $student['student_id']; ?>">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo $student['name']; ?>" required><br><br>
            <label for="dob">Date of Birth:</label>
            <input type="date" id="dob" name="dob" value="<?php echo $student['dob']; ?>" required><br><br>
            <label for="sex">Sex:</label>
            <select id="sex" name="sex" required>
                <option value="Male" <?php echo ($student['sex'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                <option value="Female" <?php echo ($student['sex'] === 'Female') ? 'selected' : ''; ?>>Female</option>
            </select><br><br>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo $student['email']; ?>" required><br><br>
            <label for="contact_no">Contact Number:</label>
            <input type="text" id="contact_no" name="contact_no" value="<?php echo $student['contact_no']; ?>" required><br><br>
            <label for="preferred_country">Preferred Country:</label>
            <input type="text" id="preferred_country" name="preferred_country" value="<?php echo $student['preferred_country']; ?>" required><br><br>
            <button type="submit">Save Changes</button>
        </form>
        <a href="dashboard.php">Back to Dashboard</a><br>
        <a href="individual_student.php?student_id=<?php echo $student['student_id']; ?>">Back to Student Details</a><br>
        <a href="logout.php">Logout</a>
    </div>
</body>
</html>
