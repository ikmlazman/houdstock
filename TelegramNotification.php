<?php
function sendTelegramNotification($message) {
    $token = '7557012745:AAH-VVkbBpvkp0FDPzWZeQmsi2voLqg1I9M'; // Replace with your bot token
    $chat_id = 'WhatsAppNotiBot'; // Replace with your chat ID
    $url = "https://api.telegram.org/bot$token/sendMessage?chat_id=$chat_id&text=" . urlencode($message);
    $response = file_get_contents($url);
    
    if ($response === FALSE) {
        echo "Error sending message.";
    } else {
        echo "Message sent successfully!";
    }
}

// Test the notification
$message = "Test Notification: Your bot is working!";
sendTelegramNotification($message);
?>
