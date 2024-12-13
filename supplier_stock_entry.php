<?php
session_start();
require_once 'connect.php';

// Check if the user is logged in and if they are a 'supplier'
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'supplier') {
    header('Location: login1.php');
    exit();
}

// Verify if supplier_id is set
$supplierId = isset($_SESSION['supplier_id']) ? $_SESSION['supplier_id'] : null;
if ($supplierId === null) {
    echo "<p>Supplier ID not found. Please log in again.</p>";
    exit();
}

// Initialize messages
$message = '';

// Process form submission for Create or Update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['product_id'], $_POST['quantity_in_stock'])) {
        $productId = $_POST['product_id'];
        $stockQuantity = $_POST['quantity_in_stock'];

        // Check if stock entry already exists for this product and supplier
        $stmt = $con->prepare("SELECT * FROM supplier_stock WHERE supplier_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $supplierId, $productId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Update stock quantity if record exists
            $updateStmt = $con->prepare("UPDATE supplier_stock SET quantity_in_stock = ? WHERE supplier_id = ? AND product_id = ?");
            $updateStmt->bind_param("iii", $stockQuantity, $supplierId, $productId);
            $updateStmt->execute();
            $message = "Stock quantity updated successfully for product ID: $productId with quantity: $stockQuantity!";
        } else {
            // Insert new record if no existing entry
            $insertStmt = $con->prepare("INSERT INTO supplier_stock (supplier_id, product_id, quantity_in_stock) VALUES (?, ?, ?)");
            $insertStmt->bind_param("iii", $supplierId, $productId, $stockQuantity);
            $insertStmt->execute();
            $message = "Stock quantity added successfully for product ID: $productId with quantity: $stockQuantity!";
        }
    }
}

// Process Delete
if (isset($_GET['delete_id'])) {
    $deleteId = $_GET['delete_id'];
    $deleteStmt = $con->prepare("DELETE FROM supplier_stock WHERE supplier_id = ? AND product_id = ?");
    $deleteStmt->bind_param("ii", $supplierId, $deleteId);
    $deleteStmt->execute();
    $message = "Stock entry deleted successfully for product ID: $deleteId!";
}

// Fetch all products associated with this supplier
$productStmt = $con->prepare("SELECT * FROM product WHERE supplier_id = ?");
$productStmt->bind_param("i", $supplierId);
$productStmt->execute();
$productResult = $productStmt->get_result();

// Fetch all stock entries for this supplier
$stockStmt = $con->prepare("SELECT s.product_id, p.product_name, s.quantity_in_stock 
                            FROM supplier_stock s 
                            JOIN product p ON s.product_id = p.product_id 
                            WHERE s.supplier_id = ?");
$stockStmt->bind_param("i", $supplierId);
$stockStmt->execute();
$stockResult = $stockStmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Availability Management</title>
    <a href="supplier_dashboard.php" style="display: inline-block; margin-bottom: 15px; padding: 10px 15px; background: #007bff; color: #fff; text-decoration: none; border-radius: 5px;">Back to Dashboard</a>
    
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
        .container { max-width: 800px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        h2 { color: #333; text-align: center;}
        label { display: 
            block; margin-top: 10px; color: #555; text-align: center; }
            input[type="number"], select {
    padding: 10px;
    margin-top: 5px;
    border-radius: 4px;
    border: 1px solid #ccc;
    display: block;
    margin-left: auto;
    margin-right: auto;
}
button {
    margin-top: 10px;
    padding: 10px 15px;
    background-color: #4CAF50;
    color: white;
    border: none;
    cursor: pointer;
    border-radius: 4px;
    display: block;
    margin-left: auto;
    margin-right: auto;
}

        button:hover { background-color: #45a049; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 12px; text-align: left; }
        th { background-color: #f2f2f2; }
        .low-stock { background-color: #ffcccc; } /* Highlight low stock rows */
        .btn-delete { background-color: #f44336; color: white; border: none; padding: 5px 10px; cursor: pointer; }
        .btn-delete:hover { background-color: #d32f2f; }
    </style>
</head>
<body>

<div class="container">
    <h2>Manage Stock Availability</h2>

    <!-- Display Message -->
    <?php if ($message): ?>
        <script>
            alert('<?php echo $message; ?>');
        </script>
    <?php endif; ?>

    <!-- Add or Update Stock Form -->
    <form method="POST" action="supplier_stock_entry.php">
        <label for="product_id">Select Product:</label>
        <select id="product_id" name="product_id" required>
            <?php while ($product = $productResult->fetch_assoc()) { ?>
                <option value="<?php echo $product['product_id']; ?>">
                    <?php echo htmlspecialchars($product['product_name']); ?>
                </option>
            <?php } ?>
        </select>

        <label for="quantity_in_stock">Stock Quantity:</label>
        <input type="number" id="quantity_in_stock" name="quantity_in_stock" min="0" required>

        <button type="submit">Update Stock</button>
    </form>

    <!-- Display Stock Entries -->
    <h2>Stock Availability</h2>
    
    <table>
        <thead>
            <tr>
                <th>Product ID</th>
                <th>Product Name</th>
                <th>Stock Quantity</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($stock = $stockResult->fetch_assoc()) { ?>
            <tr class="<?php echo ($stock['quantity_in_stock'] < 10) ? 'low-stock' : ''; ?>">
                <td><?php echo $stock['product_id']; ?></td>
                <td><?php echo htmlspecialchars($stock['product_name']); ?></td>
                <td><?php echo $stock['quantity_in_stock']; ?></td>
                <td>
                    <a href="supplier_stock_entry.php?delete_id=<?php echo $stock['product_id']; ?>" onclick="return confirm('Are you sure you want to delete this stock entry?');">
                        <button class="btn-delete">Delete</button>
                    </a>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

</body>
</html>
