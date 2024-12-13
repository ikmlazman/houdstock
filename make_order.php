<?php
session_start();
require_once 'connect.php';

// Check if the user is logged in
if (!isset($_SESSION['role'])) {
    header('Location: login.php');
    exit();
}

// Only proceed if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $supplier_name = 'Default Supplier'; // Replace with logic to set supplier if needed

    // Insert new order into orders table
    $sql = "INSERT INTO orders (product_id, supplier_name) VALUES (?, ?)";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("is", $product_id, $supplier_name);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        header("Location: manage_orders.php");
        exit();
    } else {
        echo "Failed to create order.";
    }
}
?>
