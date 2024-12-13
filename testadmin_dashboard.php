<?php
session_start();

// Check if the user is an admin or redirect to login
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: login1.php');
    exit();
}

require_once 'connect.php';

// Fetch product data from the database
$sql = "
    SELECT 
        product.product_id,
        product.product_name,
        supplier.supplier_name,
        product.quantity_in_stock,
        product.price_per_unit
    FROM 
        product
    JOIN 
        supplier ON product.supplier_id = supplier.supplier_id
";
$result = mysqli_query($con, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #e0e0e0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .navbar {
            background-color: #222;
            display: flex;
            justify-content: center;
            padding: 10px 0;
            position: relative;
        }

        .navbar a {
            color: white;
            padding: 14px 20px;
            text-decoration: none;
            text-align: center;
            transition: background-color 0.3s;
        }

        .navbar a:hover {
            background-color: #444;
        }

        .container {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        table {
            width: 100%;
            max-width: 600px;
            margin: 20px 0;
            border-collapse: collapse;
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }

        th {
            background-color: #333;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .btn {
            padding: 5px 10px;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
            font-size: 14px;
        }

        .btn-success {
            background-color: green;
        }

        .btn-danger {
            background-color: red;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <div class="navbar">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="displayproduct.php">Manage Inventory</a>
        <a href="manage_orders.php">Manage Orders</a>
        <a href="logout.php" class="logout">Logout</a>
    </div>

    <div class="container">
        <h1>Admin Dashboard</h1>
        <h2>Inventory Management</h2>

        <?php if (mysqli_num_rows($result) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Product ID</th>
                    <th>Item Name</th>
                    <th>Quantity in Stock</th>
                    <th>Price per Unit</th>
                    <th>Supplier Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                while($row = mysqli_fetch_assoc($result)): 
                    $lowStock = $row['quantity_in_stock'] < 5;
                    $outOfStock = $row['quantity_in_stock'] == 0;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['product_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['quantity_in_stock']); ?></td>
                    <td><?php echo htmlspecialchars($row['price_per_unit']); ?></td>
                    <td><?php echo htmlspecialchars($row['supplier_name']); ?></td>
                    <td>
                        <!-- Form for Adding Inventory -->
                        <form action="update_inventory.php" method="post" style="display:inline-block;">
                            <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                            <label for="add_quantity_<?php echo $row['product_id']; ?>">Add Quantity:</label>
                            <input type="number" id="add_quantity_<?php echo $row['product_id']; ?>" name="quantity" min="1" required>
                            <button type="submit" name="add" class="btn btn-success">Add</button>
                        </form>

                        <!-- Form for Deducting Inventory -->
                        <form action="update_inventory.php" method="post" style="display:inline-block;">
                            <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                            <label for="deduct_quantity_<?php echo $row['product_id']; ?>">Deduct Quantity:</label>
                            <input type="number" id="deduct_quantity_<?php echo $row['product_id']; ?>" name="quantity" min="1" required>
                            <button type="submit" name="deduct" class="btn btn-danger">Deduct</button>
                        </form>
                    </td>
                </tr>

                <!-- Modal for low stock or out of stock -->
                <?php if ($lowStock || $outOfStock): ?>
                    <div id="modal_<?php echo $row['product_id']; ?>" class="modal">
                        <div class="modal-content">
                            <span class="close" onclick="closeModal(<?php echo $row['product_id']; ?>)">&times;</span>
                            <h3>Stock Alert!</h3>
                            <p>
                                Product: <?php echo htmlspecialchars($row['product_name']); ?><br>
                                Supplier: <?php echo htmlspecialchars($row['supplier_name']); ?><br>
                                <?php echo ($lowStock ? "Low Stock: Only {$row['quantity_in_stock']} left!" : "Out of Stock!") ?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p>No products found in the inventory.</p>
        <?php endif; ?>
    </div>

    <footer>
        <p>© 2024 Your Inventory Manager. All rights reserved.</p>
    </footer>

    <script>
        // Function to show the modal
        function showModal(productId) {
            document.getElementById("modal_" + productId).style.display = "block";
        }

        // Function to close the modal
        function closeModal(productId) {
            document.getElementById("modal_" + productId).style.display = "none";
        }

        // Automatically show the modal for low or out-of-stock products
        <?php 
            // Loop through products to show modal if needed
            mysqli_data_seek($result, 0); // Reset pointer for loop
            while ($row = mysqli_fetch_assoc($result)) {
                if ($row['quantity_in_stock'] < 5 || $row['quantity_in_stock'] == 0) {
                    echo "showModal({$row['product_id']});";
                }
            }
        ?>
    </script>

</body>
</html>
