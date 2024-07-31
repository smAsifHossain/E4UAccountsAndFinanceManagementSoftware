<?php
session_start();
require_once('db.php');

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$user_role = $user['role'];

// Get student_id from the query parameter
if (!isset($_GET['student_id'])) {
    header("Location: students_list.php");
    exit();
}

$student_id = $_GET['student_id'];

// Fetch student information
$stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    echo "Student not found.";
    exit();
}

// Fetch student transactions
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE student_id = ? ORDER BY transaction_id DESC");
$stmt->execute([$student_id]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_balance = 0;
foreach ($transactions as $transaction) {
    $total_balance += ($transaction['debit_credit'] == 'Credit' ? $transaction['amount'] : -$transaction['amount']);
}

// Handle student information update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_student'])) {
    $name = $_POST['name'];
    $dob = $_POST['dob'];
    $sex = $_POST['sex'];
    $email = $_POST['email'];
    $contact_no = $_POST['contact_no'];
    $preferred_country = $_POST['preferred_country'];

    $stmt = $pdo->prepare("UPDATE students SET name = ?, dob = ?, sex = ?, email = ?, contact_no = ?, preferred_country = ? WHERE student_id = ?");
    $stmt->execute([$name, $dob, $sex, $email, $contact_no, $preferred_country, $student_id]);

    // Refresh the page to reflect changes
    header("Location: individual_student.php?student_id=$student_id");
    exit();
}

// Handle transaction update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_transaction']) && $user_role == 'Admin') {
    $transaction_id = $_POST['transaction_id'];
    $amount = $_POST['amount'];

    $stmt = $pdo->prepare("UPDATE transactions SET amount = ? WHERE transaction_id = ?");
    $stmt->execute([$amount, $transaction_id]);

    // Refresh the page to reflect changes
    header("Location: individual_student.php?student_id=$student_id");
    exit();
}

// Handle transaction delete
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_transaction']) && $user_role == 'Admin') {
    $transaction_id = $_POST['transaction_id'];

    $stmt = $pdo->prepare("DELETE FROM transactions WHERE transaction_id = ?");
    $stmt->execute([$transaction_id]);

    // Refresh the page to reflect changes
    header("Location: individual_student.php?student_id=$student_id");
    exit();
}

// Handle adding a new note
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_note'])) {
    $small_note = $_POST['small_note'];

    $stmt = $pdo->prepare("INSERT INTO notes (student_id, small_notes) VALUES (?, ?)");
    $stmt->execute([$student_id, $small_note]);

    // Refresh the page to reflect the newly added note
    header("Location: individual_student.php?student_id=$student_id");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Individual Student | Education 4 You</title>
    <link rel="stylesheet" href="styles/individual_student.css">
</head>
<body>
    <div class="container">
        <h1>Individual Student</h1>

        <!-- Student Information -->
        <h2>Student Information</h2>
        <form method="post" action="individual_student.php?student_id=<?= $student_id ?>">
            <input type="hidden" name="update_student" value="1">
            <div>
                <label for="student_id">Student ID:</label>
                <input type="text" id="student_id" name="student_id" value="<?= $student['student_id'] ?>" readonly>
            </div>
            <div>
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?= $student['name'] ?>" required>
            </div>
            <div>
                <label for="dob">Date of Birth:</label>
                <input type="date" id="dob" name="dob" value="<?= $student['dob'] ?>" required>
            </div>
            <div>
                <label for="sex">Sex:</label>
                <select id="sex" name="sex" required>
                    <option value="Male" <?= $student['sex'] == 'Male' ? 'selected' : '' ?>>Male</option>
                    <option value="Female" <?= $student['sex'] == 'Female' ? 'selected' : '' ?>>Female</option>
                </select>
            </div>
            <div>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?= $student['email'] ?>" required>
            </div>
            <div>
                <label for="contact_no">Contact Number:</label>
                <input type="text" id="contact_no" name="contact_no" value="<?= $student['contact_no'] ?>" required>
            </div>
            <div>
                <label for="preferred_country">Preferred Country:</label>
                <input type="text" id="preferred_country" name="preferred_country" value="<?= $student['preferred_country'] ?>" required>
            </div>
            <button class="btn" type="submit">Save</button>
        </form>

        <!-- Account Details -->
        <h2>Account Details</h2>
        <table>
            <thead>
                <tr>
                    <th>Transaction Id</th>
                    <th>Debit/Credit</th>
                    <th>Date of Transaction</th>
                    <th>Payment Type</th>
                    <th>Reason</th>
                    <th>Amount</th>
                    <th>Payment Note</th>
                    <?php if ($user_role == 'Admin') { ?>
                    <th>Edit/Delete</th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $transaction) { ?>
                <tr>
                    <td><?= $transaction['transaction_id'] ?></td>
                    <td><?= $transaction['debit_credit'] ?></td>
                    <td><?= $transaction['date_of_transaction'] ?></td>
                    <td><?= $transaction['payment_type'] ?></td>
                    <td><?= $transaction['reason'] ?></td>
                    <td><?= $transaction['amount'] ?></td>
                    <td><?= $transaction['payment_note'] ?></td>
                    <?php if ($user_role == 'Admin') { ?>
                    <td>
                        <form method="post" action="individual_student.php?student_id=<?= $student_id ?>" onsubmit="return confirmEdit(this);">
                            <input type="hidden" name="update_transaction" value="1">
                            <input type="hidden" name="transaction_id" value="<?= $transaction['transaction_id'] ?>">
                            <input type="hidden" name="amount" value="<?= $transaction['amount'] ?>">
                            <button type="submit" class="edit-btn">Edit</button>
                        </form>
                        <form method="post" action="individual_student.php?student_id=<?= $student_id ?>" onsubmit="return confirm('Are you sure you want to delete this transaction?');">
                            <input type="hidden" name="delete_transaction" value="1">
                            <input type="hidden" name="transaction_id" value="<?= $transaction['transaction_id'] ?>">
                            <button type="submit" class="delete-btn">Delete</button>
                        </form>
                    </td>
                    <?php } ?>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <p class="total-balance"><strong>Total Balance: <?= $total_balance ?></strong></p>
        <a href="make_transaction.php?student_id=<?= $student_id ?>&student_name=<?= urlencode($student['name']) ?>" class="btn">Make a Transaction</a>

        <!-- Notes Section -->
        <h2>Notes</h2>
        <form method="post" action="individual_student.php?student_id=<?= $student_id ?>">
            <input type="hidden" name="add_note" value="1">
            <textarea name="small_note" class="notes-input" rows="4" cols="50" placeholder="Enter small note"></textarea>
            <button class="add-note-btn" class="btn" type="submit">Add Note</button>
        </form>

        <!-- Display Existing Notes -->
        <h3>Existing Notes:</h3>
        <div class="notes-list">
            <?php
            $stmt = $pdo->prepare("SELECT * FROM notes WHERE student_id = ?");
            $stmt->execute([$student_id]);
            $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($notes) {
                foreach ($notes as $note) {
                    echo '<div class="note">';
                    echo '<p>' . htmlspecialchars($note['small_notes']) . '</p>';
                    echo '</div>';
                }
            } else {
                echo '<p>No notes found.</p>';
            }
            ?>
        </div>
        <br>
        <a href="dashboard.php" class="btn">Back to Dashboard</a>
        <a href="students_list.php" class="btn">Back to Student List</a>
        <a href="logout.php" class="btn">Logout</a>
    </div>

    <script>
        function confirmEdit(form) {
            const newAmount = prompt('Please enter the new amount:');
            if (newAmount !== null) {
                form.amount.value = newAmount;
                return true;
            }
            return false;
        }
    </script>
</body>
</html>
