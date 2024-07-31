<?php
session_start();
require_once('db.php');

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Initialize search query
$search_query = '';

// Fetch all students ordered by ID in descending order
$stmt = $pdo->query("SELECT * FROM students ORDER BY student_id DESC");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students List | Education 4 You</title>
    <link rel="stylesheet" href="styles/students_list.css">
    <script>
        function fetchStudents(query) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'search_students.php?search=' + query, true);
            xhr.onload = function() {
                if (this.status === 200) {
                    document.getElementById('students-table-body').innerHTML = this.responseText;
                }
            }
            xhr.send();
        }

        function deleteStudent(studentId) {
            if (confirm('Are you sure you want to delete this student?')) {
                const xhr = new XMLHttpRequest();
                xhr.open('GET', 'delete_student.php?student_id=' + studentId, true);
                xhr.onload = function() {
                    if (this.status === 200) {
                        fetchStudents(document.getElementById('search').value);
                    }
                }
                xhr.send();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('search').addEventListener('input', function() {
                fetchStudents(this.value);
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Students List</h1>
        </div>
        <div class="search-form">
            <form id="search-form" method="get" action="students_list.php">
                <input type="text" id="search" name="search" placeholder="Search by ID, name, email, country, status" value="<?= htmlspecialchars($search_query) ?>">
                <button type="submit">Search</button>
            </form>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Country</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody id="students-table-body">
                    <?php if (count($students) > 0) { ?>
                        <?php foreach ($students as $student) { ?>
                        <tr>
                            <td><a href="individual_student.php?student_id=<?= $student['student_id'] ?>"><?= $student['student_id'] ?></a></td>
                            <td><?= $student['name'] ?></td>
                            <td><?= $student['email'] ?></td>
                            <td><?= $student['preferred_country'] ?></td>
                            <td><button onclick="deleteStudent(<?= $student['student_id'] ?>)">Delete</button></td>
                        </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="5">No students found.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <div class="dashboard-links">
            <a href="dashboard.php" class="btn">Back to Dashboard</a>
            <a href="add_student.php" class="btn">Add Student</a>
            <a href="logout.php" class="btn">Logout</a>
        </div>
    </div>
</body>
</html>
