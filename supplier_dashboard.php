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

$username = $_SESSION['username']; // Set this to the supplier's username or name

// Fetch stock availability for each supplier from supplier_stock table
$stmt = $con->prepare("SELECT p.product_name, ss.quantity_in_stock
                       FROM supplier_stock ss 
                       JOIN product p ON ss.product_id = p.product_id 
                       WHERE ss.supplier_id = ?");
$stmt->bind_param("i", $supplierId);
$stmt->execute();
$supplierStockResult = $stmt->get_result();

// Prepare data for the pie chart
$productNames = [];
$stockQuantities = [];
while ($row = $supplierStockResult->fetch_assoc()) {
    $productNames[] = $row['product_name'];
    $stockQuantities[] = $row['quantity_in_stock'];
}

//--12/11/2024
// Telegram Bot Token and Chat ID (set your bot token and chat ID here)
$token = '7576848535:AAGVA7luER6suIA5HjlAIgpZ_98KwHFnguw';
$chat_Id = '5520559929';

// Function to send a message to Telegram
function sendTelegramNotification($message, $token, $chat_Id) {
    $url = "https://api.telegram.org/bot$token/sendMessage";
    $data = [
        'chat_id' => $chat_Id,
        'text' => $message
    ];
    
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    $context = stream_context_create($options);
    file_get_contents($url, false, $context);
}

// Fetch supplier name
$supplierStmt = $con->prepare("SELECT supplier_name FROM supplier WHERE supplier_id = ?");
$supplierStmt->bind_param("i", $supplierId);
$supplierStmt->execute();
$supplierResult = $supplierStmt->get_result();
$supplier = $supplierResult->fetch_assoc();
$supplierName = $supplier['supplier_name']; // Get the supplier's name

// Process the form to update price
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'], $_POST['new_price'])) {
    $productId = $_POST['product_id'];
    $newPrice = $_POST['new_price'];

    // Fetch current price to compare
    $stmt = $con->prepare("SELECT product_name, price_per_unit FROM product WHERE product_id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();

    if ($product) {
        $currentPrice = $product['price_per_unit'];
        $productName = $product['product_name'];

        if ($newPrice != $currentPrice) {
            // Update price
            $updateStmt = $con->prepare("UPDATE product SET price_per_unit = ? WHERE product_id = ?");
            $updateStmt->bind_param("di", $newPrice, $productId);
            $updateStmt->execute();
        
            // Send Telegram notification
            $change = $newPrice > $currentPrice ? 'increased' : 'decreased';
            $message = "Supplier '$supplierName' has updated the price of '$productName' (ID: $productId). The price has been $change to RM $newPrice.";
            sendTelegramNotification($message, $token, $chat_Id);
        
            // Redirect back to refresh table
            header("Location: supplier_dashboard.php");
            exit();
        }
    } else {
        echo "<p>Product not found.</p>";
    }
}

// Fetch products supplied by this supplier
$stmt = $con->prepare("SELECT * FROM `product` WHERE supplier_id = ?");
$stmt->bind_param("i", $supplierId);
$stmt->execute();
$result = $stmt->get_result();

// Fetch orders for this supplier
$orderStmt = $con->prepare("
    SELECT o.order_id, o.date_ordered, o.order_quantity, o.total_price, p.product_name 
    FROM orders o 
    JOIN product p ON o.product_id = p.product_id 
    WHERE p.supplier_id = ?
");
$orderStmt->bind_param("i", $supplierId);
$orderStmt->execute();
$orderResult = $orderStmt->get_result();

// Get the number of orders for the badge
$orderCount = $orderResult->num_rows;

// Handle AJAX request to fetch order data
if (isset($_POST['fetch_orders']) && $_POST['fetch_orders'] === 'true') {
    $orders = [];
    while ($orderRow = $orderResult->fetch_assoc()) {
        $orders[] = $orderRow;
    }
    echo json_encode($orders);
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Dashboard</title>
    <style>
        /* Your existing CSS code here */

        h1, h2, h3 {
            text-align: center;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px, 20px;
            background-color: #333;
            color: white;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            transition: background-color 0.3s;
        }
                    

        .navbar a:hover {
            background-color: #575757;
            border-radius: 4px;
        }

        .navbar a:hover {
            background-color: #575757;
            border-radius: 4px;
        }

        navbar .left-links {
        display: flex;
        gap: 10px; /* Adds spacing between links */
        }

        .navbar .right-links {
        display: flex;
        gap: 10px; /* Adds spacing between links */
        }

        .logout {
            color: right;
            font-weight: bold;
        }

        .container {
            padding: 20px;
        }

        .content {
            margin: 20px 0;
        }

        table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
}

table, th, td {
    border: 1px solid #e0e0e0; /* Soft gray border */
}

th, td {
    padding: 12px;
    font-size: 14px;
    color: #333; /* Darker text for better contrast */
}

th {
    background-color: #4CAF50; /* Soft green for the header row */
    color: white; /* White text for the header */
    font-weight: bold; /* Bold header text for emphasis */
}

tr:nth-child(even) {
    background-color: #f9f9f9; /* Light gray for even rows */
    text-align: center;
}

tr:nth-child(odd) {
    background-color: #ffffff; /* White for odd rows for contrast */
    text-align: center;
}

tr:hover {
    background-color: #eaeaea; /* Slight darkening on hover for interactivity */
}

td {
    color: #555; /* Lighter gray for regular table cell text */
}




        .btn {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #45a049;
        }

        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 10px 0;
        }

        /* Smooth transition for order details */
        #order-section {
            display: none;
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        #order-section.show {
            display: block;
            opacity: 1;
        }

        #back-button {
        display: block;
        margin: 20px auto 0; /* Adds space above the button and centers it */
        padding: 10px 20px;
        background-color: #4CAF50;
        color: white;
        border: none;
        cursor: pointer;
        }

        .badge {
            background-color: red;
            border-radius: 50%;
            color: white;
            padding: 5px 10px;
            font-size: 14px;
        }

        /* New Styles for Centering Pie Chart and Small Table */
        .chart-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin: 20px;
            height: auto;
        }

        .chart-container canvas {
            flex: 0 0 auto;
            width: 300px;
            height: 300px;
        }
        .stock-table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
}

.stock-table, .stock-table th, .stock-table td {
    border: 1px solid #ddd; /* Lighter border color for subtle separation */
}

.stock-table th, .stock-table td {
    padding: 8px;
    text-align: center;
    font-size: 14px;
    color: #333; /* Dark gray text for better readability */
}

.stock-table th {
    background-color: #d3d3d3; /* Light grey for the header row */
    font-weight: 600; /* Slightly bolder text for headers */
    color: #555; /* Darker text for contrast against grey background */
}

.stock-table tr:nth-child(even) {
    background-color: #f9f9f9; /* Very light gray for even rows */
}

.stock-table tr:hover {
    background-color: #eaeaea; /* Slight darkening on hover for subtle interactivity */
}

.stock-table td {
    background-color: #ffffff; /* Keep table cells white */
}

.stock-table td,
.stock-table th {
    border-bottom: 1px solid #ccc; /* Light gray line between rows */
}

    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    
    <div class="navbar">
        <div class="left-links">
        <a href="supplier_stock_entry.php">Stock Entry</a>
        <a href="#" id="order-link">
            Order
            <?php if ($orderCount > 0): ?>
                <span id="order-badge" class="badge"><?php echo $orderCount; ?></span>
            <?php endif; ?>
        </a>
        <a href="report_supplier.php">Generate Report</a>
        <a href="user_manual.php">User Manual</a>
        </div>
        <div class="right-links">
        <a href="editprof_supplier.php">Edit Profile</a>
        <a href="logout.php" class="logout">Logout</a>
    </div>
</div>

    <div class="container">
         <!-- Order Details Section (initially hidden) -->
    <div id="order-section">
        <h2>Order Details</h2>
        <table id="order-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Date Ordered</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Total Price</th>
                </tr>
            </thead>
            <tbody>
                <!-- Orders will be dynamically inserted here -->
            </tbody>
        </table>
        <button id="back-button" class="btn">Close Order</button>
    </div>
        <h1>Welcome, <?php echo $username; ?>!</h1>
                <!--Pie chart -->
        <div class ="chart-container">
        <canvas id="supplierStockChart"></canvas>

        <!-- Table -->
<table class="stock-table">
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Quantity In Stock</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($productNames as $index => $productName) { ?>
            <tr>
                <td><?php echo $productName; ?></td>
                <td><?php echo $stockQuantities[$index]; ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
        <h3>Product List</h3>
        <table>
            <thead>
                <tr>
                    <th>Product ID</th>
                    <th>Product Name</th>
                    <th>Price (RM)</th>
                    <th>Update Price</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['product_id']; ?></td>
                    <td><?php echo $row['product_name']; ?></td>
                    <td><?php echo $row['price_per_unit']; ?></td>
                    <td>
                        <form method="POST" action="supplier_dashboard.php">
                            <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                            <input type="number" name="new_price" step="0.01" min="0" required placeholder="New Price">
                            <button type="submit" class="btn">Update Price</button>
                        </form>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    
    
   
</body>
<script>

// Chart.js code for rendering the pie chart
const ctx = document.getElementById('supplierStockChart').getContext('2d');
    const supplierStockChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($productNames); ?>,
            datasets: [{
                label: 'Stock Availability',
                data: <?php echo json_encode($stockQuantities); ?>,
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'
                ],
            }]
        },
        options: {
            responsive: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Stock Availability per Product'
                }
            }
        }
    });

// JavaScript code for fetching and displaying orders
const orderLink = document.getElementById('order-link');
const orderSection = document.getElementById('order-section');
const backButton = document.getElementById('back-button');
const orderTableBody = document.querySelector('#order-table tbody');

// Fetch and show order details when the "Order" link is clicked
orderLink.addEventListener('click', function () {
    // Remove the order badge
    const badge = document.getElementById('order-badge');
    if (badge) {
        badge.remove();
    }

    // Fetch orders dynamically using AJAX
    fetch('supplier_dashboard.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'fetch_orders=true'
    })
    .then(response => response.json())
    .then(data => {
        orderTableBody.innerHTML = ''; // Clear existing rows
        data.forEach(order => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${order.order_id}</td>
                <td>${order.date_ordered}</td>
                <td>${order.product_name}</td>
                <td>${order.order_quantity}</td>
                <td>${order.total_price}</td>
            `;
            orderTableBody.appendChild(row);
        });

        // Show the order section
        orderSection.classList.add('show');
    })
    .catch(error => console.error('Error fetching orders:', error));
});

// Return to the dashboard when clicking the back button
backButton.addEventListener('click', function () {
    orderSection.classList.remove('show'); // Hide the order section
});
</script>
 <!-- Footer -->
 <footer>
        <p>© 2024 HoudStock. All rights reserved.</p>
    </footer>
</html>
