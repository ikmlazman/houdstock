<?php
session_start();
require_once 'connect.php';

// Check if the user is logged in and if their role is 'admin', 'employee', or 'supplier'
if (!isset($_SESSION['role'])) {
    header('Location: login1.php');
    exit();
}

$user_role = $_SESSION['role'];
$supplier_id = $_SESSION['supplier_id'] ?? null; // Use correct session variable for supplier

// Fetch orders based on user role
if ($user_role === 'supplier') {
    if (!$supplier_id) {
        echo "Supplier ID not found in session.";
        exit();
    }

    // Fetch orders related to the products supplied by this supplier
    $sql = "
        SELECT 
            orders.order_id,
            orders.product_id,
            product.product_name,
            orders.order_quantity,
            product.price_per_unit,
            orders.date_ordered,
            (orders.order_quantity * product.price_per_unit) AS total_price
        FROM 
            orders
        JOIN 
            product ON orders.product_id = product.product_id
        JOIN 
            supplier ON product.supplier_id = supplier.supplier_id
        WHERE 
            supplier.supplier_id = ?
    ";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $supplier_id);
} else {
    // Fetch all orders for admin and employee
    $sql = "
        SELECT 
            orders.order_id,
            orders.product_id,
            product.product_name,
            orders.order_quantity,
            product.price_per_unit,
            orders.date_ordered,
            supplier.supplier_name,
            (orders.order_quantity * product.price_per_unit) AS total_price
        FROM 
            orders
        JOIN 
            product ON orders.product_id = product.product_id
        JOIN 
            supplier ON product.supplier_id = supplier.supplier_id
    ";
    $stmt = $con->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();

// Handle case when no orders are found
if ($result->num_rows === 0) {
    $no_orders = true;
} else {
    $no_orders = false;
}

$stmt->close();
$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css">
</head>
<body>

<div class="container">
    <h1>Manage Orders</h1>

    <?php if ($no_orders): ?>
        <p>No orders found.</p>
    <?php else: ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Product ID</th>
                <th>Product Name</th>
                <th>Quantity Ordered</th>
                <th>Price per Unit</th>
                <th>Total Price</th>
                <th>Date Ordered</th>
                <?php if ($user_role !== 'supplier'): ?>
                    <th>Supplier Name</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                <td><?php echo htmlspecialchars($row['product_id']); ?></td>
                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                <td><?php echo htmlspecialchars($row['order_quantity']); ?></td>
                <td><?php echo number_format($row['price_per_unit'], 2); ?></td>
                <td><?php echo number_format($row['total_price'], 2); ?></td>
                <td><?php echo htmlspecialchars($row['date_ordered']); ?></td>
                <?php if ($user_role !== 'supplier'): ?>
                    <td><?php echo htmlspecialchars($row['supplier_name']); ?></td>
                <?php endif; ?>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <a href="<?php echo ($user_role === 'admin') ? 'admin_dashboard.php' : (($user_role === 'employee') ? 'employee_dashboard.php' : 'supplier_dashboard.php'); ?>" class="btn btn-secondary">Back to Dashboard</a>
</div>

<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>
