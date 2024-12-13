<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HoudStock - Inventory Manager</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* General Styles */
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: #f4f4f4;
            display: flex;
            overflow-x: hidden;
        }

        /* Navigation Bar */
        nav {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            background: #2c3e50;
            color: #fff;
            display: flex;
            flex-direction: column;
            padding: 20px;
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.2);
        }

        nav ul {
            list-style: none;
            padding: 0;
        }

        nav ul li {
            margin: 20px 0;
        }

        nav ul li a {
            text-decoration: none;
            color: #fff;
            font-size: 1rem;
            display: flex;
            align-items: center;
            padding: 10px 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        nav ul li a:hover {
            background: #34495e;
            transform: translateX(5px);
        }

        nav ul li a span.icon {
            margin-right: 10px;
        }

        /* Main Content */
        .container {
            margin-left: 270px;
            padding: 20px;
            width: calc(100% - 270px);
        }

        .header {
            background: #2c3e50;
            color: white;
            text-align: center;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
            animation: fadeIn 1s ease-in-out;
        }

        .header h1 {
            font-size: 2.5rem;
            margin: 0;
        }

        .header p {
            font-size: 1.2rem;
            margin: 10px 0 0;
        }

        /* Dashboard Card */
        .card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(8px);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
            animation: fadeUp 1s ease;
        }

        .card h2 {
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .graph-container {
            position: relative;
            height: 400px;
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            nav {
                width: 100%;
                height: auto;
                flex-direction: row;
                justify-content: space-around;
            }

            .container {
                margin-left: 0;
                width: 100%;
            }
        }

                    /* Info Button */
            .info-icon {
                margin-top: auto; /* Push the button to the bottom of the navbar */
                width: 40px; /* Small width */
                height: 40px; /* Small height */
                display: flex;
                justify-content: center;
                align-items: center;
                border-radius: 50%; /* Makes it circular */
                background-color: #3498db; /* Light blue color */
                color: white; /* White text/icon */
                font-size: 18px; /* Font size for the icon */
                border: none; /* Removes the border */
                transition: all 0.3s ease-in-out;
            }

            .info-icon:hover {
                background-color: #2980b9; /* Darker blue on hover */
                transform: scale(1.1); /* Slight zoom effect */
            }

            .info-icon span {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .nav-tabs .nav-link {
            color: #34495e;
            border: 1px solid transparent;
            border-radius: 8px;
            padding: 10px 20px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

            .nav-tabs .nav-link:hover {
            background-color: #ecf0f1;
            color: #2c3e50;
        }

            .nav-tabs .nav-link.active {
            background-color: #3498db;
            color: white;
            border: none;
        }


    </style>
</head>
<body>
    <nav>
        <ul>
        <li><a href="index.php"><span class="icon">🏠</span> Home</a></li>
        <li><a href="login1.php">
                <span class="icon">🔑</span>
                <span class="text">Login</span>
            </a></li>
            <li><a href="contact.php">
                <span class="icon">📞</span>
                <span class="text">Contact</span>
            </a></li>
            <li><a href="aboutus.php">
                <span class="icon">ℹ️</span>
                <span class="text">About</span>
            </a></li>
        </ul>
        <!-- Info Button -->
        <button class="info-icon" data-bs-toggle="modal" data-bs-target="#userManualModal">
       ℹ️
        </button>
    </nav>
    <div class="container">
        <header class="header">
            <h1>HoudStock</h1>
            <p>Effortless Inventory, Instant Alerts – Powered by Telegram!</p>
        </header>
        <div class="card">
            <h2>Stock Overview</h2>
            <div class="graph-container">
                <canvas id="myChart"></canvas>
            </div>
        </div>
    </div>
<<!-- Modal -->
<div class="modal fade" id="userManualModal" tabindex="-1" aria-labelledby="userManualLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userManualLabel">User Manual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Tabs Navigation -->
                <ul class="nav nav-tabs" id="manualTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="admin-tab" data-bs-toggle="tab" data-bs-target="#admin" type="button" role="tab" aria-controls="admin" aria-selected="true">Admin</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="employee-tab" data-bs-toggle="tab" data-bs-target="#employee" type="button" role="tab" aria-controls="employee" aria-selected="false">Employee</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="supplier-tab" data-bs-toggle="tab" data-bs-target="#supplier" type="button" role="tab" aria-controls="supplier" aria-selected="false">Supplier</button>
                    </li>
                </ul>

                <!-- Tabs Content -->
                <div class="tab-content" id="manualTabsContent">
                    <div class="tab-pane fade show active p-3" id="admin" role="tabpanel" aria-labelledby="admin-tab">
                        <h5>Admin Manual</h5>
                        <p><strong>Admin Dashboard Overview</strong><br>
The Admin Dashboard is the central hub for managing the inventory system. It allows admins to view, edit, and manage product details and supplier information.</p>

<p><strong>How to Access:</strong><br>
Log in with your admin credentials. Upon successful login, you will be redirected to the Admin Dashboard.</p>

<p><strong>Dashboard Features:</strong></p>
<ul>
    <li><strong>View Products:</strong><br>
    See a list of all products in the inventory. Columns include Product ID, Product Name, Supplier Name, Stock Quantity, and Price per Unit.</li>
    
    <li><strong>Edit Product Details:</strong><br>
    Click the "Edit" button next to a product to update its information (e.g., price or name).</li>
    
    <li><strong>Delete Products:</strong><br>
    Click the "Delete" button to remove a product from the inventory.</li>
    
    <li><strong>Supplier Management:</strong><br>
    View suppliers associated with each product. Update or modify supplier details as needed.</li>

    <li><strong>Make Orders to Suppliers:</strong><br>
    Create orders to suppliers by selecting the products you need to restock and entering the required quantities. Once the order is submitted, the supplier will be notified.</li>
    
    <li><strong>Add New Products:</strong><br>
    Use the form to add new products to the inventory. You will be prompted to enter product details such as name, price, and initial stock quantity.</li>

    <li><strong>Generate Report:</strong><br>
    Admins can generate reports for various actions including product additions, orders, stock updates, and supplier interactions. Reports include timestamps and relevant data to track activities efficiently.</li>
</ul>


<p><strong>Additional Notes:</strong><br>
Only users with the "admin" role can access this page. Any unauthorized access attempts will redirect the user to the login page. Ensure you have a stable internet connection for database interactions.</p>

                    </div>
                    <div class="tab-pane fade p-3" id="employee" role="tabpanel" aria-labelledby="employee-tab">
                        <h5>Employee Manual</h5>
                        <p><strong>Employee Dashboard Overview</strong><br>
The Employee Dashboard is designed for employees to view and monitor inventory details efficiently.</p>

<p><strong>How to Access:</strong><br>
Log in with your employee credentials. If authentication is successful, you will be redirected to the Employee Dashboard.</p>

<p><strong>Dashboard Features:</strong></p>
<ul>
    <li><strong>View Products:</strong><br>
    A table displays a list of all products in the inventory. Columns include Product ID, Product Name, Supplier Name, Stock Quantity, and Price per Unit.</li>
    
    <li><strong>Real-Time Updates:</strong><br>
    The dashboard fetches live data from the inventory database to ensure accurate information.</li>
    
    <li><strong>Add New Products:</strong><br>
    Use the form to add new products to the inventory. You will be prompted to enter product details such as name, price, and initial stock quantity.</li>

    <li><strong>Restricted Actions:</strong><br>
    Employees can generate reports but cannot make orders..</li>
</ul>

<p><strong>Additional Notes:</strong><br>
Only users with the "employee" role can access this page. Unauthorized access attempts will redirect the user to the login page. For any changes or updates to inventory data, contact your admin.</p>

                    </div>
                    <div class="tab-pane fade p-3" id="supplier" role="tabpanel" aria-labelledby="supplier-tab">
                        <h5>Supplier Manual</h5>
                        <p><strong>Supplier Dashboard Overview</strong><br>
The Supplier Dashboard allows suppliers to view and manage the products they supply to the café. It provides personalized data based on the logged-in supplier's account.</p>

<p><strong>How to Access:</strong><br>
Log in with your supplier credentials. If authentication is successful, you will be redirected to the Supplier Dashboard.</p>

<p><strong>Dashboard Features:</strong></p>
<ul>
    <li><strong>View Products You Supply:</strong><br>
    The dashboard displays a table containing:
    <ul>
        <li>Product ID</li>
        <li>Product Name</li>
        <li>Quantity Supplied</li>
        <li>Price per Unit</li>
    </ul>
    </li>
    
    <li><strong>Manage Pricing:</strong><br>
    Update prices for the products you supply directly through the dashboard.</li>
    
    
    <li><strong>Real-Time Updates:</strong><br>
    The data shown is dynamically fetched, ensuring it is up-to-date.</li>

    <li><strong>Update Product Stock:</strong><br>
    Use the form to update stock levels for your inventory. You can enter quantity, and submit them to the system for addition to the inventory database.</li>

    <li><strong>Generate Report:</strong><br>
    Suppliers can generate order reports based on the products they supply. The reports can include details about total price and quantity over a specific time period.</li>
</ul>


<p><strong>Additional Notes:</strong><br>
Only users with the "supplier" role can access this page. Unauthorized access attempts will redirect the user to the login page. Ensure the supplier_id is set during login. If you encounter an error regarding the supplier_id, log out and log back in.</p>

                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('myChart').getContext('2d');
        let myChart;

        function fetchData() {
            fetch('fetch_data.php')
                .then(response => response.json())
                .then(data => {
                    const productNames = data.map(item => item.product_name);
                    const quantities = data.map(item => item.quantity_in_stock);

                    if (!myChart) {
                        myChart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: productNames,
                                datasets: [{
                                    label: 'Quantity in Stock',
                                    data: quantities,
                                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                    borderColor: 'rgba(75, 192, 192, 1)',
                                    borderWidth: 2
                                }]
                            },
                            options: {
                                animations: {
                                    tension: {
                                        duration: 1000,
                                        easing: 'easeInOutBounce',
                                        from: 0.3,
                                        to: 0.5,
                                        loop: true
                                    }
                                },
                                responsive: true,
                                maintainAspectRatio: false
                            }
                        });
                    } else {
                        myChart.data.labels = productNames;
                        myChart.data.datasets[0].data = quantities;
                        myChart.update();
                    }
                })
                .catch(error => console.error('Error fetching data:', error));
        }

        setInterval(fetchData, 5000);
        fetchData();
    </script>
</body>
</html>
