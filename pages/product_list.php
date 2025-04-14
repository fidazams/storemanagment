<?php
require_once BASE_PATH . 'db.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add_product') {
            $product_name = $_POST['product_name'];
            $product_code = isset($_POST['product_code']) ? $_POST['product_code'] : null; // Allow product code to be optional
            $buy_rate = $_POST['buy_rate'];
            $sell_rate = $_POST['sell_rate'];

            $stmt = $conn->prepare("INSERT INTO products (name, code, buy_rate, sell_rate, stock) VALUES (?, ?, ?, ?, 0)");
            $stmt->bind_param("ssdd", $product_name, $product_code, $buy_rate, $sell_rate);
            $stmt->execute();
            $stmt->close();
        } elseif ($_POST['action'] === 'delete_product') {
            $product_id = $_POST['product_id'];
            $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Fetch products
$low_stock_filter = isset($_GET['low_stock']) && $_GET['low_stock'] == 1;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$query = "SELECT * FROM products WHERE 1=1";
if ($low_stock_filter) {
    $query .= " AND stock <= 5";
}
if ($search) {
    $query .= " AND (name LIKE '%$search%' OR code LIKE '%$search%')";
}
$products = $conn->query($query);

// Calculate summary
$total_products = $conn->query("SELECT COUNT(*) as total FROM products")->fetch_assoc()['total'] ?? 0;
?>

<h2>পণ্য তালিকা</h2>
<div class="row mb-3">
    <div class="col-md-6 d-flex align-items-center">
        <form method="GET" class="d-flex w-100">
            <input type="hidden" name="page" value="product_list">
            <input type="text" name="search" class="form-control me-2" placeholder="পণ্য খুঁজুন" value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary me-2">খুঁজুন</button>
            <a href="?page=product_list&low_stock=1" class="btn btn-danger">লো স্টক</a>
        </form>
    </div>
    <div class="col-md-6 text-end">
        <p class="mb-0">মোট পণ্য: <?php echo $total_products; ?></p>
        <button class="btn btn-primary mt-2" onclick="showAddProductPopup()">নতুন পণ্য যোগ করুন</button>
    </div>
</div>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>পণ্যের নাম</th>
            <th>পণ্যের কোড</th>
            <th>ক্রয় মূল্য</th>
            <th>বিক্রয় মূল্য</th>
            <th>স্টক</th>
            <th>অ্যাকশন</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($product = $products->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($product['name']); ?></td>
                <td><?php echo htmlspecialchars($product['code'] ?? '-'); ?></td>
                <td>৳<?php echo number_format($product['buy_rate'], 2); ?></td>
                <td>৳<?php echo number_format($product['sell_rate'], 2); ?></td>
                <td><?php echo $product['stock']; ?></td>
                <td>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('আপনি কি এই পণ্যটি মুছতে চান?');">
                        <input type="hidden" name="action" value="delete_product">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <button type="submit" class="btn btn-sm btn-danger">মুছুন</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>