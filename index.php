<?php
define('BASE_PATH', dirname(__FILE__) . '/');
session_start();
require_once BASE_PATH . 'db.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Calculate total balance (for simplicity, sum of bank balances for now)
$total_balance = $conn->query("SELECT SUM(balance) as total FROM bank_accounts")->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div>দোকান ব্যবস্থাপনা</div>
        <div>
            <span>মোট ব্যালেন্স: ৳<?php echo number_format($total_balance, 2); ?></span>
            <button class="btn btn-sm btn-outline-secondary ms-3">প্রোফাইল</button>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
        <a href="?page=dashboard">ড্যাশবোর্ড</a>
        <a href="?page=product_list">পণ্য তালিকা</a>
        <a href="?page=stock_dashboard">স্টক ড্যাশবোর্ড</a>
        <a href="?page=stock_history">স্টক ইতিহাস</a>
        <a href="?page=party_list">পার্টি তালিকা</a>
        <a href="?page=cost">খরচ</a>
        <a href="?page=bank">ব্যাংক</a>
        <a href="?page=invest_loan">ইনভেস্ট ও লোন</a>
        <a href="?page=asset">সম্পদ</a>
        <a href="?page=business_report">ব্যবসায়িক প্রতিবেদন</a>
    </div>

    <!-- Main Content -->
    <div class="content">
        <?php
        $page_files = [
            'dashboard' => 'pages/dashboard.php',
            'product_list' => 'pages/product_list.php',
            'add_product' => 'pages/add_product.php',
            'stock_dashboard' => 'pages/stock_dashboard.php',
            'stock_in' => 'pages/stock_in.php',
            'stock_out' => 'pages/stock_out.php',
            'stock_history' => 'pages/stock_history.php',
            'party_list' => 'pages/party_list.php',
            'cost' => 'pages/cost.php',
            'bank' => 'pages/bank.php',
            'invest_loan' => 'pages/invest_loan.php',
            'asset' => 'pages/asset.php',
            'business_report' => 'pages/business_report.php'
        ];

        if (isset($page_files[$page]) && file_exists($page_files[$page])) {
            include $page_files[$page];
        } else {
            echo "<h2>পৃষ্ঠা পাওয়া যায়নি</h2>";
        }
        ?>
    </div>

    <!-- Popup Modal -->
    <div class="modal fade" id="genericModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalBody">
                </div>
                <div class="modal-footer" id="modalFooter">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>

<?php
$conn->close();
?>