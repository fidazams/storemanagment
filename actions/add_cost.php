<?php
require_once '../db.php';

$field = $_POST['field'];
$amount = $_POST['amount'];
$details = $_POST['details'] ?? '';
$reference = $_POST['reference'] ?? '';
$date = $_POST['date'];

$stmt = $conn->prepare("INSERT INTO costs (field, amount, details, reference, date) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sdsss", $field, $amount, $details, $reference, $date);
$stmt->execute();

header("Location: ../index.php?page=cost");
exit;
