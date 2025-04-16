<?php
require_once BASE_PATH . 'db.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_party') {
    $party_type = $_POST['party_type'] ?? '';
    $party_name = $_POST['party_name'] ?? '';
    $phone = !empty($_POST['mobile_number']) ? $_POST['mobile_number'] : null;
    $address = !empty($_POST['address']) ? $_POST['address'] : null;
    $due_amount = isset($_POST['due_amount']) && $_POST['due_amount'] !== '' ? (float)$_POST['due_amount'] : 0.00;

    if (empty($party_type) || empty($party_name)) {
        echo "<div class='alert alert-danger'>পার্টির ধরণ এবং নাম আবশ্যক।</div>";
    } else {
        $stmt = $conn->prepare("INSERT INTO parties (type, name, phone, address, due_amount) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssds", $party_type, $party_name, $phone, $address, $due_amount);
        
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>পার্টি সফলভাবে যোগ করা হয়েছে।</div>";
        } else {
            echo "<div class='alert alert-danger'>পার্টি যোগ করতে ব্যর্থ: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
}


// Fetch suppliers and customers
$suppliers = $conn->query("SELECT * FROM parties WHERE type='supplier'");
$customers = $conn->query("SELECT * FROM parties WHERE type='customer'");
?>

<h2>পার্টি তালিকা</h2>
<div class="mb-3">
    <button class="btn btn-primary" onclick="showAddPartyPopup()">নতুন পার্টি যোগ করুন</button>
</div>

<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#suppliers">সরবরাহকারী</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#customers">গ্রাহক</a>
    </li>
</ul>

<div class="tab-content">
    <!-- Suppliers -->
    <div class="tab-pane fade show active" id="suppliers">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>সরবরাহকারী</th>
                    <th>মোট পাওনা</th>
                    <th>আজকের পাওনা</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($supplier = $suppliers->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($supplier['name']); ?></td>
                        <td>৳<?php echo number_format($supplier['due_amount'], 2); ?></td>
                        <td>৳0.00</td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <!-- Customers -->
    <div class="tab-pane fade" id="customers">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>গ্রাহক</th>
                    <th>মোট পাওনা</th>
                    <th>আজকের পাওনা</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($customer = $customers->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($customer['name']); ?></td>
                        <td>৳<?php echo number_format($customer['due_amount'], 2); ?></td>
                        <td>৳0.00</td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>