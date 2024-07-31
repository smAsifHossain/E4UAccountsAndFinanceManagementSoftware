<?php
session_start();
require_once('db.php');

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['amount']) && isset($_POST['action']) && $_POST['action'] === 'edit_transaction') {
    $id = $_POST['id'];
    $amount = $_POST['amount'];

    try {
        $stmt = $pdo->prepare("UPDATE transactions_office SET amount = ? WHERE transaction_id = ?");
        $stmt->execute([$amount, $id]);

        // Check if any rows were affected
        if ($stmt->rowCount() > 0) {
            header("Location: office_finance.php");
            exit();
        } else {
            // Redirect with error if no rows were updated
            header("Location: office_finance.php?error=edit_failed");
            exit();
        }
    } catch (PDOException $e) {
        // Handle database errors
        echo "Error: " . $e->getMessage();
    }
} else {
    // Redirect if POST data is missing or action is incorrect
    header("Location: office_finance.php");
    exit();
}
?>
