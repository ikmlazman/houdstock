<?php
// Connect to the database
require_once 'connect.php';

// Query to fetch product data
$sql = "SELECT product_name, quantity_in_stock FROM product";
$result = mysqli_query($con, $sql);

$data = array();
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

// Return the data in JSON format
header('Content-Type: application/json');
echo json_encode($data);
?>
