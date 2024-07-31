<?php
session_start();
require_once('db.php');

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['student_id'])) {
    $student_id = $_GET['student_id'];

    // Delete transactions associated with the student
    $stmt = $pdo->prepare("DELETE FROM transactions WHERE student_id = ?");
    $stmt->execute([$student_id]);

    // Delete notes associated with the student
    $stmt = $pdo->prepare("DELETE FROM notes WHERE student_id = ?");
    $stmt->execute([$student_id]);

    // Delete student
    $stmt = $pdo->prepare("DELETE FROM students WHERE student_id = ?");
    $stmt->execute([$student_id]);

    echo "Student deleted successfully";
    exit();
}
?>
