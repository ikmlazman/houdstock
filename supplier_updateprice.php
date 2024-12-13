<?php
session_start();
require_once 'connect.php';

// Check if the user is logged in and is a supplier
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'supplier') {
    header('Location: login1.php');
    exit();
}

// Telegram Bot Token and Chat ID
$botToken = '7576848535:AAGVA7luER6suIA5HjlAIgpZ_98KwHFnguw';
$chat_Id = '5520559929';

// Function to send a notification to Telegram
function sendTelegramNotification($message, $token, $chat_Id) {
    $url = "https://api.telegram.org/bot$token/sendMessage";
    $data = [
        'chat_id' => $chat_Id,
        'text' => $message
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        error_log('CURL error: ' . curl_error($ch));
        echo "<script>alert('Error sending notification to Telegram. Please check the logs for more details.');</script>";
    } else {
        $responseData = json_decode($response, true);
        if ($responseData['ok']) {
            echo "<script>alert('Notification sent: {$responseData['result']['text']}');</script>";
        } else {
            echo "<script>alert('Error from Telegram: {$responseData['description']}');</script>";
        }
    }
    curl_close($ch);
}


// Check if price update form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'], $_POST['price_per_unit'])) {
    $productId = $_POST['product_id'];
    $newPrice = $_POST['price_per_unit'];

    // Get current product price and name
    $stmt = $con->prepare("SELECT price_per_unit, product_name FROM product WHERE product_id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if ($product) {
        $currentPrice = $product['price_per_unit'];
        $productName = $product['product_name'];

        // Update price if it has changed
        if ($newPrice != $currentPrice) {
            $updateStmt = $con->prepare("UPDATE product SET price_per_unit = ? WHERE product_id = ?");
            $updateStmt->bind_param("di", $newPrice, $productId);
            $updateStmt->execute();

            // Prepare and send the notification
            $change = ($newPrice > $currentPrice) ? 'increased' : 'decreased';
            $message = "The price of '$productName' (ID: $productId) has been $change to $newPrice.";
            sendTelegramNotification($message, $botToken, $chat_Id);

            echo "<script>alert('Price updated successfully and notification sent to Telegram.');</script>";
        } else {
            echo "<script>alert('No price change detected. No notification sent.');</script>";
        }
    } else {
        echo "<script>alert('Product not found.');</script>";
    }
} else {
    echo "<script>alert('Invalid request.');</script>";
}
?>
