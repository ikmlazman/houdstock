<?php
session_start();

// Ensure the user is an admin or employee
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'employee')) {
    header('Location: login1.php');
    exit();
}

require_once 'connect.php';

// Check if the form has been submitted
if (isset($_POST['action']) && isset($_POST['quantity'])) {
    $action = $_POST['action'];
    $quantities = $_POST['quantity']; // Array of product quantities

    // Initialize flag to check if there are errors
    $error = false;
    
    // Iterate through each product and update quantities
    foreach ($quantities as $product_id => $quantity) {
        // Fetch the current stock for the product
        $sql = "SELECT quantity_in_stock, product_name FROM product WHERE product_id = $product_id";
        $result = mysqli_query($con, $sql);
        
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $current_stock = $row['quantity_in_stock'];
            $product_name = $row['product_name'];
            
            // Determine the new stock based on action
            if ($action == 'add') {
                $new_stock = $current_stock + $quantity;
            } elseif ($action == 'deduct') {
                $new_stock = $current_stock - $quantity;
                if ($new_stock < 0) {
                    echo "Error: Cannot deduct more than available stock for product: $product_name (Product ID: $product_id).<br>";
                    $error = true;
                    break;
                }
            } else {
                echo "Invalid action.<br>";
                $error = true;
                break;
            }
            
            // Update the stock in the database
            $update_sql = "UPDATE product SET quantity_in_stock = $new_stock WHERE product_id = $product_id";
            if (!mysqli_query($con, $update_sql)) {
                echo "Error updating stock for product: $product_name (Product ID: $product_id).<br>";
                $error = true;
                break;
            }

            // Send Telegram notification (optional)
            $message = "Stock for product '$product_name' has been updated. New stock: $new_stock.";
            sendTelegramNotification($message);
        } else {
            echo "Error fetching data for product ID: $product_id.<br>";
            $error = true;
            break;
        }
    }

    // Redirect back to inventory management page if no errors
    if (!$error) {
        header('Location: inventory_management.php');
        exit();
    }
} else {
    echo "No action or quantity provided.<br>";
}

// Function to send Telegram notifications
function sendTelegramNotification($message) {
    $token = '7557012745:AAEOy-HxoeKEc0i8VqdF0T6lkTSPYVjdC_A'; // Your bot token
    $chat_id = '5520559929'; // Your numeric chat ID

    // Create the URL for the API request
    $url = "https://api.telegram.org/bot$token/sendMessage?chat_id=$chat_id&text=" . urlencode($message);

    // Send request to Telegram API
    $response = file_get_contents($url);

    // Check for response
    if ($response === FALSE) {
        echo "Error sending message.<br>";
    }
}
?>
