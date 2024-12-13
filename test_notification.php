<?php
session_start();
require_once 'connect.php';

// Check if user is logged in
if (!isset($_SESSION['role'])) {
    header('Location: login.php');
    exit();
}

// Define the Telegram notification function
function sendTelegramNotification($message) {
    $token = '7557012745:AAEOy-HxoeKEc0i8VqdF0T6lkTSPYVjdC_A'; // Your bot token
    $chat_id = '5599295520'; // Your numeric chat ID

    // Create the URL for the API request
    $url = "https://api.telegram.org/bot$token/sendMessage?chat_id=$chat_id&text=" . urlencode($message);

    // Send request to Telegram API
    $response = file_get_contents($url);

    // Check for response
    if ($response === FALSE) {
        echo "Error sending message.";
    }
}

// Function to check stock levels and send appropriate notification for a specific product
function checkStockAndNotify($product_id, $product_name, $quantity_in_stock) {
    if ($quantity_in_stock > 10) {
        $message = "Product: $product_name (ID $product_id) - In Stock: Sufficient stock available.";
    } elseif ($quantity_in_stock >= 4 && $quantity_in_stock <= 10) {
        $message = "Product: $product_name (ID $product_id) - Low Stock: Only $quantity_in_stock items left!";
    } elseif ($quantity_in_stock > 0 && $quantity_in_stock < 4) {
        $message = "Product: $product_name (ID $product_id) - Warning: Stock is very low, only $quantity_in_stock items remaining!";
    } else {
        $message = "Product: $product_name (ID $product_id) - Out of Stock: This item is currently unavailable!";
    }

    // Send the notification
    sendTelegramNotification($message);
}

// Check if product ID is provided
if (isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];

    // Fetch the current product quantity and name using a prepared statement
    $stmt = $con->prepare("SELECT product_name, quantity_in_stock FROM product WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    
    // Fetch the quantity and product name correctly from the product array
    if ($product) {
        $currentQuantity = $product['quantity_in_stock'];
        $productName = $product['product_name'];

        // Determine whether to add or deduct quantity
        if (isset($_POST['add'])) {
            $newQuantity = $currentQuantity + 1; // Add 1 to the quantity
            // Send a notification that stock was added
            $addedStockMessage = "Stock Added: Product $productName (ID $product_id) now has $newQuantity items.";
            sendTelegramNotification($addedStockMessage);
        } elseif (isset($_POST['deduct'])) {
            $newQuantity = max(0, $currentQuantity - 1); // Deduct 1, but prevent negative values
        }

        // Update the product quantity using a prepared statement
        $updateStmt = $con->prepare("UPDATE product SET quantity_in_stock = ? WHERE product_id = ?");
        $updateStmt->bind_param("ii", $newQuantity, $product_id);
        $updateStmt->execute();

        // Trigger the stock level notification based on the new quantity, product ID, and name
        checkStockAndNotify($product_id, $productName, $newQuantity);

        // Redirect back to the admin dashboard
        header('Location: admin_dashboard.php');
        exit();
    } else {
        // If the product is not found, redirect to admin dashboard
        header('Location: admin_dashboard.php');
        exit();
    }
} else {
    // If no product ID is provided, redirect to admin dashboard
    header('Location: admin_dashboard.php');
    exit();
}
?>
