<?php
require_once '../db.php';

if (isset($_POST['field_name']) && trim($_POST['field_name']) !== '') {
    $field = trim($_POST['field_name']);
    $stmt = $conn->prepare("INSERT INTO cost_fields (name) VALUES (?)");
    $stmt->bind_param("s", $field);
    $stmt->execute();
}

header("Location: ../index.php?page=cost");
exit;
