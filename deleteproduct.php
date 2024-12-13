<?php
require_once 'connect.php';

if (isset($_GET['deleteproduct_id'])) {
    $product_id = $_GET['deleteproduct_id'];

    // Delete records from supplier_stock table that reference the product
    $sql = "DELETE FROM supplier_stock WHERE product_id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();

    // Now delete the product from the product table
    $sql = "DELETE FROM product WHERE product_id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $product_id);

    if ($stmt->execute()) {
        header('Location: displayproduct.php'); // Redirect after successful deletion
    } else {
        die("Error deleting product: " . $stmt->error);
    }
} else {
    die("No product ID specified.");
}
?>
