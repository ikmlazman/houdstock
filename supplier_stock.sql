CREATE TABLE supplier_stock (
    supplier_stock_id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity_in_stock INT DEFAULT 0, -- Default quantity set to 0
    date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Dummy date column
    FOREIGN KEY (supplier_id) REFERENCES supplier(supplier_id),
    FOREIGN KEY (product_id) REFERENCES product(product_id)
);
