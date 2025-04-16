<?php
require_once BASE_PATH . 'db.php';

// Default: Full previous month
$startDate = date('Y-m-01', strtotime('first day of last month'));
$endDate = date('Y-m-t', strtotime('last day of last month'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $startDate = $_POST['start'] ?? $startDate;
    $endDate = $_POST['end'] ?? $endDate;
}

// Dummy data: Replace with your actual stock/profit data
$products = [
    ['name' => 'Product A', 'code' => 'A001', 'present_stock' => 50, 'stock_in' => 100, 'stock_out' => 50, 'profit' => 5000],
    ['name' => 'Product B', 'code' => 'B002', 'present_stock' => 20, 'stock_in' => 70, 'stock_out' => 50, 'profit' => 3000],
    ['name' => 'Product C', 'code' => 'C003', 'present_stock' => 100, 'stock_in' => 150, 'stock_out' => 50, 'profit' => 7000],
];

$total_in = $total_out = $total_profit = 0;
foreach ($products as $p) {
    $total_in += $p['stock_in'];
    $total_out += $p['stock_out'];
    $total_profit += $p['profit'];
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Stock Report</h3>
    <form action="stock_report_pdf.php" method="get" target="_blank">
        <input type="hidden" name="start" value="<?= $startDate ?>">
        <input type="hidden" name="end" value="<?= $endDate ?>">
        <button class="btn btn-danger"><i class="bi bi-file-earmark-pdf"></i> Download PDF</button>
    </form>
</div>

<!-- Filter Form -->
<form method="POST" class="row g-3 align-items-end mb-4">
    <div class="col-md-3">
        <label class="form-label">Start Date</label>
        <input type="date" name="start" class="form-control" value="<?= $startDate ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label">End Date</label>
        <input type="date" name="end" class="form-control" value="<?= $endDate ?>">
    </div>
    <div class="col-md-3">
        <button type="submit" class="btn btn-primary w-100">Filter</button>
    </div>
</form>

<!-- Stock Table -->
<div class="card shadow-sm">
    <div class="card-body">
        <h6 class="mb-3">Stock Details with Profit</h6>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Product Name</th>
                        <th>Code</th>
                        <th>Present Stock</th>
                        <th>Total In</th>
                        <th>Total Out</th>
                        <th>Profit (৳)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td><?= $p['name'] ?></td>
                            <td><?= $p['code'] ?></td>
                            <td><?= $p['present_stock'] ?></td>
                            <td><?= $p['stock_in'] ?></td>
                            <td><?= $p['stock_out'] ?></td>
                            <td><?= number_format($p['profit']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="fw-bold table-secondary">
                        <td colspan="3">Total</td>
                        <td><?= $total_in ?></td>
                        <td><?= $total_out ?></td>
                        <td><?= number_format($total_profit) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
