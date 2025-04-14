<?php
require_once BASE_PATH . 'db.php';

// Handle date range filter
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-7 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Fetch report data
$stock_in = $conn->query("SELECT SUM(total_price) as total FROM stock_transactions WHERE type='in' AND date BETWEEN '$start_date' AND '$end_date'")->fetch_assoc()['total'] ?? 0;
$stock_out = $conn->query("SELECT SUM(total_price) as total FROM stock_transactions WHERE type='out' AND date BETWEEN '$start_date' AND '$end_date'")->fetch_assoc()['total'] ?? 0;
$profit = $conn->query("SELECT SUM((unit_price - (SELECT buy_rate FROM products WHERE id=stock_transactions.product_id)) * quantity) as total FROM stock_transactions WHERE type='out' AND date BETWEEN '$start_date' AND '$end_date'")->fetch_assoc()['total'] ?? 0;
$costs = $conn->query("SELECT SUM(amount) as total FROM costs WHERE date BETWEEN '$start_date' AND '$end_date'")->fetch_assoc()['total'] ?? 0;
$investments = $conn->query("SELECT SUM(amount) as total FROM investments WHERE date BETWEEN '$start_date' AND '$end_date'")->fetch_assoc()['total'] ?? 0;
$loans = $conn->query("SELECT SUM(amount) as total FROM loans WHERE date BETWEEN '$start_date' AND '$end_date'")->fetch_assoc()['total'] ?? 0;
$assets = $conn->query("SELECT SUM(amount) as total FROM assets WHERE date BETWEEN '$start_date' AND '$end_date'")->fetch_assoc()['total'] ?? 0;

// Fetch graph data
$graph_data = [];
$graph_dates = [];
$current_date = strtotime($start_date);
$end_timestamp = strtotime($end_date);
while ($current_date <= $end_timestamp) {
    $date = date('Y-m-d', $current_date);
    $graph_dates[] = date('d M', $current_date);
    $stock_in_day = $conn->query("SELECT SUM(total_price) as total FROM stock_transactions WHERE type='in' AND date='$date'")->fetch_assoc()['total'] ?? 0;
    $stock_out_day = $conn->query("SELECT SUM(total_price) as total FROM stock_transactions WHERE type='out' AND date='$date'")->fetch_assoc()['total'] ?? 0;
    $graph_data['stock_in'][] = $stock_in_day;
    $graph_data['stock_out'][] = $stock_out_day;
    $current_date = strtotime("+1 day", $current_date);
}
?>

<h2>ব্যবসায়িক প্রতিবেদন</h2>
<div class="mb-3">
    <form method="GET" class="row g-3">
        <input type="hidden" name="page" value="business_report">
        <div class="col-md-4">
            <label>শুরুর তারিখ</label>
            <input type="date" class="form-control" name="start_date" value="<?php echo $start_date; ?>" required>
        </div>
        <div class="col-md-4">
            <label>শেষের তারিখ</label>
            <input type="date" class="form-control" name="end_date" value="<?php echo $end_date; ?>" required>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button type="submit" class="btn btn-primary me-2">ফিল্টার করুন</button>
            <a href="#" class="btn btn-success" onclick="window.print()">PDF ডাউনলোড</a>
        </div>
    </form>
</div>

<div class="row">
    <div class="col-md-12">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>মেট্রিক</th>
                    <th>মান</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>স্টক ইন</td>
                    <td>৳<?php echo number_format($stock_in, 2); ?></td>
                </tr>
                <tr>
                    <td>স্টক আউট</td>
                    <td>৳<?php echo number_format($stock_out, 2); ?></td>
                </tr>
                <tr>
                    <td>লাভ</td>
                    <td>৳<?php echo number_format($profit, 2); ?></td>
                </tr>
                <tr>
                    <td>খরচ</td>
                    <td>৳<?php echo number_format($costs, 2); ?></td>
                </tr>
                <tr>
                    <td>ইনভেস্টমেন্ট</td>
                    <td>৳<?php echo number_format($investments, 2); ?></td>
                </tr>
                <tr>
                    <td>লোন/উত্তোলন</td>
                    <td>৳<?php echo number_format($loans, 2); ?></td>
                </tr>
                <tr>
                    <td>সম্পদ</td>
                    <td>৳<?php echo number_format($assets, 2); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">স্টক ইন/স্টক আউট</div>
            <div class="card-body">
                <canvas id="reportChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('reportChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($graph_dates); ?>,
            datasets: [
                {
                    label: 'স্টক ইন',
                    data: <?php echo json_encode($graph_data['stock_in']); ?>,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    fill: true,
                },
                {
                    label: 'স্টক আউট',
                    data: <?php echo json_encode($graph_data['stock_out']); ?>,
                    borderColor: '#fd7e14',
                    backgroundColor: 'rgba(253, 126, 20, 0.1)',
                    fill: true,
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'তারিখ'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'মূল্য (৳)'
                    },
                    beginAtZero: true
                }
            }
        }
    });
});
</script>