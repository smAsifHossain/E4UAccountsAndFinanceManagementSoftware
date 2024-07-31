<?php
require_once('db.php');

$search_query = '';
if (isset($_GET['search'])) {
    $search_query = trim($_GET['search']);
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id LIKE ? OR name LIKE ? OR email LIKE ? OR preferred_country LIKE ? ORDER BY student_id DESC");
    $search_term = "%$search_query%";
    $stmt->execute([$search_term, $search_term, $search_term, $search_term]);
} else {
    $stmt = $pdo->query("SELECT * FROM students ORDER BY student_id DESC");
}

$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($students) > 0) {
    foreach ($students as $student) {
        echo '<tr>';
        echo '<td><a href="individual_student.php?student_id=' . $student['student_id'] . '">' . $student['student_id'] . '</a></td>';
        echo '<td>' . $student['name'] . '</td>';
        echo '<td>' . $student['email'] . '</td>';
        echo '<td>' . $student['preferred_country'] . '</td>';
        echo '<td><button onclick="deleteStudent(' . $student['student_id'] . ')">Delete</button></td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="5">No students found.</td></tr>';
}
?>
