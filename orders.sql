CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    order_quantity INT,
    total_price DECIMAL(10, 2),
    supplier_id INT,  -- This is the supplier_id column
    date_ordered DATETIME,
    FOREIGN KEY (product_id) REFERENCES product(product_id),
    FOREIGN KEY (supplier_id) REFERENCES supplier(supplier_id)  -- Linking to the supplier table
);