<?php
require_once BASE_PATH . 'db.php';

// Fetch all products for the product list
$search_query = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$where_clause = $search_query ? "WHERE name LIKE '%$search_query%'" : '';
$products = $conn->query("SELECT * FROM products $where_clause");

// Fetch customers for the party selection
$customers = $conn->query("SELECT * FROM parties WHERE type='customer'");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $invoice_no = $_POST['invoice_no'] ?? null;
    $product_ids = $_POST['product_id'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    $unit_prices = $_POST['unit_price'] ?? [];
    $party_id = $_POST['party_id'];
    $paid_amount = $_POST['paid_amount'] ?? 0;
    $chalan_cost = $_POST['chalan_cost'] ?? 0;

    $total_price = 0;
    foreach ($quantities as $i => $quantity) {
        if ($quantity > 0) {
            $total_price += $quantity * $unit_prices[$i];
        }
    }
    $due_amount = $total_price - $paid_amount;

    // Insert stock transactions
    foreach ($product_ids as $i => $product_id) {
        if ($quantities[$i] > 0) {
            $total = $quantities[$i] * $unit_prices[$i];
            $stmt = $conn->prepare("INSERT INTO stock_transactions (type, product_id, quantity, unit_price, total_price, party_id, invoice_no, date, paid_amount, due_amount, chalan_cost) VALUES ('out', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iiddisssd", $product_id, $quantities[$i], $unit_prices[$i], $total, $party_id, $invoice_no, $date, $paid_amount, $due_amount, $chalan_cost);
            $stmt->execute();
            $stmt->close();

            // Update product stock
            $conn->query("UPDATE products SET stock = stock - $quantities[$i] WHERE id = $product_id");
        }
    }

    // Update party due amount
    $conn->query("UPDATE parties SET due_amount = due_amount + $due_amount WHERE id = $party_id");

    // Record chalan cost if any
    if ($chalan_cost > 0) {
        $stmt = $conn->prepare("INSERT INTO costs (field, amount, details, reference, date) VALUES ('Chalan', ?, 'Chalan cost from stock out', ?, ?)");
        $stmt->bind_param("dss", $chalan_cost, $invoice_no, $date);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: ?page=stock_dashboard");
    exit;
}
?>

<h2>Stock Out</h2>
<form method="POST" action="">
    <div class="row">
        <!-- Left Column: Product Selection -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Select Products</div>
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
                                <th>Product Name</th>
                                <th>Rate</th>
                                <th>Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($product = $products->fetch_assoc()) { ?>
                                <tr>
                                    <td><input type="checkbox" class="product-checkbox" value="<?php echo $product['id']; ?>" data-name="<?php echo htmlspecialchars($product['name']); ?>" data-rate="<?php echo $product['sell_rate']; ?>" onchange="updateSelectedProducts()"></td>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td>৳<?php echo number_format($product['sell_rate'], 2); ?></td>
                                    <td><?php echo $product['stock']; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- Right Column: Selected Products -->
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
                        <tbody id="selectedProducts">
                        </tbody>
                    </table>
                    <hr>
                    <div>Total Price: ৳<span id="totalPrice">0.00</span></div>
                    <div class="mt-3">
                        <label>Party</label>
                        <select class="form-control" name="party_id" required>
                            <option value="">Select Customer</option>
                            <?php while ($customer = $customers->fetch_assoc()) { ?>
                                <option value="<?php echo $customer['id']; ?>"><?php echo htmlspecialchars($customer['name']); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="mt-3">
                        <label>Paid Amount</label>
                        <input type="number" class="form-control" name="paid_amount" value="0" min="0" oninput="calculateDue()">
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-4">Previous Due: ৳0.00</div>
                        <div class="col-md-4">Present Due: ৳<span id="presentDue">0.00</span></div>
                        <div class="col-md-4">Total Due: ৳<span id="totalDue">0.00</span></div>
                    </div>
                    <div class="mt-3">
                        <label>Chalan Cost</label>
                        <input type="number" class="form-control" name="chalan_cost" value="0" min="0">
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
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.product-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
        updateSelectedProducts();
    });
}

function updateSelectedProducts() {
    const selectedProducts = document.getElementById('selectedProducts');
    selectedProducts.innerHTML = '';
    let totalPrice = 0;

    const checkboxes = document.querySelectorAll('.product-checkbox:checked');
    checkboxes.forEach((checkbox, index) => {
        const productId = checkbox.value;
        const name = checkbox.dataset.name;
        const rate = parseFloat(checkbox.dataset.rate);
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${name}</td>
            <td><input type="number" class="form-control quantity" name="quantity[]" value="0" min="0" oninput="updateTotalPrice()"></td>
            <td><input type="number" class="form-control unit-price" name="unit_price[]" value="${rate.toFixed(2)}" oninput="updateTotalPrice()"></td>
            <td class="total">৳0.00</td>
            <td><button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.parentElement.remove(); updateTotalPrice();">X</button></td>
            <input type="hidden" name="product_id[]" value="${productId}">
        `;
        selectedProducts.appendChild(row);
    });
    updateTotalPrice();
}

function updateTotalPrice() {
    let totalPrice = 0;
    document.querySelectorAll('#selectedProducts tr').forEach(row => {
        const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
        const unitPrice = parseFloat(row.querySelector('.unit-price').value) || 0;
        const total = quantity * unitPrice;
        row.querySelector('.total').innerText = '৳' + total.toFixed(2);
        totalPrice += total;
    });
    document.getElementById('totalPrice').innerText = totalPrice.toFixed(2);
    calculateDue();
}

function calculateDue() {
    const totalPrice = parseFloat(document.getElementById('totalPrice').innerText) || 0;
    const paidAmount = parseFloat(document.querySelector('input[name="paid_amount"]').value) || 0;
    const presentDue = totalPrice - paidAmount;
    document.getElementById('presentDue').innerText = presentDue.toFixed(2);
    document.getElementById('totalDue').innerText = presentDue.toFixed(2); // Assuming no previous due for simplicity
}

function searchProducts() {
    const query = document.getElementById('productSearch').value;
    window.location.href = `?page=stock_out&search=${encodeURIComponent(query)}`;
}
</script>