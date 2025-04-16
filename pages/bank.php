<?php
require_once BASE_PATH . 'db.php';

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
            $details = $_POST['transaction_details'] ?? null;
            $reference = $_POST['transaction_reference'] ?? null;
            $date = $_POST['transaction_date'];

            $multiplier = $type === 'Deposit' ? 1 : -1;
            $stmt = $conn->prepare("UPDATE bank_accounts SET balance = balance + ? WHERE id = ?");
            $adjusted_amount = $amount * $multiplier;
            $stmt->bind_param("di", $adjusted_amount, $account_id);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("INSERT INTO bank_transactions (account_id, type, amount, details, reference, date) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isdsds", $account_id, $type, $amount, $details, $reference, $date);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Fetch accounts and (optionally filtered) transactions
$accounts = $conn->query("SELECT * FROM bank_accounts");

$selected_account_id = $_GET['account_id'] ?? null;
if ($selected_account_id) {
    $stmt = $conn->prepare("SELECT bt.*, ba.name as account_name FROM bank_transactions bt JOIN bank_accounts ba ON bt.account_id = ba.id WHERE bt.account_id = ? ORDER BY bt.date DESC");
    $stmt->bind_param("i", $selected_account_id);
    $stmt->execute();
    $transactions = $stmt->get_result();
    $stmt->close();
} else {
    $transactions = $conn->query("SELECT bt.*, ba.name as account_name FROM bank_transactions bt JOIN bank_accounts ba ON bt.account_id = ba.id ORDER BY bt.date DESC");
}
?>

<h2>ব্যাংক</h2>
<div class="mb-3 d-flex gap-2">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAccountModal">নতুন অ্যাকাউন্ট</button>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTransactionModal">নতুন লেনদেন</button>
</div>

<h4>অ্যাকাউন্টসমূহ</h4>
<div class="row row-cols-1 row-cols-md-3 g-3 mb-4">
    <?php while ($acc = $accounts->fetch_assoc()): ?>
        <div class="col">
            <div class="card border-info">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($acc['name']); ?></h5>
                    <p class="card-text">ব্যালেন্স: ৳<?php echo number_format($acc['balance'], 2); ?></p>
                    <a href="?account_id=<?php echo $acc['id']; ?>" class="btn btn-outline-primary btn-sm">লেনদেন দেখুন</a>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<h4>লেনদেনের ইতিহাস <?php if ($selected_account_id) echo '(ফিল্টার করা)' ?></h4>
<table class="table table-bordered table-sm">
    <thead>
        <tr>
            <th>তারিখ</th>
            <th>অ্যাকাউন্ট</th>
            <th>ধরন</th>
            <th>পরিমাণ</th>
            <th>বিস্তারিত</th>
            <th>রেফারেন্স</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($txn = $transactions->fetch_assoc()): ?>
            <tr>
                <td><?php echo $txn['date']; ?></td>
                <td><?php echo htmlspecialchars($txn['account_name']); ?></td>
                <td><?php echo $txn['type'] === 'Deposit' ? 'জমা' : 'উত্তোলন'; ?></td>
                <td class="<?php echo $txn['type'] === 'Deposit' ? 'text-success' : 'text-danger'; ?>">
                    <?php echo ($txn['type'] === 'Deposit' ? '+' : '-') . number_format($txn['amount'], 2); ?>
                </td>
                <td><?php echo htmlspecialchars($txn['details'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($txn['reference'] ?? '-'); ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<!-- Add Account Modal -->
<div class="modal fade" id="addAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <input type="hidden" name="action" value="add_account">
            <div class="modal-header">
                <h5 class="modal-title">নতুন অ্যাকাউন্ট</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label>ব্যাংকের নাম</label>
                <input type="text" name="bank_name" class="form-control" required>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">সংরক্ষণ</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Transaction Modal -->
<div class="modal fade" id="addTransactionModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <input type="hidden" name="action" value="add_transaction">
            <div class="modal-header">
                <h5 class="modal-title">নতুন লেনদেন</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label>অ্যাকাউন্ট</label>
                <select name="transaction_account" class="form-control mb-2" required>
                    <?php
                    $accounts->data_seek(0); // rewind for reuse
                    while ($acc = $accounts->fetch_assoc()): ?>
                        <option value="<?php echo $acc['id']; ?>"><?php echo htmlspecialchars($acc['name']); ?></option>
                    <?php endwhile; ?>
                </select>

                <label>ধরন</label>
                <select name="transaction_type" class="form-control mb-2" required>
                    <option value="Deposit">জমা</option>
                    <option value="Withdraw">উত্তোলন</option>
                </select>

                <label>পরিমাণ</label>
                <input type="number" name="transaction_amount" class="form-control mb-2" step="0.01" required>

                <label>বিস্তারিত</label>
                <input type="text" name="transaction_details" class="form-control mb-2">

                <label>রেফারেন্স</label>
                <input type="text" name="transaction_reference" class="form-control mb-2">

                <label>তারিখ</label>
                <input type="date" name="transaction_date" class="form-control mb-2" required>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">সংরক্ষণ</button>
            </div>
        </form>
    </div>
</div>
