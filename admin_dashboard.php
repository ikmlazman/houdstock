<?php
// admin_dashboard.php - Admin Dashboard Page
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
//8.11.2024

// Get the supplier ID from the URL and validate it
$supplier_id = isset($_GET['supplier_id']) && is_numeric($_GET['supplier_id']) ? intval($_GET['supplier_id']) : 0;

// Query to fetch the supplier username
$supplier_query = "SELECT username, supplier_name FROM supplier WHERE supplier_id = $supplier_id";
$supplier_result = mysqli_query($con, $supplier_query);
$supplier = mysqli_fetch_assoc($supplier_result);

// Store products with low or out-of-stock quantity for pop-up
$lowStockProducts = [];
while ($row = mysqli_fetch_assoc($result)) {
    if ($row['quantity_in_stock'] <= 5) {
        $lowStockProducts[] = $row; // Store product details for pop-up
    }
}

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

        .navbar-toggle {
            display: inline-block;
            color: #fff;
            font-size: 24px;
            cursor: pointer;
            margin-right: 10px;
        }

        .navbar-links {
            display: none;
            flex-direction: column;
        }

        .navbar-links.active {
            display: flex;
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

        .navbar .logout {
            margin-left: auto;
        }

        /* Dropdown Styles */
        .dropdown {
            position: relative;
            margin-top: 15px;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #333;
            min-width: 160px;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1;
            flex-direction: column;
        }

        .dropdown-content a {
            padding: 10px;
            text-decoration: none;
            display: block;
            color: white;
            text-align: left;
        }

        .dropdown-content a:hover {
            background-color: #575757;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        .container {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        h1 {
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }

        table {
            margin: 20px 0;
            border-collapse: collapse;
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 5px;
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

        .btn:hover {
            opacity: 0.8;
        }

        @media (min-width: 600px) {
            .navbar-links {
                display: flex;
                flex-direction: row;
            }
            .navbar-toggle {
                display: none;
            }
        
            .form-container {
    display: inline-block;
    width: 150px; /* Reduced width for a smaller form */
    padding: 10px;
    background-color: #fff; /* White background */
    border-radius: 5px; /* Slightly rounded corners */
    border: 1px solid #ddd; /* Light border for subtle separation */
}

.form-container input[type="number"] {
    width: 100%; /* Input takes up full container width */
    padding: 5px;
    margin: 5px 0; /* Spacing between input and button */
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px; /* Smaller text for a minimalist look */
    box-sizing: border-box; /* Ensures padding does not affect width */
}

.form-container button {
    width: 100%;
    padding: 6px;
    background-color: #4CAF50; /* Simple green background */
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 14px;
    cursor: pointer;
}

.form-container button:hover {
    background-color: #45a049; /* Slightly darker green on hover */
}

.logout {
            color: right;
            font-weight: bold;
        }

        footer {
            margin-top: 40px;
            text-align: center;
            background-color: #333;
            color: white;
            padding: 10px 0;
        }
 /* Parent container (if needed) */
.parent-container {
    display: flex;
    justify-content: flex-start; /* Aligns content to the left by default */
}

/* Button Style */
.btn-ikmal {
    background-color: rgba(0, 0, 0, 0.1); /* Black with a subtle transparency */
    color: black;
    font-size: 18px;
    padding: 12px 30px;
    border-radius: 5px; /* Rectangular shape with slight rounded corners */
    text-transform: uppercase; /* Make text uppercase */
    font-weight: bold;
    letter-spacing: 1px;
    transition: all 0.3s ease; /* Smooth transition */
    border: none; /* No border */
    text-decoration: none; /* Remove underline from text */
    float: right; /* Push the button to the right */
}


/* Hover Effect */
.btn-ikmal:hover {
    background-color: #333333; /* Darker black when hovered */
    transform: translateY(-5px); /* Slight lift effect */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Shadow for depth */
}

.btn-ikmal:focus {
    outline: none;
}


    </style>
    <script>


    </script>
</head>
<body>

    <!-- Navbar -->
    <div class="navbar">
        <span class="navbar-toggle" onclick="toggleNavbar()">☰</span>
        <div class="navbar-links" id="navbarLinks">
            <div class="dropdown">
                <a href="#">Manage Users</a>
                <div class="dropdown-content">
                    <a href="add_supplier.php">Supplier</a>
                    <a href="add_employee.php">Employee</a>
                    <a href="add_admin.php">Admin</a>
                </div>
            </div>
            <a href="displayproduct.php">Manage Inventory</a>
            <a href="manage_orders.php">Manage Orders</a>
            <a href="report.php">Generate Reports</a>
            <a href="user_manual.php">User Manual</a>
            <a href="logout.php" class="logout">Logout</a>
    </div>
    </div>
    

    <div class="container">
        <h1>Admin Dashboard</h1>
        <div class="content">
            <p>Welcome, <strong><?php echo $_SESSION['username']; ?></strong>! Use the navigation bar to manage various sections of the inventory system.</p>
        </div>

        <h2>Inventory Management</h2>
         <!-- Stylish Ikmal Button -->
         <div class="text-center">
            <a href="inventorybulk.php" class="btn btn-ikmal">Bulk Update</a>
        </div>
        <?php if (mysqli_num_rows($result) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Product ID</th>
                    <th>Item Name</th>
                    <th>Quantity in Stock</th>
                    <th>Price per Unit (RM)</th>
                    <th>Supplier Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                mysqli_data_seek($result, 0); // Reset the result pointer
                while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['product_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['quantity_in_stock']); ?></td>
                    <td><?php echo htmlspecialchars($row['price_per_unit']); ?></td>
                    <td><?php echo htmlspecialchars($row['supplier_name']); ?></td>
                    <td>
                    <!-- Form for Adding Inventory -->
                    <form action="update_inventory.php" method="post" class="form-container">
                        <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                        <label for="add_quantity_<?php echo $row['product_id']; ?>">Add Quantity:</label>
                        <input type="number" id="add_quantity_<?php echo $row['product_id']; ?>" name="quantity" min="1" required>
                        <button type="submit" name="add" class="btn btn-success">Add</button>
                    </form>

                    <!-- Form for Deducting Inventory -->
                    <form action="update_inventory.php" method="post" class="form-container">
                        <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                        <label for="deduct_quantity_<?php echo $row['product_id']; ?>">Deduct Quantity:</label>
                        <input type="number" id="deduct_quantity_<?php echo $row['product_id']; ?>" name="quantity" min="1" required>
                        <button type="submit" name="deduct" class="btn btn-danger">Deduct</button>
                    </form>
                </td>

                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p>No products found in the inventory.</p>
        <?php endif; ?>
    </div>
   

    <footer>
        <p1>© 2024 HoudStock. All rights reserved.</p1>
    </footer>

<script>
 document.addEventListener('DOMContentLoaded', function() {
    <?php if (count($lowStockProducts) > 0): ?>
        let lowStockMessage = "Warning: The following products have stock issues:\n\n";
        let telegramMessage = "Warning: The following products have stock issues and need to be ordered:\n\n";

        <?php foreach ($lowStockProducts as $product): ?>
            <?php 
                // If stock is 0, show "Out of Stock"
                if ($product['quantity_in_stock'] == 0) { 
                    echo "lowStockMessage += 'Product: " . addslashes($product['product_name']) . ", Status: Out of Stock\\n';";
                    echo "telegramMessage += 'Product: " . addslashes($product['product_name']) . ", Status: Out of Stock. Please order this product.\\n';";
                }
                // If stock is below 5 but greater than 0, show "Low Stock"
                elseif ($product['quantity_in_stock'] < 5) {
                    echo "lowStockMessage += 'Product: " . addslashes($product['product_name']) . ", Status: Low Stock (" . $product['quantity_in_stock'] . ")\\n';";
                    echo "telegramMessage += 'Product: " . addslashes($product['product_name']) . ", Status: Low Stock (" . $product['quantity_in_stock'] . ")\\n';";
                }
            ?>
        <?php endforeach; ?>
        
        // Check if there is any low stock or out-of-stock product, then show an alert
        if (lowStockMessage !== "Warning: The following products have stock issues:\n\n") {
            alert(lowStockMessage);  // Display alert with low stock information

            // Send the message to the supplier via Telegram using the fetch API
            fetch('https://api.telegram.org/bot7314268770:AAGGj8oQgnGCob8icZV2u3Q3ZKarZtj5Azo/sendMessage', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    chat_id: '-7314268770',
                    text: telegramMessage
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Telegram Message Sent:', data);
            })
            .catch(error => {
                console.error('Error sending message to Telegram:', error);
            });
        }
    <?php endif; ?>
});

</script>
   
</body>
</html>
