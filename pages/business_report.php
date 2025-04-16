<?php
require_once BASE_PATH . 'db.php';

// Sample static data (replace with actual queries later)
$presentStock = 120; // units
$stockValue = 150000;
$cashBalance = 35000;
$bankBalance = 50000;
$totalAsset = $stockValue + $cashBalance + $bankBalance;
$netInvestment = 100000;
$totalBusinessValue = $totalAsset + $netInvestment;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Business Report</h3>
</div>

<!-- KPI Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-2">
        <div class="card shadow-sm border-start border-dark">
            <div class="card-body text-center">
                <h6 class="text-muted">Present Stock</h6>
                <h5 class="fw-bold"><?= $presentStock ?> units</h5>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card shadow-sm border-start border-primary">
            <div class="card-body text-center">
                <h6 class="text-muted">Stock Value</h6>
                <h5 class="text-primary fw-bold">৳<?= number_format($stockValue) ?></h5>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card shadow-sm border-start border-success">
            <div class="card-body text-center">
                <h6 class="text-muted">Cash Balance</h6>
                <h5 class="text-success fw-bold">৳<?= number_format($cashBalance) ?></h5>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card shadow-sm border-start border-info">
            <div class="card-body text-center">
                <h6 class="text-muted">Bank Balance</h6>
                <h5 class="text-info fw-bold">৳<?= number_format($bankBalance) ?></h5>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card shadow-sm border-start border-secondary">
            <div class="card-body text-center">
                <h6 class="text-muted">Total Asset</h6>
                <h5 class="fw-bold">৳<?= number_format($totalAsset) ?></h5>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card shadow-sm border-start border-warning">
            <div class="card-body text-center">
                <h6 class="text-muted">Business Value</h6>
                <h5 class="text-warning fw-bold">৳<?= number_format($totalBusinessValue) ?></h5>
            </div>
        </div>
    </div>
</div>

<!-- Reports Button Group -->
<h5 class="mt-4 mb-3">Reports</h5>
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <button class="btn btn-outline-dark w-100 d-flex align-items-center justify-content-center py-2">
            <i class="bi bi-box-seam me-2"></i> Stock Report
        </button>
    </div>
    <div class="col-md-3">
        <button class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center py-2">
            <i class="bi bi-truck me-2"></i> Supplier Report
        </button>
    </div>
    <div class="col-md-3">
        <button class="btn btn-outline-success w-100 d-flex align-items-center justify-content-center py-2">
            <i class="bi bi-people me-2"></i> Customer Report
        </button>
    </div>
    <div class="col-md-3">
        <button class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center py-2">
            <i class="bi bi-cash-coin me-2"></i> Cost Report
        </button>
    </div>
</div>

<!-- Charts -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="mb-3">Business Overview (Dummy Daily Incomes)</h6>
                <canvas id="overviewChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="mb-3">Profit / Loss Overview (Dummy)</h6>
                <canvas id="profitLossChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Chart Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx1 = document.getElementById('overviewChart').getContext('2d');
new Chart(ctx1, {
    type: 'line',
    data: {
        labels: ['Sat', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
        datasets: [{
            label: 'Daily Income',
            data: [1200, 1500, 1000, 1800, 2000, 1700, 2200],
            borderColor: 'rgba(75, 192, 192, 1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true }
        }
    }
});

const ctx2 = document.getElementById('profitLossChart').getContext('2d');
new Chart(ctx2, {
    type: 'bar',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr'],
        datasets: [
            {
                label: 'Profit',
                data: [5000, 7000, 4000, 6500],
                backgroundColor: 'rgba(54, 162, 235, 0.7)'
            },
            {
                label: 'Loss',
                data: [2000, 3000, 2500, 1000],
                backgroundColor: 'rgba(255, 99, 132, 0.7)'
            }
        ]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>
