<?php
session_start();
require_once('db.php');

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Function to generate unique transaction_id
function generate_transaction_id() {
    global $pdo; // Assuming $pdo is your PDO object connected to the database

    $prefix = 'T';
    $date = date('dmY'); // Get current day, month, year in dmy format
    $sequential_number = rand(10, 99); // Generate a random two-digit number
    $transaction_id = $prefix . $date . $sequential_number;

    // Check if transaction_id already exists in the database
    $sql_check = "SELECT COUNT(*) FROM transactions WHERE transaction_id = :transaction_id";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->bindParam(':transaction_id', $transaction_id);
    $stmt_check->execute();
    $count = $stmt_check->fetchColumn();

    // If transaction_id already exists, generate a new one
    while ($count > 0) {
        $sequential_number = rand(10, 99); // Regenerate random two-digit number
        $transaction_id = $prefix . $date . $sequential_number;

        // Check again with the new transaction_id
        $stmt_check->bindParam(':transaction_id', $transaction_id);
        $stmt_check->execute();
        $count = $stmt_check->fetchColumn();
    }

    return $transaction_id;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Fetch data from form
    $transaction_id = generate_transaction_id();
    $student_id = $_POST['student_id'];
    $amount = $_POST['amount'];
    $debit_credit = $_POST['debit_credit'];
    $date_of_transaction = $_POST['date_of_transaction'];
    $payment_type = $_POST['payment_type'];
    $refund_type = $_POST['refund_type'];
    $reason = $_POST['reason'];

    // Handle payment note based on payment type
    switch ($payment_type) {
        case 'Bkash':
            $sender_number = $_POST['sender_bkash_number'];
            $receiver_number = $_POST['receiver_bkash_number'];
            $payment_note = "Sender Bkash Number: $sender_number, Receiver Bkash Number: $receiver_number";
            break;
        case 'Nagad':
            $sender_number = $_POST['sender_nagad_number'];
            $receiver_number = $_POST['receiver_nagad_number'];
            $payment_note = "Sender Nagad Number: $sender_number, Receiver Nagad Number: $receiver_number";
            break;
        case 'Bank Transfer':
            $sender_bank_name = $_POST['sender_bank_name'];
            $sender_account_number = $_POST['sender_account_number'];
            $receiver_bank_name = $_POST['receiver_bank_name'];
            $receiver_account_number = $_POST['receiver_account_number'];
            $payment_note = "Sender Bank: $sender_bank_name, Account Number: $sender_account_number, Receiver Bank: $receiver_bank_name, Account Number: $receiver_account_number";
            break;
        default:
            $payment_note = '';
            break;
    }

    // Prepare SQL statement
    $sql = "INSERT INTO transactions (transaction_id, student_id, amount, debit_credit, date_of_transaction, payment_type, refund_type, reason, payment_note)
            VALUES (:transaction_id, :student_id, :amount, :debit_credit, :date_of_transaction, :payment_type, :refund_type, :reason, :payment_note)";
    
    $stmt = $pdo->prepare($sql);

    // Bind parameters
    $stmt->bindParam(':transaction_id', $transaction_id);
    $stmt->bindParam(':student_id', $student_id);
    $stmt->bindParam(':amount', $amount);
    $stmt->bindParam(':debit_credit', $debit_credit);
    $stmt->bindParam(':date_of_transaction', $date_of_transaction);
    $stmt->bindParam(':payment_type', $payment_type);
    $stmt->bindParam(':refund_type', $refund_type);
    $stmt->bindParam(':reason', $reason);
    $stmt->bindParam(':payment_note', $payment_note);

    // Execute the query
    if ($stmt->execute()) {
        // Transaction successfully added
        echo "<script>alert('Transaction successfully added!');</script>";
        // Redirect to individual_student.php with student_id
        header("Location: individual_student.php?student_id=$student_id");
        exit();
    } else {
        // Error in adding transaction
        echo "<script>alert('Error adding transaction. Please try again later.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make Transaction | Education 4 You</title>
    <link rel="stylesheet" href="styles/make_transaction.css">
</head>
<body>
    <div class="container">
        <h1>Make Transaction</h1>
        <form method="post" action="">
            <label for="amount">Amount:</label>
            <input type="text" id="amount" name="amount" required><br><br>

            <label for="student_id">Student ID:</label>
            <input type="text" id="student_id" name="student_id" value="<?php echo isset($_GET['student_id']) ? $_GET['student_id'] : ''; ?>" readonly><br><br>

            <label for="student_name">Student Name:</label>
            <input type="text" id="student_name" name="student_name" value="<?php echo isset($_GET['student_name']) ? $_GET['student_name'] : ''; ?>" readonly><br><br>

            <label for="debit_credit">Debit/Credit:</label>
            <select id="debit_credit" name="debit_credit" required>
                <option value="Debit">Debit</option>
                <option value="Credit">Credit</option>
            </select><br><br>

            <label for="date_of_transaction">Date of Transaction:</label>
            <input type="date" id="date_of_transaction" name="date_of_transaction" value="<?php echo date('Y-m-d'); ?>"><br><br>

            <label for="payment_type">Payment Type:</label>
            <select id="payment_type" name="payment_type" onchange="showPaymentFields(this.value)" required>
                <option value="">Select Payment Type</option>
                <option value="Cash">Cash</option>
                <option value="Bank Transfer">Bank Transfer</option>
                <option value="Bkash">Bkash</option>
                <option value="Nagad">Nagad</option>
            </select><br><br>

            <!-- Fields based on payment type -->
            <div id="bankTransferFields" style="display: none;">
                <label for="sender_bank_name">Sender Bank Name:</label>
                <input type="text" id="sender_bank_name" name="sender_bank_name"><br><br>

                <label for="sender_account_number">Sender Account Number:</label>
                <input type="text" id="sender_account_number" name="sender_account_number"><br><br>

                <label for="receiver_bank_name">Receiver Bank Name:</label>
                <input type="text" id="receiver_bank_name" name="receiver_bank_name"><br><br>

                <label for="receiver_account_number">Receiver Account Number:</label>
                <input type="text" id="receiver_account_number" name="receiver_account_number"><br><br>
            </div>

            <div id="bkashFields" style="display: none;">
                <label for="sender_bkash_number">Sender Bkash Number:</label>
                <input type="text" id="sender_bkash_number" name="sender_bkash_number"><br><br>

                <label for="receiver_bkash_number">Receiver Bkash Number:</label>
                <input type="text" id="receiver_bkash_number" name="receiver_bkash_number"><br><br>
            </div>

            <div id="nagadFields" style="display: none;">
                <label for="sender_nagad_number">Sender Nagad Number:</label>
                <input type="text" id="sender_nagad_number" name="sender_nagad_number"><br><br>

                <label for="receiver_nagad_number">Receiver Nagad Number:</label>
                <input type="text" id="receiver_nagad_number" name="receiver_nagad_number"><br><br>
            </div>

            <label for="refund_type">Refund Type:</label>
            <select id="refund_type" name="refund_type" required>
                <option value="Refundable">Refundable</option>
                <option value="Non refundable">Non refundable</option>
            </select><br><br>

            <label for="reason">Reason:</label><br>
            <textarea id="reason" name="reason" rows="4" cols="50" required></textarea><br><br>

            <button type="submit" class="btn" onclick="return confirm('Are you sure you want to make this payment?')">Make Payment and Generate Invoice</button>
        </form>

        <a href="dashboard.php" class="btn">Back to Dashboard</a>
        <a href="students_list.php" class="btn">Back to Student List</a>
        <a href="logout.php" class="btn">Logout</a>
    </div>

    <script>
        function showPaymentFields(paymentType) {
            var bankTransferFields = document.getElementById('bankTransferFields');
            var bkashFields = document.getElementById('bkashFields');
            var nagadFields = document.getElementById('nagadFields');

            // Hide all fields initially
            bankTransferFields.style.display = 'none';
            bkashFields.style.display = 'none';
            nagadFields.style.display = 'none';

            // Show fields based on payment type
            switch (paymentType) {
                case 'Bank Transfer':
                    bankTransferFields.style.display = 'block';
                    break;
                case 'Bkash':
                    bkashFields.style.display = 'block';
                    break;
                case 'Nagad':
                    nagadFields.style.display = 'block';
                    break;
                default:
                    break;
            }
        }
    </script>
</body>
</html>
