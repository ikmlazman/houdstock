<?php
session_start();

// Ensure the user is an admin or employee
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'employee')) {
    header('Location: login1.php');
    exit();
}

require_once 'connect.php';

function sendTelegramNotification($action, $product_name, $quantity, $current_stock, $new_stock, $supplier_name) {
    $token = '7557012745:AAEOy-HxoeKEc0i8VqdF0T6lkTSPYVjdC_A'; // Your bot token
    $chat_id = '5520559929'; // Your numeric chat ID

    // Determine the message based on the action (add or deduct)
    if ($action == 'add') {
        $message = "Stock Added: Product: $product_name (Supplier: $supplier_name) - Quantity Added: $quantity. Current Stock: $current_stock + $quantity = $new_stock.";
    } elseif ($action == 'deduct') {
        $message = "Stock Deducted: Product: $product_name (Supplier: $supplier_name) - Quantity Deducted: $quantity. Current Stock: $current_stock - $quantity = $new_stock.";
    } else {
        $message = "Invalid action performed for product: $product_name.";
    }

    // Create the URL for the API request
    $url = "https://api.telegram.org/bot$token/sendMessage?chat_id=$chat_id&text=" . urlencode($message);

    // Send request to Telegram API
    $response = file_get_contents($url);

    // Check for response
    if ($response === FALSE) {
        echo "Error sending message.";
    }
}

// Fetch data from the database for displaying products
$sql = "SELECT p.product_id, p.product_name, p.quantity_in_stock, p.price_per_unit, s.supplier_name 
        FROM product p
        JOIN supplier s ON p.supplier_id = s.supplier_id";
$result = mysqli_query($con, $sql);

// Handle form submission for adding or deducting stock
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['quantity']) && isset($_POST['action'])) {
    $quantities = $_POST['quantity'];
    $action = $_POST['action'];
    $error = false;

    // Loop through each product's quantity and update the stock accordingly
    foreach ($quantities as $product_id => $quantity) {
        // Ensure quantity is a valid number and not empty
        $quantity = (int)$quantity;
        if ($quantity <= 0) {
            continue; // Skip if the quantity is blank or zero
        }

        // Fetch the current stock for the product
        $sql = "SELECT quantity_in_stock, product_name, supplier_id FROM product WHERE product_id = $product_id";
        $result = mysqli_query($con, $sql);

        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $current_stock = (int)$row['quantity_in_stock']; // Cast to integer
            $product_name = $row['product_name'];
            $supplier_id = $row['supplier_id'];

            // Fetch the supplier name
            $supplier_sql = "SELECT supplier_name FROM supplier WHERE supplier_id = $supplier_id";
            $supplier_result = mysqli_query($con, $supplier_sql);
            $supplier_name = '';
            if ($supplier_result) {
                $supplier_row = mysqli_fetch_assoc($supplier_result);
                $supplier_name = $supplier_row['supplier_name'];
            }

            // Determine the new stock based on the action (add or deduct)
            if ($action == 'add') {
                $new_stock = $current_stock + $quantity;
            } elseif ($action == 'deduct') {
                $new_stock = $current_stock - $quantity;
                // Check if new stock goes below zero
                if ($new_stock < 0) {
                    echo "Error: Cannot deduct more than available stock for product: $product_name (Product ID: $product_id).<br>";
                    $error = true;
                    break;
                }
            } else {
                echo "Invalid action.<br>";
                $error = true;
                break;
            }

            // Update the stock in the database
            $update_sql = "UPDATE product SET quantity_in_stock = $new_stock WHERE product_id = $product_id";
            if (!mysqli_query($con, $update_sql)) {
                echo "Error updating stock for product: $product_name (Product ID: $product_id).<br>";
                $error = true;
                break;
            }

            // Send individual stock update message
            sendTelegramNotification($action, $product_name, $quantity, $current_stock, $new_stock, $supplier_name);
        } else {
            echo "Error fetching data for product ID: $product_id.<br>";
            $error = true;
            break;
        }
    }

    // If no errors, redirect
    if (!$error) {
        header('Location: inventorybulk.php');
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Inventory - Bulk Update</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <script>
        function confirmAction(action) {
            return confirm(`Are you sure you want to ${action} the entered quantities?`);
        }
    </script>
    <style>

        title {
            text-align: center;
        }
        body {
            background-color: #e0e0e0;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #495057;
            font-size: 1.5rem;
            margin-bottom: 20px;
        }
        .back-btn {
            background-color: #007bff;
            color: #fff;
            font-size: 1rem;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-left: auto;
            margin-right: auto;
        }
        .back-btn:hover {
            background-color: #0056b3;
        }
        .table {
            margin-top: 20px;
        }
        .table th, .table td {
            text-align: center;
            vertical-align: middle;
        }
        .table thead {
            background-color: #f0f0f0;
            color: #495057;
        }
        .table-bordered {
            border: 1px solid #dee2e6;
        }
        .table-bordered th, .table-bordered td {
            border: 1px solid #dee2e6;
        }
        .btn-success, .btn-danger {
            font-size: 1rem;
            padding: 10px 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2 class="mb-4 text-center">Bulk Update</h2>

        <!-- Back Button -->
        <a href="<?php echo ($_SESSION['role'] == 'admin') ? 'admin_dashboard.php' : 'employee_dashboard.php'; ?>" class="back-btn">Back to Dashboard</a>

        <form action="inventorybulk.php" method="post">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Item Name</th>
                        <th>Quantity in Stock</th>
                        <th>Price per Unit (RM)</th>
                        <th>Supplier Name</th>
                        <th>Input Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['product_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['quantity_in_stock']); ?></td>
                            <td><?php echo htmlspecialchars($row['price_per_unit']); ?></td>
                            <td><?php echo htmlspecialchars($row['supplier_name']); ?></td>
                            <td>
                                <input type="number" name="quantity[<?php echo $row['product_id']; ?>]" class="form-control" placeholder="Enter quantity" min="0">
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <div class="d-flex justify-content-between">
                <button type="submit" name="action" value="add" class="btn btn-success" onclick="return confirmAction('add');">Add Quantities</button>
                <button type="submit" name="action" value="deduct" class="btn btn-danger" onclick="return confirmAction('deduct');">Deduct Quantities</button>
            </div>
        </form>
    </div>
</body>
</html>
