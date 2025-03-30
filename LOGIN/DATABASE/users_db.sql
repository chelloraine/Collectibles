CREATE DATABASE userlist_db;
USE userlist_db;


-- Create users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    contact VARCHAR(15) NOT NULL,
    birthday DATE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    address VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    zip VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

ALTER TABLE users ADD COLUMN role ENUM('user', 'admin') DEFAULT 'user';
UPDATE users SET first_name='Mechelle' WHERE username='admin';
UPDATE users SET last_name='Monsale' WHERE username='admin';
UPDATE users SET email='mechelleloraine.monsale@letran.edu.ph' WHERE username='admin';

INSERT INTO users (username, password, role) 
VALUES ('admin', '$2y$10$XXck90mju15XPWCtDWbGj.SriwAbM7frXqwUoLl8D3RPqo.Y3PvF2', 'admin');


UPDATE users SET role = 'admin' WHERE username = 'admin';

SELECT * FROM users WHERE username='admin';

DELETE FROM users 
WHERE id=2;
SELECT * FROM users;

ALTER TABLE users ADD COLUMN status ENUM('active', 'inactive', 'banned') NOT NULL DEFAULT 'active';
SHOW COLUMNS FROM users LIKE 'status';
SELECT id, first_name, last_name, email, username, role, status FROM users;
ALTER TABLE users ADD COLUMN profile_picture VARCHAR(255) NULL;
ALTER TABLE addresses ADD COLUMN is_default TINYINT(1) NOT NULL DEFAULT 0;

DESCRIBE users;

