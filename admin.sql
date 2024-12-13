CREATE TABLE admin (
    admin_id INT PRIMARY KEY AUTO_INCREMENT,         -- Unique ID for each admin
    username VARCHAR(50) NOT NULL,                   -- Admin username
    password VARCHAR(50) NOT NULL,                  -- Hashed password
    email VARCHAR(100) NOT NULL,                     -- Email for password recovery/notifications
    role VARCHAR(20) DEFAULT 'admin',                -- Role, in case you have multiple admin levels
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,  -- Record creation date
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP  -- Last update date
);


--
-- Dumping data for table `user`
--

INSERT INTO `admin` (`username`, `password`, `email`, `role`) 
VALUES ('admin', 'ikmal', 'azmanikmal@gmail.com', 'admin');


