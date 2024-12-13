<?php
session_start();
require_once 'connect.php';

// Check if the user is logged in
if (!isset($_SESSION['role'])) {
    header('Location: login1.php');
    exit();
}

// Check the user's role (admin or user)
$is_admin = $_SESSION['role'] == 'admin';

// Function to send Telegram notifications
function sendTelegramNotification($message, $token, $chat_id) {
    $url = "https://api.telegram.org/bot$token/sendMessage?chat_id=$chat_id&text=" . urlencode($message);
    
    // Send request to Telegram API
    $response = file_get_contents($url);
    if ($response === FALSE) {
        error_log("Error sending message to Telegram.");
    }
}

// Supplier's Telegram Bot Token and Chat ID for order notifications
$supplier_bot_token = '7314268770:AAGGj8oQgnGCob8icZV2u3Q3ZKarZtj5Azo';
$supplier_chat_id = '5520559929';

// Admin's Telegram Bot Token and Chat ID for stock changes
$admin_bot_token = '7314268770:AAGGj8oQgnGCob8icZV2u3Q3ZKarZtj5Azo';
$admin_chat_id = '5520559929';

// Check stock levels and notify supplier if stock is low
function checkStockAndNotify($product_id, $product_name, $quantity_in_stock) {
    global $admin_bot_token, $admin_chat_id;
    if ($quantity_in_stock < 3) {
        $message = "Attention: Stock for '$product_name' (ID: $product_id) is low. Current stock: $quantity_in_stock.";
        sendTelegramNotification($message, $admin_bot_token, $admin_chat_id);
    }
}

// Attempt to fetch all orders with product and supplier details from the database
$sql = "
    SELECT 
        orders.order_id,
        orders.product_id,
        supplier.supplier_name,
        product.product_name,
        orders.order_quantity,
        orders.date_ordered
    FROM 
        orders
    JOIN 
        product ON orders.product_id = product.product_id
    JOIN 
        supplier ON product.supplier_id = supplier.supplier_id
";
$result = mysqli_query($con, $sql);

if (!$result) {
    die("Database query failed. Error: " . mysqli_error($con));
}

// Handle stock updates or new orders
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'];
    
    if (isset($_POST['make_order'])) {
        // Notify supplier of new order with specified quantity
        $quantity_ordered = intval($_POST['order_quantity']);
        $product_name_query = "SELECT product_name FROM product WHERE product_id = ?";
        $stmt = $con->prepare($product_name_query);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $product_result = $stmt->get_result()->fetch_assoc();
        $product_name = $product_result['product_name'];

        // Send order notification to supplier
        $order_message = "New Order: '$product_name' (ID: $product_id) - Quantity: $quantity_ordered";
        sendTelegramNotification($order_message, $supplier_bot_token, $supplier_chat_id);

        // Insert the order into the orders table
        $insert_order_sql = "INSERT INTO orders (product_id, order_quantity) VALUES (?, ?)";
        $insert_stmt = $con->prepare($insert_order_sql);
        $insert_stmt->bind_param("ii", $product_id, $quantity_ordered);
        $insert_stmt->execute();

        header("Location: manage_orders.php");
        exit();
    } else {
        // Handle stock add/deduct functionality
        $action = isset($_POST['add']) ? 'add' : (isset($_POST['deduct']) ? 'deduct' : null);

        // Fetch current stock quantity
        $stock_sql = "SELECT quantity_in_stock, product_name FROM product WHERE product_id = ?";
        $stmt = $con->prepare($stock_sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stock_result = $stmt->get_result();
        $stock_data = $stock_result->fetch_assoc();

        if ($stock_data) {
            $current_quantity = $stock_data['quantity_in_stock'];
            $product_name = $stock_data['product_name'];

            // Determine new quantity based on action
            if ($action === 'add') {
                $new_quantity = $current_quantity + 1;
                $notification_message = "Stock Added: Product '$product_name' (ID: $product_id) now has $new_quantity items.";
            } elseif ($action === 'deduct') {
                $new_quantity = max(0, $current_quantity - 1);
                $notification_message = "Stock Deducted: Product '$product_name' (ID: $product_id) now has $new_quantity items.";
            }

            // Update product quantity
            $update_sql = "UPDATE product SET quantity_in_stock = ? WHERE product_id = ?";
            $update_stmt = $con->prepare($update_sql);
            $update_stmt->bind_param("ii", $new_quantity, $product_id);
            $update_stmt->execute();

            // Notify about stock changes
            sendTelegramNotification($notification_message, $admin_bot_token, $admin_chat_id);

            // Check stock level for notification
            checkStockAndNotify($product_id, $product_name, $new_quantity);

            // Redirect to refresh data
            header("Location: manage_orders.php");
            exit();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css">
    <style>
        /* Additional styling */
    </style>
</head>
<a href="<?php echo $is_admin ? 'admin_dashboard.php' : 'employee_dashboard.php'; ?>" class="btn btn-secondary">Back to Dashboard</a>
</div>
<body>

<div class="container">
    <h1>Manage Orders</h1>
<!-- 12.11.2024 -->
<!-- Product List Table (Minimalist Style) -->
    <table class="table table-sm table-borderless" style="width: 50%; margin-bottom: 20px;">
        <thead>
            <tr>
                <th scope="col">Product ID</th>
                <th scope="col">Product Name</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $product_query = "SELECT product_id, product_name FROM product";
            $product_result = mysqli_query($con, $product_query);
            if ($product_result && mysqli_num_rows($product_result) > 0):
                while ($product_row = mysqli_fetch_assoc($product_result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product_row['product_id']); ?></td>
                        <td><?php echo htmlspecialchars($product_row['product_name']); ?></td>
                    </tr>
                <?php endwhile;
            else: ?>
                <tr>
                    <td colspan="2">No products available.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <!-- Add Order Form for Supplier Notification -->
    <form action="" method="post" class="mb-3">
        <div class="form-group">
            <label for="product_id">Product ID:</label>
            <input type="number" name="product_id" class="form-control" placeholder="Enter Product ID" required>
        </div>
        <div class="form-group">
            <label for="order_quantity">Order Quantity:</label>
            <input type="number" name="order_quantity" class="form-control" placeholder="Enter Quantity" required>
        </div>
        <button type="submit" name="make_order" class="btn btn-primary">Order & Notify Supplier</button>
    </form>

    <?php if ($result && mysqli_num_rows($result) > 0): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Product ID</th>
                <th>Supplier Name</th>
                <th>Product</th>
                <th>Quantity Ordered</th>
                <th>Date Ordered</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                <td><?php echo htmlspecialchars($row['product_id']); ?></td>
                <td><?php echo htmlspecialchars($row['supplier_name']); ?></td>
                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                <td><?php echo htmlspecialchars($row['order_quantity']); ?></td>
                <td><?php echo htmlspecialchars($row['date_ordered']); ?></td>
                <td>
                    <?php if ($is_admin): ?>
                        <a href="delete_order.php?id=<?php echo $row['order_id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                        
                        <!-- Add and Deduct Stock Form -->
                        <form action="" method="post" style="display: inline-block;">
                            <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                            <button type="submit" name="add" class="btn btn-success btn-sm">Add Stock</button>
                            <button type="submit" name="deduct" class="btn btn-danger btn-sm">Deduct Stock</button>
                        </form>
                    <?php else: ?>
                        <span class="badge badge-secondary">View Only</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>No orders found.</p>
    <?php endif; ?>



<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>
