<?php
session_start();

// Dummy role data for testing; replace with actual session role check
// $_SESSION['role'] = 'admin'; // Uncomment for testing as admin
// $_SESSION['role'] = 'employee'; // Uncomment for testing as employee
// $_SESSION['role'] = 'supplier'; // Uncomment for testing as supplier

$role = isset($_SESSION['role']) ? $_SESSION['role'] : null;

if (!$role) {
    echo "Please log in to access the user manual.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Manual</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #2c2c2c; /* Dark grey background */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh; /* Full viewport height */
        }
        .container {
            background-color: #3c3c3c; /* Slightly lighter grey for content */
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
            padding: 30px;
            width: 90%;
            max-width: 600px; /* Maximum width for larger screens */
        }
        h1 {
            text-align: center;
            color: #ffffff; /* White text for contrast */
        }
        .manual-section {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #555; /* Darker border for sections */
            border-radius: 8px;
        }
        h2 {
            color: #ffffff; /* White text for section headings */
        }
        ul {
            list-style-type: disc;
            padding-left: 20px;
            color: #dddddd; /* Light grey text for list items */
        }
        .back-button {
            display: block;
            margin-top: 20px;
            padding: 10px 15px;
            text-align: center;
            background-color: #007BFF; /* Blue background for button */
            color: white; /* White text */
            border-radius: 5px;
            text-decoration: none; /* Remove underline */
            transition: background-color 0.3s ease, transform 0.2s ease; /* Smooth transitions */
        }
        .back-button:hover {
            background-color: #0056b3; /* Darker blue on hover */
            transform: translateY(-2px); /* Slight lift effect on hover */
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>User Manual</h1>

        <?php if ($role == 'admin'): ?>

            <!-- Admin Manual Section -->
            <div class="manual-section">
                <h2>Admin User Manual</h2>
                <p><strong>Overview:</strong> This section provides guidelines for system administrators.</p>
                <ul>
                    <li><strong>Manage Inventory:</strong> Access the full list of products, add new products, or delete products.</li>
                    <li><strong>Manage Users:</strong> Add, edit, or delete user accounts and assign roles.</li>
                    <li><strong>View Reports:</strong> Generate reports on product stock, and orders.</li>
                    <li><strong>Make Orders:</strong> Create orders to suppliers by selecting the products you need to restock and entering the required quantities. Once the order is submitted, the supplier will be notified.</li>
                </ul>
            </div>
            <a href="javascript:history.back()" class="back-button">Back</a>

        <?php elseif ($role == 'employee'): ?>

            <!-- Employee Manual Section -->
            <div class="manual-section">
                <h2>Employee User Manual</h2>
                <p><strong>Overview:</strong> This section provides guidelines for employees on managing daily operations.</p>
                <ul>
                    <li><strong>Update Stock:</strong> Record new stock levels for products as they are restocked or used.</li>
                    <li><strong>View Reports:</strong> Generate orders and inventory stock reports.</li>
                    <li><strong>Inventory Check:</strong> Regularly monitor stock levels to ensure product availability.</li>
                </ul>
            </div>
            <a href="javascript:history.back()" class="back-button">Back</a>

        <?php elseif ($role == 'supplier'): ?>

            <!-- Supplier Manual Section -->
            <div class="manual-section">
                <h2>Supplier User Manual</h2>
                <p><strong>Overview:</strong> This section provides guidelines for suppliers on managing product supplies.</p>
                <ul>
                    <li><strong>Update Product Prices:</strong> Change the prices of products supplied based on new rates.</li>
                    <li><strong>View Orders:</strong> Check the orders placed for supplied products and manage deliveries.</li>
                    <li><strong>View Reports:</strong> Generate orders reports.</li>
                </ul>
            </div>
            <a href="javascript:history.back()" class="back-button">Back</a>

        <?php else: ?>
            <p style="color:white;">You do not have permission to view the user manual.</p>
        <?php endif; ?>

    </div>

</body>
</html>
