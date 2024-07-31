<!-- Filename: delete_transaction.php -->
<?php
session_start();
require_once('db.php');

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['transaction_id'])) {
    $transaction_id = $_GET['transaction_id'];

    // Fetch student ID associated with the transaction
    $stmt = $pdo->prepare("SELECT student_id FROM transactions WHERE transaction_id = ?");
    $stmt->execute([$transaction_id]);
    $student_id = $stmt->fetch(PDO::FETCH_ASSOC)['student_id'];

    // Delete transaction
    $stmt = $pdo->prepare("DELETE FROM transactions WHERE transaction_id = ?");
    $stmt->execute([$transaction_id]);

    header("Location: individual_student.php?student_id=$student_id");
    exit();
}
?>
