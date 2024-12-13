<?php
function sendTelegramNotification($supplier_name, $message) {
    // Replace with your supplier bot's token and chat ID
    $supplier_bot_token = '7314268770:AAGGj8oQgnGCob8icZV2u3Q3ZKarZtj5Azo'; 
    $supplier_chat_id = 'Supplier_OrderBot'; 

    // Create the URL for the API request
    $url = "https://api.telegram.org/bot$supplier_bot_token/sendMessage?chat_id=$supplier_chat_id&text=" . urlencode($message);

    // Send request to Telegram API
    $response = @file_get_contents($url); // Suppress warnings with '@'

    // Log the response
    if ($response === FALSE) {
        error_log("Error sending message to supplier. URL: $url");
    } else {
        $responseData = json_decode($response, true);
        if (!$responseData['ok']) {
            error_log("Error from Telegram: " . $responseData['description']);
        } else {
            echo "Message sent successfully to $supplier_name!";
        }
    }
}

// Test the notification
$supplier_name = "Test Supplier";
$message = "Hello $supplier_name, this is a test notification!";
sendTelegramNotification($supplier_name, $message);
?>
