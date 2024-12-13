<?php
session_start();
require_once 'connect.php';

// Check if the user is logged in as a supplier
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'supplier') {
    header('Location: login1.php');
    exit();
}

// Supplier information
$supplierId = $_SESSION['supplier_id'];
$username = $_SESSION['username'];

// Handle new item addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    // Sanitize and validate input values
    $productName = trim($_POST['product_name']);
    $pricePerUnit = trim($_POST['price']);
    $quantityInStock = trim($_POST['quantity_in_stock']);
    $category = trim($_POST['category']);
    $dateAdded = date('Y-m-d H:i:s');

    if (empty($productName) || empty($pricePerUnit) || empty($quantityInStock) || empty($category)) {
        echo "<script>alert('All fields must be filled.');</script>";
    } else {
        // Start a database transaction
        $con->begin_transaction();

        try {
            // Insert new product into the 'product' table
            $insertProductStmt = $con->prepare("INSERT INTO product (product_name, price_per_unit, category, supplier_id, date_added) VALUES (?, ?, ?, ?, ?)");
            $insertProductStmt->bind_param("sdsis", $productName, $pricePerUnit, $category, $supplierId, $dateAdded);
            $insertProductStmt->execute();
            $productId = $insertProductStmt->insert_id;
            $insertProductStmt->close();

            // Insert stock details into the 'supplier_stock' table
            $stockStmt = $con->prepare("INSERT INTO supplier_stock (supplier_id, product_id, quantity_in_stock, date_added) VALUES (?, ?, ?, ?)");
            $stockStmt->bind_param("iiis", $supplierId, $productId, $quantityInStock, $dateAdded);
            $stockStmt->execute();
            $stockStmt->close();

            // Commit transaction
            $con->commit();
            echo "<script>alert('Product added successfully!');</script>";
            header("Location: supplier_inventory.php");
            exit();
        } catch (Exception $e) {
            $con->rollback();
            echo "<script>alert('Error occurred while adding the product: {$e->getMessage()}');</script>";
        }
    }
}

// Handle item deletion
if (isset($_GET['delete_id'])) {
    $deleteId = $_GET['delete_id'];

    // Validate if the stock belongs to the supplier
    $stmt = $con->prepare("SELECT supplier_stock_id FROM supplier_stock WHERE supplier_stock_id = ? AND supplier_id = ?");
    $stmt->bind_param("ii", $deleteId, $supplierId);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();

        // Delete stock entry
        $deleteStmt = $con->prepare("DELETE FROM supplier_stock WHERE supplier_stock_id = ?");
        $deleteStmt->bind_param("i", $deleteId);
        $deleteStmt->execute();
        $deleteStmt->close();
        header("Location: supplier_inventory.php");
        exit();
    } else {
        echo "<script>alert('Product not found or unauthorized deletion.');</script>";
        $stmt->close();
    }
}

// Fetch all inventory for this supplier
$inventoryStmt = $con->prepare("
    SELECT ss.supplier_stock_id, p.product_name, p.price_per_unit,
           ss.quantity_in_stock, ss.date_added, p.category, s.supplier_name
    FROM supplier_stock ss
    JOIN product p ON ss.product_id = p.product_id
    JOIN supplier s ON p.supplier_id = s.supplier_id
    WHERE ss.supplier_id = ?
");
$inventoryStmt->bind_param("i", $supplierId);
$inventoryStmt->execute();
$inventoryResult = $inventoryStmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Inventory</title>
    <style>
        /* Minimalist CSS */
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h1, h2 {
            text-align: center;
            color: #444;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            padding: 10px;
            border: 1px solid #ddd;
        }

        table th {
            background-color: #f4f4f4;
            color: #333;
        }

        form input, form button, form select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        form button {
            background: #28a745;
            color: #fff;
            cursor: pointer;
        }

        form button:hover {
            background: #218838;
        }

        .delete-btn {
            background: #dc3545;
            color: #fff;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .delete-btn:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($username); ?></h1>
        <h2>Your Inventory</h2>

        <!-- Back Button -->
        <a href="supplier_dashboard.php" style="display: inline-block; margin-bottom: 15px; padding: 10px 15px; background: #007bff; color: #fff; text-decoration: none; border-radius: 5px;">Back to Dashboard</a>

        <!-- Form to Add New Item -->
        <form method="POST" action="">
            <input type="text" name="product_name" placeholder="Product Name" required>
            <input type="number" step="0.01" name="price" placeholder="Price per Unit" required>
            <input type="number" name="quantity_in_stock" placeholder="Quantity in Stock" required>
            <select name="category" required>
                <option value="beverage">Beverage</option>
                <option value="food">Food</option>
                <option value="condiment">Condiment</option>
                <option value="others">Others</option>
            </select>
            <button type="submit" name="add_item">Add Item</button>
        </form>

        <!-- Display Inventory -->
        <table>
            <thead>
                <tr>
                    <th>Stock ID</th>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Category</th>
                    <th>Date Added</th>
                    <th>Supplier Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $inventoryResult->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['supplier_stock_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                        <td><?php echo number_format($row['price_per_unit'], 2); ?></td>
                        <td><?php echo $row['quantity_in_stock']; ?></td>
                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                        <td><?php echo $row['date_added']; ?></td>
                        <td><?php echo htmlspecialchars($row['supplier_name']); ?></td>
                        <td>
                            <a href="?delete_id=<?php echo $row['supplier_stock_id']; ?>" class="delete-btn">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>
