<?php
require_once BASE_PATH . 'db.php';

// Fetch stock info
$today = date('Y-m-d');
$stock_in_today = $conn->query("SELECT SUM(quantity) as total FROM stock_transactions WHERE type='in' AND date='$today'")->fetch_assoc()['total'] ?? 0;
$stock_out_today = $conn->query("SELECT SUM(quantity) as total FROM stock_transactions WHERE type='out' AND date='$today'")->fetch_assoc()['total'] ?? 0;
$stock_in_value = $conn->query("SELECT SUM(total_price) as total FROM stock_transactions WHERE type='in' AND date='$today'")->fetch_assoc()['total'] ?? 0;
$stock_out_value = $conn->query("SELECT SUM(total_price) as total FROM stock_transactions WHERE type='out' AND date='$today'")->fetch_assoc()['total'] ?? 0;
$income_today = $conn->query("SELECT SUM(total_price) as total FROM stock_transactions WHERE type='out' AND date='$today'")->fetch_assoc()['total'] ?? 0;
$profit_today = $conn->query("SELECT SUM((unit_price - (SELECT buy_rate FROM products WHERE id=stock_transactions.product_id)) * quantity) as total FROM stock_transactions WHERE type='out' AND date='$today'")->fetch_assoc()['total'] ?? 0;
$low_stock = $conn->query("SELECT COUNT(*) as total FROM products WHERE stock <= 5")->fetch_assoc()['total'] ?? 0;

// Fetch cost info
$cost_today = $conn->query("SELECT SUM(amount) as total FROM costs WHERE date='$today'")->fetch_assoc()['total'] ?? 0;
$yesterday = date('Y-m-d', strtotime('-1 day'));
$cost_yesterday = $conn->query("SELECT SUM(amount) as total FROM costs WHERE date='$yesterday'")->fetch_assoc()['total'] ?? 0;
$this_month = date('Y-m');
$cost_this_month = $conn->query("SELECT SUM(amount) as total FROM costs WHERE DATE_FORMAT(date, '%Y-%m')='$this_month'")->fetch_assoc()['total'] ?? 0;
$last_month = date('Y-m', strtotime('-1 month'));
$cost_last_month = $conn->query("SELECT SUM(amount) as total FROM costs WHERE DATE_FORMAT(date, '%Y-%m')='$last_month'")->fetch_assoc()['total'] ?? 0;

// Fetch supplier info
$total_suppliers = $conn->query("SELECT COUNT(*) as total FROM parties WHERE type='supplier'")->fetch_assoc()['total'] ?? 0;
$total_to_pay = $conn->query("SELECT SUM(due_amount) as total FROM parties WHERE type='supplier'")->fetch_assoc()['total'] ?? 0;

// Fetch customer info
$total_customers = $conn->query("SELECT COUNT(*) as total FROM parties WHERE type='customer'")->fetch_assoc()['total'] ?? 0;
$total_to_collect = $conn->query("SELECT SUM(due_amount) as total FROM parties WHERE type='customer'")->fetch_assoc()['total'] ?? 0;

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
?>

<h2>ড্যাশবোর্ড</h2>
<div class="row">
    <!-- Stock Info -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <span>স্টক তথ্য</span>
                <div>
                    <a href="?page=stock_in" class="btn btn-sm btn-success">স্টক ইন</a>
                    <a href="?page=stock_out" class="btn btn-sm btn-warning">স্টক আউট</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-arrow-down text-success me-2"></i>
                            <div>
                                <small>আজকের স্টক ইন পরিমাণ</small>
                                <h5><?php echo $stock_in_today; ?></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-arrow-up text-warning me-2"></i>
                            <div>
                                <small>আজকের স্টক আউট পরিমাণ</small>
                                <h5><?php echo $stock_out_today; ?></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-money-bill-wave text-primary me-2"></i>
                            <div>
                                <small>আজকের স্টক ইন মূল্য</small>
                                <h5>৳<?php echo number_format($stock_in_value, 2); ?></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-money-bill-wave text-primary me-2"></i>
                            <div>
                                <small>আজকের স্টক আউট মূল্য</small>
                                <h5>৳<?php echo number_format($stock_out_value, 2); ?></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-coins text-success me-2"></i>
                            <div>
                                <small>আজকের আয়</small>
                                <h5>৳<?php echo number_format($income_today, 2); ?></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-chart-line text-success me-2"></i>
                            <div>
                                <small>আজকের লাভ</small>
                                <h5>৳<?php echo number_format($profit_today, 2); ?></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                            <div>
                                <small>লো স্টক প্রোডাক্ট</small>
                                <h5><?php echo $low_stock; ?> <a href="?page=product_list&low_stock=1" class="btn btn-sm btn-danger ms-2">দেখুন</a></h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Cost -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">খরচ</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-money-bill-wave me-2"></i>
                            <div>
                                <small>আজকের খরচ</small>
                                <h5>৳<?php echo number_format($cost_today, 2); ?></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-money-bill-wave me-2"></i>
                            <div>
                                <small>গতকালের খরচ</small>
                                <h5>৳<?php echo number_format($cost_yesterday, 2); ?></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-money-bill-wave me-2"></i>
                            <div>
                                <small>এই মাসের খরচ</small>
                                <h5>৳<?php echo number_format($cost_this_month, 2); ?></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-money-bill-wave me-2"></i>
                            <div>
                                <small>গত মাসের খরচ</small>
                                <h5>৳<?php echo number_format($cost_last_month, 2); ?></h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Supplier -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">সরবরাহকারী</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-users me-2"></i>
                            <div>
                                <small>মোট সরবরাহকারী</small>
                                <h5><?php echo $total_suppliers; ?></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-money-bill-wave me-2"></i>
                            <div>
                                <small>মোট পরিশোধ করতে হবে</small>
                                <h5>৳<?php echo number_format($total_to_pay, 2); ?></h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Customer -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">গ্রাহক</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-users me-2"></i>
                            <div>
                                <small>মোট গ্রাহক</small>
                                <h5><?php echo $total_customers; ?></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-money-bill-wave me-2"></i>
                            <div>
                                <small>মোট পাওনা</small>
                                <h5>৳<?php echo number_format($total_to_collect, 2); ?></h5>
                            </div>
                        </div>
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