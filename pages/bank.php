<?php
require_once BASE_PATH . 'db.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add_account') {
            $bank_name = $_POST['bank_name'];
            $stmt = $conn->prepare("INSERT INTO bank_accounts (name, balance) VALUES (?, 0)");
            $stmt->bind_param("s", $bank_name);
            $stmt->execute();
            $stmt->close();
        } elseif ($_POST['action'] === 'add_transaction') {
            $account_id = $_POST['transaction_account'];
            $type = $_POST['transaction_type'];
            $amount = $_POST['transaction_amount'];
            $details = isset($_POST['transaction_details']) ? $_POST['transaction_details'] : null;
            $reference = isset($_POST['transaction_reference']) ? $_POST['transaction_reference'] : null;
            $date = $_POST['transaction_date'];

            // Update account balance
            $multiplier = $type === 'Deposit' ? 1 : -1;
            $stmt = $conn->prepare("UPDATE bank_accounts SET balance = balance + ? WHERE id = ?");
            $adjusted_amount = $amount * $multiplier;
            $stmt->bind_param("di", $adjusted_amount, $account_id);
            $stmt->execute();
            $stmt->close();

            // Log transaction
            $stmt = $conn->prepare("INSERT INTO bank_transactions (account_id, type, amount, details, reference, date) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isdsds", $account_id, $type, $amount, $details, $reference, $date);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Fetch bank accounts and transactions
$accounts = $conn->query("SELECT * FROM bank_accounts");
$transactions = $conn->query("SELECT bt.*, ba.name as account_name FROM bank_transactions bt JOIN bank_accounts ba ON bt.account_id = ba.id ORDER BY bt.date DESC");
?>

<h2>ব্যাংক</h2>
<div class="mb-3">
    <button class="btn btn-primary" onclick="showAccountsPopup()">নতুন অ্যাকাউন্ট যোগ করুন</button>
    <button class="btn btn-primary" onclick="showNewTransactionPopup()">নতুন লেনদেন</button>
</div>

<h3>অ্যাকাউন্টসমূহ</h3>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>ব্যাংকের নাম</th>
            <th>ব্যালেন্স</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($account = $accounts->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($account['name']); ?></td>
                <td>৳<?php echo number_format($account['balance'], 2); ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<h3>লেনদেনের ইতিহাস</h3>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>তারিখ</th>
            <th>অ্যাকাউন্ট</th>
            <th>ধরণ</th>
            <th>পরিমাণ</th>
            <th>বিস্তারিত</th>
            <th>রেফারেন্স</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($transaction = $transactions->fetch_assoc()): ?>
            <tr>
                <td><?php echo $transaction['date']; ?></td>
                <td><?php echo htmlspecialchars($transaction['account_name']); ?></td>
                <td><?php echo $transaction['type'] == 'Deposit' ? 'জমা' : 'উত্তোলন'; ?></td>
                <td>৳<?php echo number_format($transaction['amount'], 2); ?></td>
                <td><?php echo htmlspecialchars($transaction['details'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($transaction['reference'] ?? '-'); ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>