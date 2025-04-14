<?php
require_once BASE_PATH . 'db.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_asset') {
    $asset_name = $_POST['asset_name'];
    $asset_amount = $_POST['asset_amount'];
    $from_cash = isset($_POST['asset_from_cash']) ? 1 : 0;
    $date = $_POST['asset_date'];

    $stmt = $conn->prepare("INSERT INTO assets (name, amount, from_cash, date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdis", $asset_name, $asset_amount, $from_cash, $date);
    $stmt->execute();
    $stmt->close();
}

// Fetch assets
$assets = $conn->query("SELECT * FROM assets ORDER BY date DESC");
?>

<h2>সম্পদ</h2>
<div class="mb-3">
    <button class="btn btn-primary" onclick="showBuyAssetPopup()">নতুন সম্পদ কিনুন</button>
</div>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>তারিখ</th>
            <th>সম্পদের নাম</th>
            <th>পরিমাণ</th>
            <th>নগদ থেকে কেনা</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($asset = $assets->fetch_assoc()): ?>
            <tr>
                <td><?php echo $asset['date']; ?></td>
                <td><?php echo htmlspecialchars($asset['name']); ?></td>
                <td>৳<?php echo number_format($asset['amount'], 2); ?></td>
                <td><?php echo $asset['from_cash'] ? 'হ্যাঁ' : 'না'; ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>