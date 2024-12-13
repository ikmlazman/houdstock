<?php
require_once 'connect.php';
$product_id = $_GET['updateproduct_id'];


// Fetch product data
$sql = "SELECT * FROM `product` WHERE product_id = $product_id";
$result = mysqli_query($con, $sql);
$row = mysqli_fetch_assoc($result);

// Initialize variables
$product_name = $row['product_name'];
$category = $row['category'];
$quantity_in_stock = $row['quantity_in_stock'];
$price_per_unit = $row['price_per_unit'];
$last_restocked_date = $row['last_restocked_date'];
$date_added = $row['date_added'];

if (isset($_POST['submit'])) {
    // Retrieve form values
    $product_name = $_POST['product_name'];
    $category = $_POST['category'];
    $quantity_in_stock = $_POST['quantity_in_stock']; // Get the updated value
    $price_per_unit = $_POST['price_per_unit'];
    $last_restocked_date = $_POST['last_restocked_date'];
    $date_added = $_POST['date_added'];

    // Check if all fields are filled
    if (!empty($product_name) && !empty($category) && !empty($quantity_in_stock)
        && !empty($price_per_unit) && !empty($last_restocked_date)
        && !empty($date_added)) {
        
        // Update query
        $sql = "UPDATE `product` SET 
                    product_name='$product_name', 
                    category='$category', 
                    quantity_in_stock='$quantity_in_stock', 
                    price_per_unit='$price_per_unit', 
                    last_restocked_date='$last_restocked_date',
                    date_added='$date_added'
                WHERE product_id = $product_id";
        
        $result = mysqli_query($con, $sql);

        // Check for successful update
        if ($result) {
            header('Location: displayproduct.php');
            exit();
        } else {
            die("Error updating record: " . mysqli_error($con));
        }
    } else {
        echo "All fields are required!";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
     <style> 
body {
            background-image: url('background.jpg'); /* Make sure the image is in the correct path */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            color: white; /* Text color to contrast with the background */
        }


</style>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css">
    <title>Update Product</title>
</head>

<body>
    <div class="container my-5">
        <form method="post">
            <div class="form-group">
                <label>Product Name</label>
                <input type="text" class="form-control" placeholder="Enter product name" name="product_name" autocomplete="off" value="<?php echo $product_name; ?>">
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category" class="form-control" required>
                    <option value="beverage" <?php echo $category == 'beverage' ? 'selected' : ''; ?>>Beverage</option>
                    <option value="food" <?php echo $category == 'food' ? 'selected' : ''; ?>>Food</option>
                    <option value="condiment" <?php echo $category == 'condiment' ? 'selected' : ''; ?>>Condiment</option>
                    <option value="other" <?php echo $category == 'other' ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
            <div class="form-group">
                <label>Quantity in Stock</label>
                <input type="text" class="form-control" placeholder="Enter quantity in stock" name="quantity_in_stock" autocomplete="off" value="<?php echo $quantity_in_stock; ?>">
            </div>              
            <div class="form-group">
                <label>Price Per Unit</label>
                <input type="text" class="form-control" placeholder="Enter price per unit" name="price_per_unit" autocomplete="off" value="<?php echo $price_per_unit; ?>">
            </div>
            <div class="form-group">
                <label>Last Restocked Date</label>
                <input type="date" class="form-control" name="last_restocked_date" autocomplete="off" value="<?php echo isset($last_restocked_date) ? date('Y-m-d', strtotime($last_restocked_date)) : ''; ?>">
            </div>
            <div class="form-group">
                <label>Date Added</label>
                <input type="date" class="form-control" name="date_added" autocomplete="off" value="<?php echo isset($date_added) ? date('Y-m-d', strtotime($date_added)) : ''; ?>">
            </div>
            <button type="submit" class="btn btn-primary" name="submit">Update</button>
        </form>
    </div>
</body>
</html>
