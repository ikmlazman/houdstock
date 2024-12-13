<?php
session_start();
require_once 'connect.php';

// Check if the user is logged in
if (!isset($_SESSION['role'])) {
    header('Location: login.php');
    exit();
}

// Only allow admin users to delete orders
if ($_SESSION['role'] !== 'admin') {
    echo "Access denied: You do not have permission to delete orders.";
    exit();
}

// Get the order ID from the URL
if (isset($_GET['id'])) {
    $order_id = $_GET['id'];

    // Fetch product_id for the Telegram notification
    $product_sql = "SELECT product_id FROM orders WHERE order_id = ?";
    $stmt = $con->prepare($product_sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $product_result = $stmt->get_result();
    $product = $product_result->fetch_assoc();

    if ($product) {
        $product_id = $product['product_id'];

        // Delete order from the database
        $delete_sql = "DELETE FROM orders WHERE order_id = ?";
        $delete_stmt = $con->prepare($delete_sql);
        $delete_stmt->bind_param("i", $order_id);
        $delete_stmt->execute();

        if ($delete_stmt->affected_rows > 0) {
            // Send notification to Telegram
            $message = "Order ID $order_id has been successfully deleted for Product ID $product_id.";
            sendTelegramNotification($message);
            header("Location: manage_orders.php");
            exit();
        } else {
            echo "Error deleting the order. Please try again.";
        }
    } else {
        echo "Order not found.";
    }
} else {
    echo "Invalid request: No order ID provided.";
}

// Function to send Telegram notifications
function sendTelegramNotification($message) {
    $token = '7314268770:AAGGj8oQgnGCob8icZV2u3Q3ZKarZtj5Azo'; // Replace with your bot token
    $chat_id = '5520559929'; // Replace with your chat ID
    $url = "https://api.telegram.org/bot$token/sendMessage?chat_id=$chat_id&text=" . urlencode($message);

    // Send request to Telegram API
    $response = file_get_contents($url);
    if ($response === FALSE) {
        error_log("Error sending message to Telegram.");
    }
}
?>
