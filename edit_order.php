<?php
session_start();
require_once 'connect.php';

// Check if the user is logged in
if (!isset($_SESSION['role'])) {
    header('Location: login.php');
    exit();
}

// Check the user's role (admin or user)
$is_admin = $_SESSION['role'] == 'admin';

// Get supplier_id from URL or session (or any other source)
$supplier_id = isset($_GET['supplier_id']) ? $_GET['supplier_id'] : 1; // Default to 1 for testing

// Fetch the supplier name from the database
$sql = "SELECT supplier_name FROM supplier WHERE supplier_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $supplier_id);
$stmt->execute();
$stmt->bind_result($supplier_name);
$stmt->fetch();
$stmt->close();

// Function to send Telegram notifications
function sendTelegramNotification($message) {
    $supplier_bot_token = '7314268770:AAGGj8oQgnGCob8icZV2u3Q3ZKarZtj5Azo'; // Replace with your bot token
    $supplier_chat_id = '5520559929'; // Replace with your chat ID
    $url = "https://api.telegram.org/bot$supplier_bot_token/sendMessage?chat_id=$supplier_chat_id&text=" . urlencode($message);
    
    // Send request to Telegram API
    $response = file_get_contents($url);
    if ($response === FALSE) {
        error_log("Error sending message to Telegram.");
    }
}

// Attempt to fetch the order details if an ID is provided
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$order = null;

if ($order_id) {
    $order_sql = "SELECT * FROM orders WHERE order_id = ?";
    $stmt = $con->prepare($order_sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order_result = $stmt->get_result();

    if ($order_result->num_rows > 0) {
        $order = $order_result->fetch_assoc();
    }
}

// Handle form submission for editing the order
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $is_admin) {
    $order_id = $_POST['order_id'];
    $product_id = $_POST['product_id'];
    $supplier_id = $_POST['supplier_id'];  // Get supplier_id from the form
    $quantity_to_add = $_POST['quantity_to_add'];

    // Fetch current quantity in stock
    $stock_sql = "SELECT quantity_in_stock FROM product WHERE product_id = ?";
    $stmt = $con->prepare($stock_sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stock_result = $stmt->get_result();
    $current_stock = $stock_result->fetch_assoc()['quantity_in_stock'];

    // Calculate new quantity in stock
    $new_quantity_in_stock = $current_stock + $quantity_to_add;

    // Update the order details
    $update_order_sql = "UPDATE orders SET product_id = ?, supplier_id = ? WHERE order_id = ?";
    $update_stmt = $con->prepare($update_order_sql);
    $update_stmt->bind_param("iii", $product_id, $supplier_id, $order_id);
    $update_stmt->execute();

    // Update product quantity in stock
    $update_stock_sql = "UPDATE product SET quantity_in_stock = ? WHERE product_id = ?";
    $update_stock_stmt = $con->prepare($update_stock_sql);
    $update_stock_stmt->bind_param("ii", $new_quantity_in_stock, $product_id);
    $update_stock_stmt->execute();

    // Send notification to the supplier
    $notification_message = "Order Updated: Product ID $product_id has been added with $quantity_to_add units. New stock level: $new_quantity_in_stock.";
    sendTelegramNotification($notification_message);

    // Redirect to manage orders page after update
    header("Location: manage_orders.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Order</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            background: url('background.jpg') no-repeat center center/cover;
        }
        h1 {
            text-align: center;
            color: #fff;
        }
        .container {
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 8px;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Edit Order</h1>

    <?php if ($order): ?>
    <form action="edit_order.php" method="post">
        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['order_id']); ?>">
        <div class="form-group">
            <label for="product_id">Product ID:</label>
            <input type="number" class="form-control" id="product_id" name="product_id" value="<?php echo htmlspecialchars($order['product_id']); ?>" required>
        </div>
        <div class="form-group">
            <label for="supplier_id">Supplier:</label>
            <select class="form-control" id="supplier_id" name="supplier_id" required>
                <!-- Populate the supplier options dynamically -->
                <?php
                $supplier_sql = "SELECT supplier_id, supplier_name FROM supplier";
                $supplier_result = $con->query($supplier_sql);
                while ($row = $supplier_result->fetch_assoc()) {
                    echo "<option value='" . $row['supplier_id'] . "'"
                        . ($row['supplier_id'] == $order['supplier_id'] ? ' selected' : '') . ">"
                        . htmlspecialchars($row['supplier_name']) . "</option>";
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label for="quantity_to_add">Quantity to Add:</label>
            <input type="number" class="form-control" id="quantity_to_add" name="quantity_to_add" min="1" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Order</button>
        <a href="manage_orders.php" class="btn btn-secondary">Cancel</a>
    </form>
    <?php else: ?>
    <p>No order found with the given ID.</p>
    <a href="manage_orders.php" class="btn btn-primary">Back to Manage Orders</a>
    <?php endif; ?>
</div>

</body>
</html>
