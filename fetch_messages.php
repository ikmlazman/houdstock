<?php
// fetch_messages.php
session_start();
require_once 'connect.php';

$sender = $_SESSION['username']; // Get the admin's username from the session
$receiver = 'admin'; // Messages for the admin

// Fetch all messages from the database
$sql = "SELECT * FROM messages WHERE receiver = ? ORDER BY created_at ASC";
$stmt = $con->prepare($sql);
$stmt->bind_param('s', $receiver);
$stmt->execute();
$result = $stmt->get_result();

// Fetch messages and return them as JSON
$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = [
        'sender' => $row['sender'],
        'message' => $row['message'],
        'created_at' => $row['created_at']
    ];
}

echo json_encode(['messages' => $messages]);
?>
