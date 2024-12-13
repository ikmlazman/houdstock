CREATE TABLE employee (
    employee_id INT PRIMARY KEY AUTO_INCREMENT,           -- Unique ID for each employee
    name VARCHAR(100) NOT NULL,                           -- Employee's full name
    username VARCHAR(50) NOT NULL UNIQUE,                 -- Username for login
    password VARCHAR(50) NOT NULL,                        -- Plain text password (if not hashed)
    email VARCHAR(100) NOT NULL,                          -- Employee's email
    phone VARCHAR(15),                                    -- Contact phone number
    position VARCHAR(50),                                 -- Job title or position
    date_of_hire DATE,                                    -- Hiring date
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,       -- Record creation date
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP -- Last update date
);

INSERT INTO employee (name, username, password, email, phone, position, date_of_hire)
VALUES ('Nur Ahmad Ikmal', 'ikmal', 'ikmal', 'azmanikmal@gmail.com', '017-972 0538', 'Manager', '2023-10-01');
