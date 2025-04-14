<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "store_management";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create tables
$conn->query("CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    code VARCHAR(50),
    stock INT,
    buy_rate DECIMAL(10,2),
    sell_rate DECIMAL(10,2)
)");

$conn->query("CREATE TABLE IF NOT EXISTS stock_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(10),
    product_id INT,
    quantity INT,
    unit_price DECIMAL(10,2),
    total_price DECIMAL(10,2),
    party_id INT,
    invoice_no VARCHAR(50),
    date DATE,
    paid_amount DECIMAL(10,2),
    due_amount DECIMAL(10,2),
    chalan_cost DECIMAL(10,2)
)");

$conn->query("CREATE TABLE IF NOT EXISTS parties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(20),
    name VARCHAR(255),
    phone VARCHAR(20),
    address TEXT,
    due_amount DECIMAL(10,2)
)");

$conn->query("CREATE TABLE IF NOT EXISTS costs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    field VARCHAR(50),
    amount DECIMAL(10,2),
    details TEXT,
    reference VARCHAR(50),
    date DATE
)");

$conn->query("CREATE TABLE IF NOT EXISTS bank_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    balance DECIMAL(10,2)
)");

$conn->query("CREATE TABLE IF NOT EXISTS bank_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_id INT,
    type VARCHAR(20),
    amount DECIMAL(10,2),
    details TEXT,
    reference VARCHAR(50),
    date DATE
)");

$conn->query("CREATE TABLE IF NOT EXISTS investments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(20),
    amount DECIMAL(10,2),
    note TEXT,
    date DATE
)");

$conn->query("CREATE TABLE IF NOT EXISTS assets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    amount DECIMAL(10,2),
    from_cash BOOLEAN,
    date DATE
)");
?>