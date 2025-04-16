<?php
require_once BASE_PATH . 'db.php';

// Fetch all products
$search_query = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$where_clause = $search_query ? "WHERE name LIKE '%$search_query%'" : '';
$products = $conn->query("SELECT * FROM products $where_clause");

// Fetch suppliers
$suppliers = $conn->query("SELECT * FROM parties WHERE type='supplier'");

// Handle stock in form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $invoice_no = $_POST['invoice_no'] ?? null;
    $product_ids = $_POST['product_id'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    $unit_prices = $_POST['unit_price'] ?? [];
    $party_id = $_POST['party_id'];
    $paid_amount = $_POST['paid_amount'] ?? 0;

    $total_price = 0;
    foreach ($quantities as $i => $quantity) {
        if ($quantity > 0) {
            $total_price += $quantity * $unit_prices[$i];
        }
    }
    $due_amount = $total_price - $paid_amount;

    foreach ($product_ids as $i => $product_id) {
        if ($quantities[$i] > 0) {
            $total = $quantities[$i] * $unit_prices[$i];
            $stmt = $conn->prepare("INSERT INTO stock_transactions (type, product_id, quantity, unit_price, total_price, party_id, invoice_no, date, paid_amount, due_amount) VALUES ('in', ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iiddisssd", $product_id, $quantities[$i], $unit_prices[$i], $total, $party_id, $invoice_no, $date, $paid_amount, $due_amount);
            $stmt->execute();
            $stmt->close();

            $conn->query("UPDATE products SET stock = stock + $quantities[$i] WHERE id = $product_id");
        }
    }

    $conn->query("UPDATE parties SET due_amount = due_amount + $due_amount WHERE id = $party_id");

    header("Location: ?page=stock_dashboard");
    exit;
}
?>

<h2>Stock In</h2>
<form method="POST">
    <div class="row">
        <!-- Left Column -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Available Products</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label>Date</label>
                        <input type="date" class="form-control" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" placeholder="Search Products" id="productSearch" oninput="searchProducts()">
                        <button type="button" class="btn btn-primary" onclick="searchProducts()">Search</button>
                    </div>
                    <a href="?page=add_product" class="btn btn-sm btn-primary mb-3">Add Product</a>
                    <table class="table table-bordered" id="productTable">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAll" onclick="toggleSelectAll()"></th>
                                <th>Name</th>
                                <th>Purchase Rate</th>
                                <th>Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($product = $products->fetch_assoc()) { ?>
                                <tr>
                                    <td><input type="checkbox" class="product-checkbox" value="<?= $product['id'] ?>" data-name="<?= htmlspecialchars($product['name']) ?>" data-rate="<?= $product['buy_rate'] ?>" onchange="updateSelectedProducts()"></td>
                                    <td><?= htmlspecialchars($product['name']) ?></td>
                                    <td>৳<?= number_format($product['buy_rate'], 2) ?></td>
                                    <td><?= $product['stock'] ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- Right Column -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Selected Products</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label>Invoice No (Optional)</label>
                        <input type="text" class="form-control" name="invoice_no">
                    </div>
                    <table class="table table-bordered" id="selectedProductsTable">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="selectedProducts"></tbody>
                    </table>
                    <hr>
                    <div>Total Price: ৳<span id="totalPrice">0.00</span></div>
                    <div class="mt-3">
                        <label>Supplier</label>
                        <select class="form-control" name="party_id" required>
                            <option value="">Select Supplier</option>
                            <?php while ($supplier = $suppliers->fetch_assoc()) { ?>
                                <option value="<?= $supplier['id'] ?>"><?= htmlspecialchars($supplier['name']) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="mt-3">
                        <label>Paid Amount</label>
                        <input type="number" class="form-control" name="paid_amount" value="0" min="0" oninput="calculateDue()">
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">Present Due: ৳<span id="presentDue">0.00</span></div>
                        <div class="col-md-6">Total Due: ৳<span id="totalDue">0.00</span></div>
                    </div>
                    <button type="submit" class="btn btn-success mt-3">Save</button>
                    <a href="?page=stock_dashboard" class="btn btn-secondary mt-3">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
function toggleSelectAll() {
    const checkboxes = document.querySelectorAll('.product-checkbox');
    const isChecked = document.getElementById('selectAll').checked;
    checkboxes.forEach(cb => {
        cb.checked = isChecked;
    });
    updateSelectedProducts();
}

function updateSelectedProducts() {
    const selectedProducts = document.getElementById('selectedProducts');
    selectedProducts.innerHTML = '';
    const checkboxes = document.querySelectorAll('.product-checkbox:checked');

    checkboxes.forEach(cb => {
        const name = cb.dataset.name;
        const rate = parseFloat(cb.dataset.rate);
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${name}</td>
            <td><input type="number" class="form-control quantity" name="quantity[]" value="0" min="0" oninput="updateTotalPrice()"></td>
            <td><input type="number" class="form-control unit-price" name="unit_price[]" value="${rate.toFixed(2)}" oninput="updateTotalPrice()"></td>
            <td class="total">৳0.00</td>
            <td><button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove(); updateTotalPrice();">X</button></td>
            <input type="hidden" name="product_id[]" value="${cb.value}">
        `;
        selectedProducts.appendChild(row);
    });

    updateTotalPrice();
}

function updateTotalPrice() {
    let total = 0;
    document.querySelectorAll('#selectedProducts tr').forEach(row => {
        const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
        const rate = parseFloat(row.querySelector('.unit-price').value) || 0;
        const totalCell = quantity * rate;
        row.querySelector('.total').innerText = '৳' + totalCell.toFixed(2);
        total += totalCell;
    });
    document.getElementById('totalPrice').innerText = total.toFixed(2);
    calculateDue();
}

function calculateDue() {
    const total = parseFloat(document.getElementById('totalPrice').innerText) || 0;
    const paid = parseFloat(document.querySelector('input[name="paid_amount"]').value) || 0;
    const due = total - paid;
    document.getElementById('presentDue').innerText = due.toFixed(2);
    document.getElementById('totalDue').innerText = due.toFixed(2);
}

function searchProducts() {
    const query = document.getElementById('productSearch').value;
    window.location.href = `?page=stock_in&search=${encodeURIComponent(query)}`;
}
</script>
