<?php
session_start();
require_once 'connect.php';

// Check if the user is logged in and is a supplier
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'supplier') {
    header('Location: login.php');
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

            // Display success message with JavaScript alert and reload page to update the table
            echo "<script>
                alert('Price updated successfully and notification sent to Telegram.');
                window.location.reload();
            </script>";
        } else {
            echo "<script>
                alert('No price change detected. No notification sent.');
            </script>";
        }
    } else {
        echo "<script>
            alert('Product not found.');
        </script>";
    }
}

// Query to fetch products supplied by this supplier
$supplierId = $_SESSION['supplier_id']; // assuming this is set in the session
$stmt = $con->prepare("SELECT * FROM `product` WHERE supplier_id = ?");
$stmt->bind_param("i", $supplierId);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Your existing HTML head content -->
</head>
<body>
    <!-- Your existing HTML content for the dashboard -->

    <!-- Products Table -->
    <table>
        <thead>
            <tr>
                <th>Product ID</th>
                <th>Product Name</th>
                <th>Price per Unit</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['product_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['price_per_unit']); ?></td>
                    <td>
                        <form method="POST" action="">
                            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($row['product_id']); ?>">
                            <input type="number" name="price_per_unit" placeholder="New Price" required>
                            <button type="submit" class="btn btn-primary">Update Price</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
