<?php
require_once BASE_PATH . 'db.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add_investment') {
            $amount = $_POST['invest_amount'];
            $note = $_POST['invest_note'] ?? null;
            $date = $_POST['invest_date'];

            $stmt = $conn->prepare("INSERT INTO investments (amount, note, date) VALUES (?, ?, ?)");
            $stmt->bind_param("dss", $amount, $note, $date);
            $stmt->execute();
            $stmt->close();
        } elseif ($_POST['action'] === 'add_loan') {
            $amount = $_POST['loan_amount'];
            $note = $_POST['loan_note'] ?? null;
            $date = $_POST['loan_date'];

            $stmt = $conn->prepare("INSERT INTO loans (amount, note, date) VALUES (?, ?, ?)");
            $stmt->bind_param("dss", $amount, $note, $date);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Fetch totals
$totalInvest = $conn->query("SELECT SUM(amount) as total FROM investments")->fetch_assoc()['total'] ?? 0;
$totalLoan = $conn->query("SELECT SUM(amount) as total FROM loans")->fetch_assoc()['total'] ?? 0;
$netInvest = $totalInvest - $totalLoan;

// Fetch investments and loans
$investments = $conn->query("SELECT * FROM investments ORDER BY date DESC");
$loans = $conn->query("SELECT * FROM loans ORDER BY date DESC");
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">ইনভেস্ট ও লোন</h3>
    <div>
        <button class="btn btn-sm btn-primary me-2" onclick="showInvestPopup()">নতুন ইনভেস্টমেন্ট</button>
        <button class="btn btn-sm btn-success" onclick="showLoanPopup()">নতুন লোন/উত্তোলন</button>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4 mb-2">
        <div class="card shadow-sm border-start border-primary">
            <div class="card-body">
                <h6 class="text-muted">মোট ইনভেস্টমেন্ট</h6>
                <h4 class="text-primary fw-bold">৳<?= number_format($totalInvest, 2) ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-2">
        <div class="card shadow-sm border-start border-danger">
            <div class="card-body">
                <h6 class="text-muted">মোট লোন</h6>
                <h4 class="text-danger fw-bold">৳<?= number_format($totalLoan, 2) ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-2">
        <div class="card shadow-sm border-start border-success">
            <div class="card-body">
                <h6 class="text-muted">নেট ইনভেস্টমেন্ট</h6>
                <h4 class="text-success fw-bold">৳<?= number_format($netInvest, 2) ?></h4>
            </div>
        </div>
    </div>
</div>

<h5 class="mt-4">ইনভেস্টমেন্ট</h5>
<table class="table table-bordered table-sm table-striped">
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
                <td><?= htmlspecialchars($investment['date']) ?></td>
                <td>৳<?= number_format($investment['amount'], 2) ?></td>
                <td><?= htmlspecialchars($investment['note'] ?? '-') ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<h5 class="mt-4">লোন/উত্তোলন</h5>
<table class="table table-bordered table-sm table-striped">
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
                <td><?= htmlspecialchars($loan['date']) ?></td>
                <td>৳<?= number_format($loan['amount'], 2) ?></td>
                <td><?= htmlspecialchars($loan['note'] ?? '-') ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>
