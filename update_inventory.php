<?php
session_start();
require_once 'connect.php';

// Check if user is logged in
if (!isset($_SESSION['role'])) {
    header('Location: login1.php');
    exit();
}

// Define the Telegram notification function
function sendTelegramNotification($message) {
    $token = '7557012745:AAEOy-HxoeKEc0i8VqdF0T6lkTSPYVjdC_A'; // Your bot token
    $chat_id = '5520559929'; // Your numeric chat ID

    // Create the URL for the API request
    $url = "https://api.telegram.org/bot$token/sendMessage?chat_id=$chat_id&text=" . urlencode($message);

    // Send request to Telegram API
    $response = file_get_contents($url);

    // Check for response
    if ($response === FALSE) {
        echo "Error sending message.";
    }
}

// Function to check stock levels and send appropriate notification
function checkStockAndNotify($product_name, $quantity_in_stock, $supplier_name) {
    if ($quantity_in_stock > 10) {
        $message = "Product: $product_name (Supplier: $supplier_name) - In Stock: Sufficient stock available.";
    } elseif ($quantity_in_stock >= 1 && $quantity_in_stock <= 10) {
        $message = "Product: $product_name (Supplier: $supplier_name) - Low Stock: Only $quantity_in_stock items left!";
    } else {
        $message = "Product: $product_name (Supplier: $supplier_name) - Out of Stock: This item is currently unavailable!";
    }

    // Send the notification
    sendTelegramNotification($message);
}

// Check if product ID and quantity are provided
if (isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $product_id = $_POST['product_id'];
    $quantity = (int)$_POST['quantity']; // The quantity entered by the user

    // Fetch the current product quantity, name, and supplier name using a prepared statement
    $stmt = $con->prepare("
        SELECT 
            product.product_name, 
            product.quantity_in_stock, 
            supplier.supplier_name 
        FROM product 
        JOIN supplier ON product.supplier_id = supplier.supplier_id 
        WHERE product.product_id = ?
    ");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    // Get the quantity, product name, and supplier name
    $currentQuantity = $product['quantity_in_stock'];
    $productName = $product['product_name'];
    $supplierName = $product['supplier_name'];

    // Determine whether to add or deduct quantity
    if (isset($_POST['add'])) {
        $newQuantity = $currentQuantity + $quantity; // Add specified quantity
        $addedStockMessage = "Stock Added: $productName now has a new total of $newQuantity items (Supplier: $supplierName).";
        sendTelegramNotification($addedStockMessage); // Send added stock notification
    } elseif (isset($_POST['deduct'])) {
        $newQuantity = max(0, $currentQuantity - $quantity); // Deduct specified quantity, prevent negative values
    }

    // Update the product quantity using a prepared statement
    $updateStmt = $con->prepare("UPDATE product SET quantity_in_stock = ? WHERE product_id = ?");
    $updateStmt->bind_param("ii", $newQuantity, $product_id);
    $updateStmt->execute();

    // Trigger the notification based on the new quantity
    checkStockAndNotify($productName, $newQuantity, $supplierName);

    // Check user role and redirect accordingly
    if ($_SESSION['role'] == 'employee') {
        // Redirect to employee_dashboard.php if the user is an employee
        header('Location: employee_dashboard.php');
    } elseif ($_SESSION['role'] == 'admin') {
        // Redirect to admin_dashboard.php if the user is an admin
        header('Location: admin_dashboard.php');
    }
    exit();
} else {
    // If no ID or quantity is provided, redirect to the appropriate dashboard
    if ($_SESSION['role'] == 'employee') {
        header('Location: employee_dashboard.php');
    } elseif ($_SESSION['role'] == 'admin') {
        header('Location: admin_dashboard.php');
    }
    exit();
}
?>