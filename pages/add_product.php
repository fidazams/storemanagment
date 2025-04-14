<?php
require_once BASE_PATH . 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $names = $_POST['name'] ?? [];
    $codes = $_POST['code'] ?? [];
    $stocks = $_POST['stock'] ?? [];
    $buy_rates = $_POST['buy_rate'] ?? [];
    $sell_rates = $_POST['sell_rate'] ?? [];

    for ($i = 0; $i < count($names); $i++) {
        if (!empty($names[$i]) && !empty($codes[$i])) {
            $stock = !empty($stocks[$i]) ? (int)$stocks[$i] : 0;
            $buy_rate = !empty($buy_rates[$i]) ? (float)$buy_rates[$i] : 0.00;
            $sell_rate = !empty($sell_rates[$i]) ? (float)$sell_rates[$i] : 0.00;

            $stmt = $conn->prepare("INSERT INTO products (name, code, stock, buy_rate, sell_rate) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssidd", $names[$i], $codes[$i], $stock, $buy_rate, $sell_rate);
            $stmt->execute();
            $stmt->close();
        }
    }
    header("Location: ?page=product_list");
    exit;
}
?>

<h2>Add Product</h2>
<button class="btn btn-sm btn-primary float-end" onclick="addProductRow()">Add More Product</button>
<form method="POST" action="">
    <div id="productRows">
        <div class="row mb-3 product-row">
            <div class="col-md-2"><input type="text" class="form-control" placeholder="Name" name="name[]"></div>
            <div class="col-md-2"><input type="text" class="form-control" placeholder="Code" name="code[]"></div>
            <div class="col-md-2"><input type="number" class="form-control" placeholder="Stock" name="stock[]"></div>
            <div class="col-md-2"><input type="number" class="form-control" placeholder="Buy Rate" name="buy_rate[]"></div>
            <div class="col-md-2"><input type="number" class="form-control" placeholder="Sell Rate" name="sell_rate[]"></div>
            <div class="col-md-2"><button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.parentElement.remove()">X</button></div>
        </div>
    </div>
    <button type="submit" class="btn btn-success">Save</button>
    <a href="?page=product_list" class="btn btn-secondary">Cancel</a>
</form>