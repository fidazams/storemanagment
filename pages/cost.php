<?php
// Fetch cost fields
$fields = $conn->query("SELECT * FROM cost_fields ORDER BY name");

// Fetch grouped totals by field
$grouped = $conn->query("SELECT field, SUM(amount) as total FROM costs GROUP BY field");

// Fetch all cost transactions
$costs = $conn->query("SELECT * FROM costs ORDER BY date DESC");
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>খরচ তালিকা</h4>
    <div>
        <button class="btn btn-sm btn-primary me-2" onclick="showAddCostModal()">নতুন খরচ যোগ করুন</button>
        <button class="btn btn-sm btn-secondary" onclick="showAddFieldModal()">নতুন খরচের খাত</button>
    </div>
</div>

<div class="row mb-4">
    <?php while ($row = $grouped->fetch_assoc()): ?>
        <div class="col-md-3">
            <div class="card border-start border-primary shadow-sm mb-2">
                <div class="card-body">
                    <h6 class="card-title"><?= htmlspecialchars($row['field']) ?></h6>
                    <p class="card-text fw-bold text-primary">৳<?= number_format($row['total'], 2) ?></p>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<table class="table table-bordered table-sm table-striped">
    <thead>
        <tr>
            <th>তারিখ</th>
            <th>খাত</th>
            <th>টাকা</th>
            <th>বিস্তারিত</th>
            <th>রেফারেন্স</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $costs->fetch_assoc()): ?>
            <tr>
                <td><?= $row['date'] ?></td>
                <td><?= $row['field'] ?></td>
                <td>৳<?= number_format($row['amount'], 2) ?></td>
                <td><?= htmlspecialchars($row['details']) ?></td>
                <td><?= htmlspecialchars($row['reference']) ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<script>
function showAddCostModal() {
    const modalBody = `
        <form id="addCostForm" method="post" action="actions/add_cost.php">
            <div class="mb-2">
                <label>খরচের খাত</label>
                <select name="field" class="form-select" required>
                    <option value="">নির্বাচন করুন</option>
                    <?php $fields->data_seek(0); while ($f = $fields->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($f['name']) ?>"><?= htmlspecialchars($f['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-2">
                <label>পরিমাণ (৳)</label>
                <input type="number" name="amount" class="form-control" step="0.01" required>
            </div>
            <div class="mb-2">
                <label>বিস্তারিত</label>
                <textarea name="details" class="form-control"></textarea>
            </div>
            <div class="mb-2">
                <label>রেফারেন্স</label>
                <input type="text" name="reference" class="form-control">
            </div>
            <div class="mb-2">
                <label>তারিখ</label>
                <input type="date" name="date" class="form-control" required value="<?= date('Y-m-d') ?>">
            </div>
            <button type="submit" class="btn btn-primary">সাবমিট</button>
        </form>
    `;
    document.getElementById("modalTitle").innerText = "নতুন খরচ যোগ করুন";
    document.getElementById("modalBody").innerHTML = modalBody;
    new bootstrap.Modal(document.getElementById("genericModal")).show();
}

function showAddFieldModal() {
    const modalBody = `
        <form method="post" action="actions/add_field.php">
            <div class="mb-2">
                <label>নতুন খরচের খাত</label>
                <input type="text" name="field_name" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success">খাত যোগ করুন</button>
        </form>
    `;
    document.getElementById("modalTitle").innerText = "নতুন খরচের খাত";
    document.getElementById("modalBody").innerHTML = modalBody;
    new bootstrap.Modal(document.getElementById("genericModal")).show();
}
</script>
