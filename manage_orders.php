<?php
session_start();
require_once 'connect.php';

// Redirect to login if the user is not logged in
if (!isset($_SESSION['role'])) {
    header('Location: login1.php');
    exit();
}

$is_admin = $_SESSION['role'] == 'admin'; // Determine if the user is an admin

// Function to send Telegram notifications
function sendTelegramNotification($message, $token, $chat_id) {
    $url = "https://api.telegram.org/bot$token/sendMessage?chat_id=$chat_id&text=" . urlencode($message);
    file_get_contents($url) ?: error_log("Error sending message to Telegram.");
}

// Telegram Bot Tokens and Chat IDs
$telegram_tokens = [
    'supplier' => ['token' => '7314268770:AAGGj8oQgnGCob8icZV2u3Q3ZKarZtj5Azo', 'chat_id' => '5520559929'],
    'admin' => ['token' => '7557012745:AAEOy-HxoeKEc0i8VqdF0T6lkTSPYVjdC_A', 'chat_id' => '5520559929'],
];

// Fetch all orders with product and supplier details
$sql = "
    SELECT 
        orders.order_id,
        orders.product_id,
        orders.order_quantity,
        product.product_name,
        orders.date_ordered,
        (orders.order_quantity * product.price_per_unit) AS total_price,
        supplier.supplier_name
    FROM 
        orders
    JOIN 
        product ON orders.product_id = product.product_id
    JOIN 
        supplier ON orders.supplier_id = supplier.supplier_id
";
$result = $con->query($sql);

// Handle POST requests (Order creation or Stock updates)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = intval($_POST['product_id']);
    $quantity_ordered = intval($_POST['order_quantity'] ?? 0);
    
    // Fetch product and supplier details
    $product_query = "
        SELECT 
            product.product_name, product.price_per_unit, 
            supplier.supplier_name, supplier.supplier_id 
        FROM 
            product 
        JOIN 
            supplier ON product.supplier_id = supplier.supplier_id 
        WHERE 
            product.product_id = ?";
    $stmt = $con->prepare($product_query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product_data = $stmt->get_result()->fetch_assoc();

    if (!$product_data) {
        echo "<script>alert('Invalid Product ID.'); window.history.back();</script>";
        exit();
    }

    $product_name = $product_data['product_name'];
    $price_per_unit = $product_data['price_per_unit'];
    $supplier_name = $product_data['supplier_name'];
    $supplier_id = $product_data['supplier_id'];

    // Check stock availability in the supplier_stock table
    $stock_query = "
        SELECT quantity_in_stock 
        FROM supplier_stock 
        WHERE product_id = ? AND supplier_id = ?";
    $stock_stmt = $con->prepare($stock_query);
    $stock_stmt->bind_param("ii", $product_id, $supplier_id);
    $stock_stmt->execute();
    $stock_result = $stock_stmt->get_result()->fetch_assoc();

    if ($stock_result) {
        $available_stock = $stock_result['quantity_in_stock'];
        if ($quantity_ordered > $available_stock) {
            // Insufficient stock, notify user
            $message = "Stock for '$product_name' is insufficient. Available stock: $available_stock, Order Quantity: $quantity_ordered.";
            // Notify the cafe owner and employee (assuming they have the same chat ID for simplicity)
            sendTelegramNotification($message, $telegram_tokens['supplier']['token'], $telegram_tokens['supplier']['chat_id']);
            sendTelegramNotification($message, $telegram_tokens['admin']['token'], $telegram_tokens['admin']['chat_id']);
            echo "<script>alert('Insufficient stock for $product_name. Available: $available_stock.'); window.history.back();</script>";
            exit();
        }
    } else {
        echo "<script>alert('Stock data not found for this product.'); window.history.back();</script>";
        exit();
    }

    if (isset($_POST['make_order'])) {
        $total_price = $price_per_unit * $quantity_ordered;

        // Insert the order into the database
        $insert_order_sql = "
            INSERT INTO orders (product_id, order_quantity, total_price, supplier_id, date_ordered) 
            VALUES (?, ?, ?, ?, NOW())
        ";
        $insert_stmt = $con->prepare($insert_order_sql);
        $insert_stmt->bind_param("iiid", $product_id, $quantity_ordered, $total_price, $supplier_id);
        if ($insert_stmt->execute()) {
            // Notify the supplier
            $order_message = "New Order: '$product_name' (ID: $product_id) - Quantity: $quantity_ordered - Supplier: $supplier_name - Total Price: RM " . number_format($total_price, 2);
            sendTelegramNotification($order_message, $telegram_tokens['supplier']['token'], $telegram_tokens['supplier']['chat_id']);
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
        .container { max-width: 600px; margin: 50px auto; text-align: center; }
        table { margin-top: 20px; font-size: 0.9rem; }
        h1 { font-size: 1.5rem; }
        .minimalist-table { width: 50%; margin: 20px auto; border: none; }
        .minimalist-table th, .minimalist-table td { padding: 10px; text-align: center; }
        .minimalist-table th { background-color: #f4f4f4; }
    </style>
</head>
<body>
<div class="container">
    <a href="<?php echo $is_admin ? 'admin_dashboard.php' : 'employee_dashboard.php'; ?>" class="btn btn-secondary mb-4">Back to Dashboard</a>
    <h1>Manage Orders</h1>

    <!-- Add Order Form -->
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

    <!-- Product List Table -->
    <table class="table table-sm table-borderless minimalist-table">
        <thead>
            <tr><th>Product ID</th><th>Product Name</th></tr>
        </thead>
        <tbody>
            <?php
            $product_query = "SELECT product_id, product_name FROM product";
            $product_result = $con->query($product_query);
            while ($row = $product_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['product_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Orders Table -->
    <?php if ($result && $result->num_rows > 0): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Product ID</th>
                <th>Supplier</th>
                <th>Product</th>
                <th>Quantity</th>
                <th>Total Price</th>
                <th>Date</th>
                <?php if ($is_admin): ?><th>Actions</th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                <td><?php echo htmlspecialchars($row['product_id']); ?></td>
                <td><?php echo htmlspecialchars($row['supplier_name']); ?></td>
                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                <td><?php echo htmlspecialchars($row['order_quantity']); ?></td>
                <td>RM <?php echo number_format($row['total_price'], 2); ?></td>
                <td><?php echo htmlspecialchars($row['date_ordered']); ?></td>
                <?php if ($is_admin): ?>
                <td><a href="delete_order.php?id=<?php echo $row['order_id']; ?>" class="btn btn-danger btn-sm">Delete</a></td>
                <?php endif; ?>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
