<?php
require_once BASE_PATH . 'db.php';

// Fetch products for the product filter
$products = $conn->query("SELECT * FROM products");

// Apply filters
$search_product = isset($_GET['search_product']) ? $conn->real_escape_string($_GET['search_product']) : '';
$type_filter = isset($_GET['type_filter']) ? $_GET['type_filter'] : 'all';
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : '';

$where_clause = [];
if ($search_product) {
    $where_clause[] = "p.name LIKE '%$search_product%'";
}
if ($type_filter !== 'all') {
    $where_clause[] = "st.type = '$type_filter'";
}
if ($date_filter) {
    $where_clause[] = "st.date = '$date_filter'";
}
$where_sql = $where_clause ? "WHERE " . implode(" AND ", $where_clause) : "";

// Fetch stock transactions
$transactions = $conn->query("SELECT st.id, st.invoice_no, st.type, party.name as party_name, 
                              COUNT(DISTINCT st.product_id) as total_products, 
                              SUM(st.total_price) as total_price, 
                              st.paid_amount, st.due_amount, st.date 
                              FROM stock_transactions st 
                              LEFT JOIN parties party ON st.party_id = party.id 
                              JOIN products p ON st.product_id = p.id 
                              $where_sql 
                              GROUP BY st.id 
                              ORDER BY st.date DESC");
?>

<h2>Stock History</h2>
<div class="row mb-3">
    <div class="col-md-3">
        <input type="text" class="form-control" id="searchProduct" placeholder="Search Product" value="<?php echo htmlspecialchars($search_product); ?>" oninput="applyFilters()">
    </div>
    <div class="col-md-3">
        <select class="form-control" id="typeFilter" onchange="applyFilters()">
            <option value="all" <?php echo $type_filter == 'all' ? 'selected' : ''; ?>>All Products</option>
            <option value="in" <?php echo $type_filter == 'in' ? 'selected' : ''; ?>>Stock In</option>
            <option value="out" <?php echo $type_filter == 'out' ? 'selected' : ''; ?>>Stock Out</option>
        </select>
    </div>
    <div class="col-md-3">
        <input type="date" class="form-control" id="dateFilter" value="<?php echo htmlspecialchars($date_filter); ?>" onchange="applyFilters()">
    </div>
</div>
<div class="card">
    <div class="card-header">Order List</div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Type</th>
                    <th>Party</th>
                    <th>Total Products</th>
                    <th>Price</th>
                    <th>Paid Amount</th>
                    <th>Due Amount</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($transaction = $transactions->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($transaction['invoice_no'] ?? 'N/A'); ?></td>
                        <td><?php echo $transaction['type'] == 'in' ? 'Stock In' : 'Stock Out'; ?></td>
                        <td><?php echo htmlspecialchars($transaction['party_name'] ?? 'N/A'); ?></td>
                        <td><?php echo $transaction['total_products']; ?></td>
                        <td>৳<?php echo number_format($transaction['total_price'], 2); ?></td>
                        <td>৳<?php echo number_format($transaction['paid_amount'], 2); ?></td>
                        <td>৳<?php echo number_format($transaction['due_amount'], 2); ?></td>
                        <td><?php echo date('d M Y', strtotime($transaction['date'])); ?></td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="showTransactionDetails(<?php echo $transaction['id']; ?>)">Details</button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function applyFilters() {
    const searchProduct = document.getElementById('searchProduct').value;
    const typeFilter = document.getElementById('typeFilter').value;
    const dateFilter = document.getElementById('dateFilter').value;

    let url = '?page=stock_history';
    if (searchProduct) url += `&search_product=${encodeURIComponent(searchProduct)}`;
    if (typeFilter !== 'all') url += `&type_filter=${typeFilter}`;
    if (dateFilter) url += `&date_filter=${dateFilter}`;

    window.location.href = url;
}

function showTransactionDetails(transactionId) {
    // Fetch transaction details via AJAX
    fetch(`?page=stock_history&action=get_transaction_details&id=${transactionId}`)
        .then(response => response.json())
        .then(data => {
            let body = `
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            data.items.forEach(item => {
                body += `
                    <tr>
                        <td>${item.product_name}</td>
                        <td>${item.quantity}</td>
                        <td>৳${parseFloat(item.unit_price).toFixed(2)}</td>
                        <td>৳${parseFloat(item.total_price).toFixed(2)}</td>
                    </tr>
                `;
            });
            body += `
                    </tbody>
                </table>
                <div>Invoice No: ${data.invoice_no || 'N/A'}</div>
                <div>Party: ${data.party_name || 'N/A'}</div>
                <div>Total Price: ৳${parseFloat(data.total_price).toFixed(2)}</div>
                <div>Paid Amount: ৳${parseFloat(data.paid_amount).toFixed(2)}</div>
                <div>Due Amount: ৳${parseFloat(data.due_amount).toFixed(2)}</div>
                <div>Chalan Cost: ৳${parseFloat(data.chalan_cost).toFixed(2)}</div>
                <div>Date: ${new Date(data.date).toLocaleDateString()}</div>
            `;
            const footer = `
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            `;
            showModal('Transaction Details', body, footer);
        });
}
</script>

<?php
// Handle AJAX request for transaction details
if (isset($_GET['action']) && $_GET['action'] === 'get_transaction_details') {
    $id = (int)$_GET['id'];
    $transaction = $conn->query("SELECT st.*, party.name as party_name 
                                 FROM stock_transactions st 
                                 LEFT JOIN parties party ON st.party_id = party.id 
                                 WHERE st.id = $id")->fetch_assoc();
    
    $items = $conn->query("SELECT st.*, p.name as product_name 
                           FROM stock_transactions st 
                           JOIN products p ON st.product_id = p.id 
                           WHERE st.id = $id");
    $items_array = [];
    while ($item = $items->fetch_assoc()) {
        $items_array[] = [
            'product_name' => $item['product_name'],
            'quantity' => $item['quantity'],
            'unit_price' => $item['unit_price'],
            'total_price' => $item['total_price']
        ];
    }

    $response = [
        'invoice_no' => $transaction['invoice_no'],
        'party_name' => $transaction['party_name'],
        'total_price' => $transaction['total_price'],
        'paid_amount' => $transaction['paid_amount'],
        'due_amount' => $transaction['due_amount'],
        'chalan_cost' => $transaction['chalan_cost'],
        'date' => $transaction['date'],
        'items' => $items_array
    ];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>