<?php 
session_start();
require_once 'connect.php';

// Fetch inventory for all suppliers (for admin view)
$inventoryStmt = $con->prepare("
    SELECT ss.supplier_stock_id, p.product_name, p.price_per_unit, 
           ss.quantity_in_stock, ss.date_added, s.supplier_name, p.category
    FROM supplier_stock ss
    JOIN product p ON ss.product_id = p.product_id
    JOIN supplier s ON ss.supplier_id = s.supplier_id
");
$inventoryStmt->execute();
$inventoryResult = $inventoryStmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css">

    <style>
        body {
            background-color: #e0e0e0;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            color: white;
        }

        .container {
            background-color: rgba(0, 0, 0, 0.7);
            padding: 20px;
            border-radius: 10px;
        }
    </style>

</head>
<body>

<div class="container">
    <button class="btn btn-primary my-5">
        <a href="product.php" class="text-light">Add product</a>
    </button>
    <table class="table table-striped table-dark">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Product Name</th>
                <th scope="col">Category</th>
                <th scope="col">Quantity In Stock</th>
                <th scope="col">Supplier Name</th>
                <th scope="col">Price Per Unit</th>
                <th scope="col">Last Restocked Date</th>
                <th scope="col">Date Added</th>
                <th scope="col">Operations</th>
            </tr>
        </thead>
        <tbody>

        <?php
        // Updated query to join the supplier table
        $sql = "
            SELECT p.*, s.supplier_name 
            FROM product p
            LEFT JOIN supplier s ON p.supplier_id = s.supplier_id
        ";
        $result = mysqli_query($con, $sql);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo '<tr>
                    <th scope="row">' . $row['product_id'] . '</th>
                    <td>' . $row['product_name'] . '</td>
                    <td>' . $row['category'] . '</td>
                    <td>' . $row['quantity_in_stock'] . '</td>
                    <td>' . $row['supplier_name'] . '</td> <!-- Display supplier name -->
                    <td>' . $row['price_per_unit'] . '</td>
                    <td>' . $row['last_restocked_date'] . '</td>
                    <td>' . $row['date_added'] . '</td>
                    <td>
                        <button class="btn btn-primary">
                            <a href="updateproduct.php?updateproduct_id=' . $row['product_id'] . '" class="text-light">Update</a>
                        </button>
                        <button class="btn btn-danger">
                            <a href="deleteproduct.php?deleteproduct_id=' . $row['product_id'] . '" class="text-light">Delete</a>
                        </button>
                    </td>
                </tr>';
            }
        }
        ?>

        </tbody>
    </table>

    <div class="text-center mt-4">
        <?php 
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            echo '<a href="admin_dashboard.php" class="btn btn-secondary">Back to Admin Dashboard</a>';
        } else {
            echo '<a href="employee_dashboard.php" class="btn btn-secondary">Back to User Dashboard</a>';
        }
        ?>
    </div>
</div>

</body>
</html>
