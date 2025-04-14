function showModal(title, body, footer) {
    document.getElementById('modalTitle').innerText = title;
    document.getElementById('modalBody').innerHTML = body;
    document.getElementById('modalFooter').innerHTML = footer;
    new bootstrap.Modal(document.getElementById('genericModal')).show();
}

function validateForm(formId) {
    const form = document.getElementById(formId);
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;

    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.classList.add('is-invalid');
        } else {
            field.classList.remove('is-invalid');
        }
    });

    return isValid;
}

function submitForm(formId, action, redirect) {
    if (!validateForm(formId)) {
        alert('অনুগ্রহ করে সব প্রয়োজনীয় ক্ষেত্র পূরণ করুন!');
        return;
    }

    const form = document.getElementById(formId);
    const formData = new FormData(form);
    formData.append('action', action);

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    }).then(response => {
        if (response.ok) {
            window.location.href = redirect;
        } else {
            alert('একটি ত্রুটি হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।');
        }
    }).catch(error => {
        console.error('Error:', error);
        alert('একটি ত্রুটি হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।');
    });
}

// Party Functions
function showAddPartyPopup() {
    const body = `
        <form id="addPartyForm">
            <div class="mb-3">
                <label>পার্টির ধরণ <span class="text-danger">*</span></label>
                <select class="form-control" name="party_type" required>
                    <option value="supplier">সরবরাহকারী</option>
                    <option value="customer">গ্রাহক</option>
                </select>
            </div>
            <div class="mb-3">
                <label>পার্টির নাম <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="party_name" required>
            </div>
            <div class="mb-3">
                <label>মোবাইল নম্বর</label>
                <input type="text" class="form-control" name="mobile_number">
            </div>
            <div class="mb-3">
                <label>ঠিকানা</label>
                <input type="text" class="form-control" name="address">
            </div>
            <div class="mb-3">
                <label>বাকি পরিমাণ</label>
                <input type="number" step="0.01" class="form-control" name="due_amount" value="0">
            </div>
        </form>
    `;
    const footer = `
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বন্ধ করুন</button>
        <button type="button" class="btn btn-primary" onclick="submitForm('addPartyForm', 'add_party', '?page=party_list')">সংরক্ষণ করুন</button>
    `;
    showModal('নতুন পার্টি যোগ করুন', body, footer);
}

// Product Functions
function showAddProductPopup() {
    const body = `
        <form id="addProductForm">
            <div class="mb-3">
                <label>পণ্যের নাম <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="product_name" required>
            </div>
            <div class="mb-3">
                <label>পণ্যের কোড</label>
                <input type="text" class="form-control" name="product_code">
            </div>
            <div class="mb-3">
                <label>ক্রয় মূল্য <span class="text-danger">*</span></label>
                <input type="number" step="0.01" class="form-control" name="buy_rate" required>
            </div>
            <div class="mb-3">
                <label>বিক্রয় মূল্য <span class="text-danger">*</span></label>
                <input type="number" step="0.01" class="form-control" name="sell_rate" required>
            </div>
        </form>
    `;
    const footer = `
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বন্ধ করুন</button>
        <button type="button" class="btn btn-primary" onclick="submitForm('addProductForm', 'add_product', '?page=product_list')">সংরক্ষণ করুন</button>
    `;
    showModal('নতুন পণ্য যোগ করুন', body, footer);
}

// Cost Functions
function showFieldOfCostPopup() {
    const body = `
        <form id="fieldOfCostForm">
            <div class="mb-3">
                <label>খরচের ক্ষেত্র <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="cost_field" required>
            </div>
        </form>
    `;
    const footer = `
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বন্ধ করুন</button>
        <button type="button" class="btn btn-primary" onclick="submitForm('fieldOfCostForm', 'add_cost_field', '?page=cost')">সংরক্ষণ করুন</button>
    `;
    showModal('খরচের ক্ষেত্র যোগ করুন', body, footer);
}

function showAddNewCostPopup() {
    const body = `
        <form id="addCostForm">
            <div class="mb-3">
                <label>তারিখ <span class="text-danger">*</span></label>
                <input type="date" class="form-control" name="cost_date" value="${new Date().toISOString().split('T')[0]}" required>
            </div>
            <div class="mb-3">
                <label>খরচের ক্ষেত্র <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="cost_field" required>
            </div>
            <div class="mb-3">
                <label>পরিমাণ <span class="text-danger">*</span></label>
                <input type="number" step="0.01" class="form-control" name="cost_amount" required>
            </div>
            <div class="mb-3">
                <label>বিস্তারিত</label>
                <input type="text" class="form-control" name="cost_details">
            </div>
            <div class="mb-3">
                <label>রেফারেন্স</label>
                <input type="text" class="form-control" name="cost_reference">
            </div>
        </form>
    `;
    const footer = `
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বন্ধ করুন</button>
        <button type="button" class="btn btn-primary" onclick="submitForm('addCostForm', 'add_cost', '?page=cost')">সংরক্ষণ করুন</button>
    `;
    showModal('নতুন খরচ যোগ করুন', body, footer);
}

// Bank Functions
function showAccountsPopup() {
    const body = `
        <form id="addAccountForm">
            <div class="mb-3">
                <label>ব্যাংকের নাম <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="bank_name" required>
            </div>
        </form>
    `;
    const footer = `
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বন্ধ করুন</button>
        <button type="button" class="btn btn-primary" onclick="submitForm('addAccountForm', 'add_account', '?page=bank')">সংরক্ষণ করুন</button>
    `;
    showModal('নতুন ব্যাংক অ্যাকাউন্ট যোগ করুন', body, footer);
}

function showNewTransactionPopup() {
    const body = `
        <form id="addTransactionForm">
            <div class="mb-3">
                <label>অ্যাকাউন্ট <span class="text-danger">*</span></label>
                <select class="form-control" name="transaction_account" required>
                    <?php
                    $accounts = $conn->query("SELECT * FROM bank_accounts");
                    while ($account = $accounts->fetch_assoc()) {
                        echo "<option value='{$account['id']}'>" . htmlspecialchars($account['name']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label>লেনদেনের ধরণ <span class="text-danger">*</span></label>
                <select class="form-control" name="transaction_type" required>
                    <option value="Deposit">জমা</option>
                    <option value="Withdraw">উত্তোলন</option>
                </select>
            </div>
            <div class="mb-3">
                <label>পরিমাণ <span class="text-danger">*</span></label>
                <input type="number" step="0.01" class="form-control" name="transaction_amount" required>
            </div>
            <div class="mb-3">
                <label>বিস্তারিত</label>
                <input type="text" class="form-control" name="transaction_details">
            </div>
            <div class="mb-3">
                <label>রেফারেন্স</label>
                <input type="text" class="form-control" name="transaction_reference">
            </div>
            <div class="mb-3">
                <label>তারিখ <span class="text-danger">*</span></label>
                <input type="date" class="form-control" name="transaction_date" value="${new Date().toISOString().split('T')[0]}" required>
            </div>
        </form>
    `;
    const footer = `
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বন্ধ করুন</button>
        <button type="button" class="btn btn-primary" onclick="submitForm('addTransactionForm', 'add_transaction', '?page=bank')">সংরক্ষণ করুন</button>
    `;
    showModal('নতুন লেনদেন যোগ করুন', body, footer);
}

// Invest & Loan Functions
function showInvestPopup() {
    const body = `
        <form id="addInvestForm">
            <div class="mb-3">
                <label>পরিমাণ <span class="text-danger">*</span></label>
                <input type="number" step="0.01" class="form-control" name="invest_amount" required>
            </div>
            <div class="mb-3">
                <label>নোট</label>
                <input type="text" class="form-control" name="invest_note">
            </div>
            <div class="mb-3">
                <label>তারিখ <span class="text-danger">*</span></label>
                <input type="date" class="form-control" name="invest_date" value="${new Date().toISOString().split('T')[0]}" required>
            </div>
        </form>
    `;
    const footer = `
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বন্ধ করুন</button>
        <button type="button" class="btn btn-primary" onclick="submitForm('addInvestForm', 'add_investment', '?page=invest_loan')">সংরক্ষণ করুন</button>
    `;
    showModal('নতুন ইনভেস্টমেন্ট যোগ করুন', body, footer);
}

function showLoanPopup() {
    const body = `
        <form id="addLoanForm">
            <div class="mb-3">
                <label>পরিমাণ <span class="text-danger">*</span></label>
                <input type="number" step="0.01" class="form-control" name="loan_amount" required>
            </div>
            <div class="mb-3">
                <label>নোট</label>
                <input type="text" class="form-control" name="loan_note">
            </div>
            <div class="mb-3">
                <label>তারিখ <span class="text-danger">*</span></label>
                <input type="date" class="form-control" name="loan_date" value="${new Date().toISOString().split('T')[0]}" required>
            </div>
        </form>
    `;
    const footer = `
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বন্ধ করুন</button>
        <button type="button" class="btn btn-primary" onclick="submitForm('addLoanForm', 'add_loan', '?page=invest_loan')">সংরক্ষণ করুন</button>
    `;
    showModal('নতুন লোন/উত্তোলন যোগ করুন', body, footer);
}

// Asset Functions
function showBuyAssetPopup() {
    const body = `
        <form id="buyAssetForm">
            <div class="mb-3">
                <label>সম্পদের নাম <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="asset_name" required>
            </div>
            <div class="mb-3">
                <label>পরিমাণ <span class="text-danger">*</span></label>
                <input type="number" step="0.01" class="form-control" name="asset_amount" required>
            </div>
            <div class="mb-3">
                <label>নগদ থেকে কেনা</label>
                <input type="checkbox" name="asset_from_cash">
            </div>
            <div class="mb-3">
                <label>তারিখ <span class="text-danger">*</span></label>
                <input type="date" class="form-control" name="asset_date" value="${new Date().toISOString().split('T')[0]}" required>
            </div>
        </form>
    `;
    const footer = `
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বন্ধ করুন</button>
        <button type="button" class="btn btn-primary" onclick="submitForm('buyAssetForm', 'add_asset', '?page=asset')">সংরক্ষণ করুন</button>
    `;
    showModal('নতুন সম্পদ কিনুন', body, footer);
}