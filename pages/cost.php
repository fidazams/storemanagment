<?php
require_once BASE_PATH . 'db.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add_cost_field') {
            $cost_field = $_POST['cost_field'];
            $stmt = $conn->prepare("INSERT INTO cost_fields (name) VALUES (?)");
            $stmt->bind_param("s", $cost_field);
            $stmt->execute();
            $stmt->close();
        } elseif ($_POST['action'] === 'add_cost') {
            $cost_date = $_POST['cost_date'];
            $cost_field = $_POST['cost_field'];
            $cost_amount = $_POST['cost_amount'];
            $cost_details = isset($_POST['cost_details']) ? $_POST['cost_details'] : null;
            $cost_reference = isset($_POST['cost_reference']) ? $_POST['cost_reference'] : null;

            $stmt = $conn->prepare("INSERT INTO costs (date, field, amount, details, reference) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdss", $cost_date, $cost_field, $cost_amount, $cost_details, $cost_reference);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Fetch cost fields and costs
$cost_fields = $conn->query("SELECT * FROM cost_fields");
$costs = $conn->query("SELECT * FROM costs ORDER BY date DESC");
?>

<h2>খরচ</h2>
<div class="mb-3">
    <button class="btn btn-primary" onclick="showFieldOfCostPopup()">খরচের ক্ষেত্র যোগ করুন</button>
    <button class="btn btn-primary" onclick="showAddNewCostPopup()">নতুন খরচ যোগ করুন</button>
</div>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>তারিখ</th>
            <th>খরচের ক্ষেত্র</th>
            <th>পরিমাণ</th>
            <th>বিস্তারিত</th>
            <th>রেফারেন্স</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($cost = $costs->fetch_assoc()): ?>
            <tr>
                <td><?php echo $cost['date']; ?></td>
                <td><?php echo htmlspecialchars($cost['field']); ?></td>
                <td>৳<?php echo number_format($cost['amount'], 2); ?></td>
                <td><?php echo htmlspecialchars($cost['details'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($cost['reference'] ?? '-'); ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>