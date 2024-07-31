<?php
session_start();
require_once('db.php');

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['user']['username'];
$role = $_SESSION['user']['role'];

// Default to current month and year if not specified in URL
$current_month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$current_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Calculate totals
$total_balance_query = "SELECT SUM(CASE WHEN debit_credit = 'credit' THEN amount ELSE -amount END) AS total_balance FROM transactions_office";
$total_income_query = "SELECT SUM(CASE WHEN debit_credit = 'credit' THEN amount ELSE 0 END) AS total_income FROM transactions_office";
$total_expenditure_query = "SELECT SUM(CASE WHEN debit_credit = 'debit' THEN amount ELSE 0 END) AS total_expenditure FROM transactions_office";

$total_balance = $pdo->query($total_balance_query)->fetchColumn();
$total_income = $pdo->query($total_income_query)->fetchColumn();
$total_expenditure = $pdo->query($total_expenditure_query)->fetchColumn();

$current_balance_query = "SELECT SUM(CASE WHEN debit_credit = 'credit' THEN amount ELSE -amount END) AS current_balance FROM transactions_office WHERE MONTH(date_of_transaction) = $current_month AND YEAR(date_of_transaction) = $current_year";
$current_income_query = "SELECT SUM(amount) AS current_income FROM transactions_office WHERE debit_credit = 'credit' AND MONTH(date_of_transaction) = $current_month AND YEAR(date_of_transaction) = $current_year";
$current_expenditure_query = "SELECT SUM(amount) AS current_expenditure FROM transactions_office WHERE debit_credit = 'debit' AND MONTH(date_of_transaction) = $current_month AND YEAR(date_of_transaction) = $current_year";

$current_balance = $pdo->query($current_balance_query)->fetchColumn();
$current_income = $pdo->query($current_income_query)->fetchColumn();
$current_expenditure = $pdo->query($current_expenditure_query)->fetchColumn();

// Fetch transactions for the current month and year
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$transactions_query = "SELECT * FROM transactions_office WHERE MONTH(date_of_transaction) = $current_month AND YEAR(date_of_transaction) = $current_year ORDER BY date_of_transaction DESC LIMIT $limit OFFSET $offset";
$transactions = $pdo->query($transactions_query)->fetchAll(PDO::FETCH_ASSOC);

$total_transactions_query = "SELECT COUNT(*) FROM transactions_office WHERE MONTH(date_of_transaction) = $current_month AND YEAR(date_of_transaction) = $current_year";
$total_transactions = $pdo->query($total_transactions_query)->fetchColumn();
$total_pages = ceil($total_transactions / $limit);

// Function to format number with two decimal places
function format_amount($amount) {
    return number_format($amount, 2);
}

// Handle edit transaction
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_transaction') {
    $id = $_POST['id'];
    $amount = $_POST['amount'];

    try {
        $stmt = $pdo->prepare("UPDATE transactions_office SET amount = ? WHERE transaction_id = ?");
        $stmt->execute([$amount, $id]);

        // Redirect after successful update
        header("Location: office_finance.php?month=$current_month&year=$current_year&page=$page");
        exit();
    } catch (PDOException $e) {
        echo "Error updating transaction: " . $e->getMessage();
    }
}

// Handle delete transaction
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_transaction') {
    $id = $_POST['id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM transactions_office WHERE transaction_id = ?");
        $stmt->execute([$id]);

        // Redirect after successful deletion
        header("Location: office_finance.php?month=$current_month&year=$current_year&page=$page");
        exit();
    } catch (PDOException $e) {
        echo "Error deleting transaction: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Office Finance Management</title>
    <link rel="stylesheet" href="styles/office_finance.css">
</head>
<body>
    <div class="container">
        <h1>Office Finance Management</h1>
        <div class="summary">
            <div class="left">
                <p><strong>Total Balance Lifetime:</strong> <?php echo format_amount($total_balance); ?></p>
                <p><strong>Total Income Lifetime:</strong> <?php echo format_amount($total_income); ?></p>
                <p><strong>Total Expenditure Lifetime:</strong> <?php echo format_amount($total_expenditure); ?></p>
            </div>
            <div class="right">
                <p><strong>Total Balance of <?php echo date('F Y'); ?>:</strong> <?php echo format_amount($current_balance); ?></p>
                <p><strong>Total Income of <?php echo date('F Y'); ?>:</strong> <?php echo format_amount($current_income); ?></p>
                <p><strong>Total Expenditure of <?php echo date('F Y'); ?>:</strong> <?php echo format_amount($current_expenditure); ?></p>
            </div>
        </div>
        <button class="btn" onclick="location.href='make_transaction_office.php'">Make a New Transaction</button>
        <br><br>
        <h2>Transactions of <?php echo date('F Y', mktime(0, 0, 0, $current_month, 1, $current_year)); ?></h2>
        <table>
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Debit/Credit</th>
                    <th>Date of Transaction</th>
                    <th>Payment Type</th>
                    <th>Reason</th>
                    <th>Amount</th>
                    <th>Payment Note</th>
                    <?php if ($role === 'Admin'): ?>
                        <th>Edit/Delete</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <td><?php echo $transaction['transaction_id']; ?></td>
                        <td><?php echo htmlspecialchars($transaction['debit_credit']); ?></td>
                        <td><?php echo $transaction['date_of_transaction']; ?></td>
                        <td><?php echo htmlspecialchars($transaction['payment_type']); ?></td>
                        <td><?php echo htmlspecialchars($transaction['reason']); ?></td>
                        <td><?php echo format_amount($transaction['amount']); ?></td>
                        <td><?php echo htmlspecialchars($transaction['payment_note']); ?></td>
                        <?php if ($role === 'Admin'): ?>
                            <td>
                                <form method="POST" action="edit_transaction_office.php" onsubmit="return confirmEdit(this);">
                                    <input type="hidden" name="id" value="<?php echo $transaction['transaction_id']; ?>">
                                    <input type="hidden" name="amount" value="<?php echo $transaction['amount']; ?>">
                                    <input type="hidden" name="action" value="edit_transaction">
                                    <button type="submit">Edit</button>
                                </form>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this transaction?');">
                                    <input type="hidden" name="id" value="<?php echo $transaction['transaction_id']; ?>">
                                    <input type="hidden" name="action" value="delete_transaction">
                                    <button type="submit">Delete</button>
                                </form>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <form method="GET">
            <label for="month">Month:</label>
            <select name="month" id="month">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?php echo $m; ?>" <?php if ($m == $current_month) echo 'selected'; ?>><?php echo date('F', mktime(0, 0, 0, $m, 10)); ?></option>
                <?php endfor; ?>
            </select>

            <label for="year">Year:</label>
            <select name="year" id="year">
                <?php for ($y = 2020; $y <= date('Y'); $y++): ?>
                    <option value="<?php echo $y; ?>" <?php if ($y == $current_year) echo 'selected'; ?>><?php echo $y; ?></option>
                <?php endfor; ?>
            </select>
            <button type="submit">Search</button>
        </form>
        <button class="btn" onclick="location.href='dashboard.php'">Back to Dashboard</button>
        <button class="btn" onclick="location.href='logout.php'">Logout</button>
    </div>
    <script>
        function confirmEdit(form) {
            const id = form.id.value;
            const amount = form.amount.value;
            const newAmount = prompt("Enter new amount for transaction ID " + id + ":", amount);
            if (newAmount !== null && newAmount !== '') {
                form.amount.value = newAmount;
                return true;
            }
            return false;
        }
    </script>
</body>
</html>
