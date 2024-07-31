<?php
session_start();
require_once('db.php');

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['id'])) {
    $id = $_POST['id'];

    $stmt = $pdo->prepare("DELETE FROM transactions_office WHERE transaction_id = ?");
    $stmt->execute([$id]);

    header("Location: office_finance.php");
    exit();
} else {
    header("Location: office_finance.php");
    exit();
}
?>
