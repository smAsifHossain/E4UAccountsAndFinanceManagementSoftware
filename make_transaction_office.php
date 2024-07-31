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

    // Get current year and month
    $year = date('y'); // Last two digits of the year
    $month = date('m'); // Current month

    // Prepare the transaction ID prefix
    $prefix = 'O' . $year . $month;

    // Generate a random two-digit number for sequential part
    $sequential_number = rand(10, 99);

    // Concatenate to form the final transaction ID
    $transaction_id = $prefix . $sequential_number;

    // Check if transaction_id already exists in the database
    $sql_check = "SELECT COUNT(*) FROM transactions_office WHERE transaction_id = :transaction_id";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->bindParam(':transaction_id', $transaction_id);
    $stmt_check->execute();
    $count = $stmt_check->fetchColumn();

    // If transaction_id already exists, generate a new one
    while ($count > 0) {
        $sequential_number = rand(10, 99); // Regenerate random two-digit number
        $transaction_id = $prefix . $sequential_number;

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
    $amount = $_POST['amount'];
    $debit_credit = $_POST['debit_credit'];
    $date_of_transaction = $_POST['date_of_transaction'];
    $payment_type = $_POST['payment_type'];
    $refund_type = $_POST['refund_type'];
    $reason = $_POST['reason'];
    $sender_name = '';
    $receiver_name = '';

    // Determine sender/receiver names based on debit/credit
    if ($debit_credit === 'Debit') {
        $receiver_name = $_POST['receiver_name'];
    } elseif ($debit_credit === 'Credit') {
        $sender_name = $_POST['sender_name'];
    }

    // Initialize payment_note as empty string
    $payment_note = '';

    // Handle payment note based on payment type and sender/receiver names
    if ($payment_type !== 'Cash') {
        switch ($payment_type) {
            case 'Bkash':
                $sender_number = $_POST['sender_bkash_number'];
                $receiver_number = $_POST['receiver_bkash_number'];
                if ($debit_credit === 'Credit') {
                    $payment_note = "Sender: $sender_name, Sender Bkash Number: $sender_number, Receiver Bkash Number: $receiver_number";
                } elseif ($debit_credit === 'Debit') {
                    $payment_note = "Receiver: $receiver_name, Sender Bkash Number: $sender_number, Receiver Bkash Number: $receiver_number";
                }
                break;
            case 'Nagad':
                $sender_number = $_POST['sender_nagad_number'];
                $receiver_number = $_POST['receiver_nagad_number'];
                if ($debit_credit === 'Credit') {
                    $payment_note = "Sender: $sender_name, Sender Nagad Number: $sender_number, Receiver Nagad Number: $receiver_number";
                } elseif ($debit_credit === 'Debit') {
                    $payment_note = "Receiver: $receiver_name, Sender Nagad Number: $sender_number, Receiver Nagad Number: $receiver_number";
                }
                break;
            case 'Bank Transfer':
                $sender_bank_name = $_POST['sender_bank_name'];
                $sender_account_number = $_POST['sender_account_number'];
                $receiver_bank_name = $_POST['receiver_bank_name'];
                $receiver_account_number = $_POST['receiver_account_number'];
                if ($debit_credit === 'Credit') {
                    $payment_note = "Sender: $sender_name, Sender Bank: $sender_bank_name, Account Number: $sender_account_number, Receiver Bank: $receiver_bank_name, Account Number: $receiver_account_number";
                } elseif ($debit_credit === 'Debit') {
                    $payment_note = "Receiver: $receiver_name, Sender Bank: $sender_bank_name, Account Number: $sender_account_number, Receiver Bank: $receiver_bank_name, Account Number: $receiver_account_number";
                }
                break;
            default:
                break;
        }
    } else {
        // Cash payment type handling
        if ($debit_credit === 'Credit') {
            $payment_note = "Sender: $sender_name";
        } elseif ($debit_credit === 'Debit') {
            $payment_note = "Receiver: $receiver_name";
        }
    }

    // Prepare SQL statement
    $sql = "INSERT INTO transactions_office (transaction_id, amount, debit_credit, date_of_transaction, payment_type, refund_type, reason, payment_note, sender_name, receiver_name)
            VALUES (:transaction_id, :amount, :debit_credit, :date_of_transaction, :payment_type, :refund_type, :reason, :payment_note, :sender_name, :receiver_name)";
    
    $stmt = $pdo->prepare($sql);

    // Bind parameters
    $stmt->bindParam(':transaction_id', $transaction_id);
    $stmt->bindParam(':amount', $amount);
    $stmt->bindParam(':debit_credit', $debit_credit);
    $stmt->bindParam(':date_of_transaction', $date_of_transaction);
    $stmt->bindParam(':payment_type', $payment_type);
    $stmt->bindParam(':refund_type', $refund_type);
    $stmt->bindParam(':reason', $reason);
    $stmt->bindParam(':payment_note', $payment_note);
    $stmt->bindParam(':sender_name', $sender_name);
    $stmt->bindParam(':receiver_name', $receiver_name);

    // Execute the query
    if ($stmt->execute()) {
        // Transaction successfully added
        echo "<script>alert('Transaction successfully added!');</script>";
        // Redirect to office_finance.php
        header("Location: office_finance.php");
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
    <link rel="stylesheet" href="styles/make_transaction_office.css">
</head>
<body>
    <div class="container">
        <h1>Make Transaction</h1>
        <form method="post" action="">
            <label for="amount">Amount:</label>
            <input type="text" id="amount" name="amount" required><br><br>

            <label for="debit_credit">Debit/Credit:</label>
            <select id="debit_credit" name="debit_credit" onchange="showNameFields(this.value)" required>
                <option value="">Select Debit/Credit</option>
                <option value="Debit">Debit</option>
                <option value="Credit">Credit</option>
            </select><br><br>

            <!-- Fields for sender/receiver names -->
            <div id="senderNameField" style="display: none;">
                <label for="sender_name">Sender's Name:</label>
                <input type="text" id="sender_name" name="sender_name"><br><br>
            </div>

            <div id="receiverNameField" style="display: none;">
                <label for="receiver_name">Receiver's Name:</label>
                <input type="text" id="receiver_name" name="receiver_name"><br><br>
            </div>

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
                <option value="">Select Refund Type</option>
                <option value="Refundable">Refundable</option>
                <option value="Non refundable">Non refundable</option>
            </select><br><br>

            <label for="reason">Reason:</label><br>
            <textarea id="reason" name="reason" rows="4" cols="50" required></textarea><br><br>

            <input type="submit" class="btn" value="Make Payment and Generate Invoice">
            <a href="dashboard.php" class="btn">Back to Dashboard</a>
            <a href="office_finance.php" class="btn">Back to Office Finance</a>
            <a href="logout.php" class="btn">Logout</a>
        </form>
    </div>

    <script>
        function showNameFields(value) {
            var senderNameField = document.getElementById("senderNameField");
            var receiverNameField = document.getElementById("receiverNameField");

            if (value === "Credit") {
                senderNameField.style.display = "block";
                receiverNameField.style.display = "none";
            } else if (value === "Debit") {
                senderNameField.style.display = "none";
                receiverNameField.style.display = "block";
            } else {
                senderNameField.style.display = "none";
                receiverNameField.style.display = "none";
            }
        }

        function showPaymentFields(value) {
            var bankTransferFields = document.getElementById("bankTransferFields");
            var bkashFields = document.getElementById("bkashFields");
            var nagadFields = document.getElementById("nagadFields");

            if (value === "Bank Transfer") {
                bankTransferFields.style.display = "block";
                bkashFields.style.display = "none";
                nagadFields.style.display = "none";
            } else if (value === "Bkash") {
                bankTransferFields.style.display = "none";
                bkashFields.style.display = "block";
                nagadFields.style.display = "none";
            } else if (value === "Nagad") {
                bankTransferFields.style.display = "none";
                bkashFields.style.display = "none";
                nagadFields.style.display = "block";
            } else {
                bankTransferFields.style.display = "none";
                bkashFields.style.display = "none";
                nagadFields.style.display = "none";
            }
        }
    </script>
</body>
</html>
