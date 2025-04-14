<?php
require_once BASE_PATH . 'db.php';

// Fetch stock summary
$stock_in_total = $conn->query("SELECT SUM(quantity) as total FROM stock_transactions WHERE type='in'")->fetch_assoc()['total'] ?? 0;
$stock_out_total = $conn->query("SELECT SUM(quantity) as total FROM stock_transactions WHERE type='out'")->fetch_assoc()['total'] ?? 0;
$stock_in_value = $conn->query("SELECT SUM(total_price) as total FROM stock_transactions WHERE type='in'")->fetch_assoc()['total'] ?? 0;
$stock_out_value = $conn->query("SELECT SUM(total_price) as total FROM stock_transactions WHERE type='out'")->fetch_assoc()['total'] ?? 0;

// Fetch stock in/out data for the last 7 days for the graph
$stock_data = [];
$dates = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dates[] = date('d M', strtotime($date));
    $stock_in = $conn->query("SELECT SUM(quantity) as total FROM stock_transactions WHERE type='in' AND date='$date'")->fetch_assoc()['total'] ?? 0;
    $stock_out = $conn->query("SELECT SUM(quantity) as total FROM stock_transactions WHERE type='out' AND date='$date'")->fetch_assoc()['total'] ?? 0;
    $stock_data['in'][] = $stock_in;
    $stock_data['out'][] = $stock_out;
}

// Fetch recent transactions
$recent_transactions = $conn->query("SELECT st.*, p.name as product_name FROM stock_transactions st JOIN products p ON st.product_id = p.id ORDER BY st.date DESC LIMIT 10");
?>

<h2>স্টক ড্যাশবোর্ড</h2>
<div class="row">
    <!-- Stock In -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <i class="fas fa-arrow-down text-success me-2"></i>
                    <div>
                        <small>স্টক ইন</small>
                        <h5>৳<?php echo number_format($stock_in_value, 2); ?></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Stock Out -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <i class="fas fa-arrow-up text-warning me-2"></i>
                    <div>
                        <small>স্টক আউট</small>
                        <h5>৳<?php echo number_format($stock_out_value, 2); ?></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Stock Graph -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">স্টক ইন/স্টক আউট</div>
            <div class="card-body">
                <canvas id="stockChart"></canvas>
            </div>
        </div>
    </div>
    <!-- Recent Transactions -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">সাম্প্রতিক লেনদেন</div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>তারিখ</th>
                            <th>পণ্য</th>
                            <th>ধরণ</th>
                            <th>পরিমাণ</th>
                            <th>মোট মূল্য</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($transaction = $recent_transactions->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $transaction['date']; ?></td>
                                <td><?php echo htmlspecialchars($transaction['product_name']); ?></td>
                                <td><?php echo $transaction['type'] == 'in' ? 'ইন' : 'আউট'; ?></td>
                                <td><?php echo $transaction['quantity']; ?></td>
                                <td>৳<?php echo number_format($transaction['total_price'], 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('stockChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($dates); ?>,
            datasets: [
                {
                    label: 'স্টক ইন',
                    data: <?php echo json_encode($stock_data['in']); ?>,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    fill: true,
                },
                {
                    label: 'স্টক আউট',
                    data: <?php echo json_encode($stock_data['out']); ?>,
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
                        text: 'পরিমাণ'
                    },
                    beginAtZero: true
                }
            }
        }
    });
});
</script>