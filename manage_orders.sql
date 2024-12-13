CREATE TABLE IF NOT EXISTS orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    supplier_name VARCHAR(100),
    status ENUM('Pending', 'Shipped', 'Delivered') DEFAULT 'Pending',
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE SET NULL
);