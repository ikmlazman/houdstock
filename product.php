<?php
session_start();
require_once 'connect.php';

if (isset($_POST['submit'])) {
    $product_name = $_POST['product_name'];
    $category = $_POST['category'];
    $quantity_in_stock = $_POST['quantity_in_stock'];
    $supplier_id = $_POST['supplier_id'];
    $price_per_unit = $_POST['price_per_unit'];
    $last_restocked_date = $_POST['last_restocked_date'];
    $date_added = $_POST['date_added'];

    if (!empty($product_name) && !empty($category) && !empty($quantity_in_stock)
        && !empty($supplier_id) && !empty($price_per_unit) &&
        !empty($last_restocked_date) && !empty($date_added)) {

        $stmt = $con->prepare("INSERT INTO `product` 
            (product_name, category, quantity_in_stock, 
            supplier_id, price_per_unit, last_restocked_date, date_added) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("sssssss", $product_name, $category, $quantity_in_stock, 
        $supplier_id, $price_per_unit, 
        $last_restocked_date, $date_added);
    
        if ($stmt->execute()) {
            header('Location: displayproduct.php');
            exit();
        } else {
            die("Error: " . $stmt->error);
        }
        
        $stmt->close();
    } else {
        echo "All fields are required!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Insert Product</title>
    <style>
       /* General Body Styling */
body {
    font-family: Arial, sans-serif;
    background-color: #f8f9fa;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh; /* Full viewport height */
    margin: 0; /* Remove default margin */
}

/* Form Container */
.form-container {
    width: 100%;
    max-width: 400px; /* Limit max width */
    background-color: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Header Styling */
h2 {
    text-align: center;
    margin-bottom: 20px;
    color: #333; /* Dark text color */
}

/* Form Group Styling */
.form-group {
    margin-bottom: 15px;
}

/* Label Styling */
label {
    display: block;
    font-size: 14px;
    color: #555; /* Medium text color */
    margin-bottom: 5px;
}

/* Input, Select, and Button Styling */
input[type="text"], 
input[type="date"], 
select, 
.btn {
    width: 100%; /* Full width */
    padding: 10px;
    margin-top: 5px;
    border: 1px solid #ddd; /* Light border */
    border-radius: 5px;
    font-size: 14px;
}

/* Button Specific Styling */
.btn {
    background-color: #007bff; /* Primary blue */
    color: white;
    border: none;
    cursor: pointer;
    transition: background-color 0.3s; /* Smooth hover effect */
}

.btn:hover {
    background-color: #0056b3; /* Darker blue on hover */
}

/* Back Button Styling */
.back-btn {
    text-align: center;
    margin-top: 20px;
}

.back-btn a {
    display: inline-block;
    padding: 10px 20px;
    background-color: #007bff; /* Primary blue */
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-size: 14px;
    transition: background-color 0.3s; /* Smooth hover effect */
}

.back-btn a:hover {
    background-color: #0056b3; /* Darker blue on hover */
}

    </style>
</head>
<body>
    
    <div class="form-container">
        <form method="post">
            <br>
            <br>
            <div class="form-group">
                <label>Product Name</label>
                <input type="text" placeholder="Enter product name" name="product_name" required>
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category" required>
                    <option value="beverage">Beverage</option>
                    <option value="food">Food</option>
                    <option value="condiment">Condiment</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="form-group">
                <label>Quantity in Stock</label>
                <input type="text" placeholder="Enter quantity in stock" name="quantity_in_stock" required>
            </div>
            <div class="form-group">
                <label>Supplier Name</label>
                <select name="supplier_id" required>
                    <option value="" disabled selected>Select a supplier</option>
                    <?php
                    $stmt = $con->prepare("SELECT supplier_id, supplier_name FROM supplier");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . $row['supplier_id'] . "'>" . $row['supplier_name'] . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label>Price Per Unit</label>
                <input type="text" placeholder="Enter price per unit" name="price_per_unit" required>
            </div>
            <div class="form-group">
                <label>Last Restocked Date</label>
                <input type="date" name="last_restocked_date" required>
            </div>
            <div class="form-group">
                <label>Date Added</label>
                <input type="date" name="date_added" required>
            </div>
            <button type="submit" class="btn" name="submit">Submit</button>
        </form>

        <div class="back-btn">
    <?php 
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        echo '<a href="admin_dashboard.php">Back to Admin Dashboard</a>';
    } else {
        echo '<a href="employee_dashboard.php">Back to User Dashboard</a>';
    }
    ?>
</div>

       
</div>
</body>
</html>
