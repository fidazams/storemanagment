<?php
require_once BASE_PATH . 'db.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add_investment') {
            $amount = $_POST['invest_amount'];
            $note = isset($_POST['invest_note']) ? $_POST['invest_note'] : null;
            $date = $_POST['invest_date'];

            $stmt = $conn->prepare("INSERT INTO investments (amount, note, date) VALUES (?, ?, ?)");
            $stmt->bind_param("dss", $amount, $note, $date);
            $stmt->execute();
            $stmt->close();
        } elseif ($_POST['action'] === 'add_loan') {
            $amount = $_POST['loan_amount'];
            $note = isset($_POST['loan_note']) ? $_POST['loan_note'] : null;
            $date = $_POST['loan_date'];

            $stmt = $conn->prepare("INSERT INTO loans (amount, note, date) VALUES (?, ?, ?)");
            $stmt->bind_param("dss", $amount, $note, $date);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Fetch investments and loans
$investments = $conn->query("SELECT * FROM investments ORDER BY date DESC");
$loans = $conn->query("SELECT * FROM loans ORDER BY date DESC");
?>

<h2>ইনভেস্ট ও লোন</h2>
<div class="mb-3">
    <button class="btn btn-primary" onclick="showInvestPopup()">নতুন ইনভেস্টমেন্ট</button>
    <button class="btn btn-primary" onclick="showLoanPopup()">নতুন লোন/উত্তোলন</button>
</div>

<h3>ইনভেস্টমেন্ট</h3>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>তারিখ</th>
            <th>পরিমাণ</th>
            <th>নোট</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($investment = $investments->fetch_assoc()): ?>
            <tr>
                <td><?php echo $investment['date']; ?></td>
                <td>৳<?php echo number_format($investment['amount'], 2); ?></td>
                <td><?php echo htmlspecialchars($investment['note'] ?? '-'); ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<h3>লোন/উত্তোলন</h3>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>তারিখ</th>
            <th>পরিমাণ</th>
            <th>নোট</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($loan = $loans->fetch_assoc()): ?>
            <tr>
                <td><?php echo $loan['date']; ?></td>
                <td>৳<?php echo number_format($loan['amount'], 2); ?></td>
                <td><?php echo htmlspecialchars($loan['note'] ?? '-'); ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>