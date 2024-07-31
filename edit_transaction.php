<!-- Filename: edit_transaction.php -->
<?php
session_start();
require_once('db.php');

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['transaction_id'])) {
    $transaction_id = $_GET['transaction_id'];

    // Fetch transaction details
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE transaction_id = ?");
    $stmt->execute([$transaction_id]);
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$transaction) {
        die("Transaction not found.");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaction_id = $_POST['transaction_id'];
    $amount = $_POST['amount'];
    $debit_credit = $_POST['debit_credit'];
    $date_of_transaction = $_POST['date_of_transaction'];
    $payment_type = $_POST['payment_type'];
    $reason = $_POST['reason'];
    $payment_note = $_POST['payment_note'];

    // Update transaction details
    $stmt = $pdo->prepare("UPDATE transactions SET amount = ?, debit_credit = ?, date_of_transaction = ?, payment_type = ?, reason = ?, payment_note = ? WHERE transaction_id = ?");
    $stmt->execute([$amount, $debit_credit, $date_of_transaction, $payment_type, $reason, $payment_note, $transaction_id]);

    // Redirect to student details page
    $stmt = $pdo->prepare("SELECT student_id FROM transactions WHERE transaction_id = ?");
    $stmt->execute([$transaction_id]);
    $student_id = $stmt->fetch(PDO::FETCH_ASSOC)['student_id'];

    header("Location: individual_student.php?student_id=$student_id");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Transaction | Education 4 You</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Edit Transaction</h1>
        <form action="edit_transaction.php" method="post">
            <input type="hidden" name="transaction_id" value="<?php echo $transaction['transaction_id']; ?>">
            <label for="amount">Amount:</label>
            <input type="number" id="amount" name="amount" step="0.01" value="<?php echo $transaction['amount']; ?>" required><br><br>
            <label for="debit_credit">Debit/Credit:</label>
            <select id="debit_credit" name="debit_credit" required>
                <option value="Debit" <?php echo ($transaction['debit_credit'] === 'Debit') ? 'selected' : ''; ?>>Debit</option>
                <option value="Credit" <?php echo ($transaction['debit_credit'] === 'Credit') ? 'selected' : ''; ?>>Credit</option>
            </select><br><br>
            <label for="date_of_transaction">Date of Transaction:</label>
            <input type="date" id="date_of_transaction" name="date_of_transaction" value="<?php echo $transaction['date_of_transaction']; ?>" required><br><br>
            <label for="payment_type">Payment Type:</label>
            <input type="text" id="payment_type" name="payment_type" value="<?php echo $transaction['payment_type']; ?>" required><br><br>
            <label for="reason">Reason:</label>
            <textarea id="reason" name="reason" rows="4" required><?php echo $transaction['reason']; ?></textarea><br><br>
            <label for="payment_note">Payment Note:</label>
            <textarea id="payment_note" name="payment_note" rows="4"><?php echo $transaction['payment_note']; ?></textarea><br><br>
            <button type="submit">Save Changes</button>
        </form>
        <a href="dashboard.php">Back to Dashboard</a><br>
        <a href="individual_student.php?student_id=<?php echo $transaction['student_id']; ?>">Back to Student Details</a><br>
        <a href="logout.php">Logout</a>
    </div>
</body>
</html>
