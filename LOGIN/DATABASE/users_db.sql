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
ALTER TABLE users ADD COLUMN role ENUM('user', 'admin') DEFAULT 'user';
UPDATE users SET first_name='Mechelle' WHERE username='admin';
UPDATE users SET last_name='Monsale' WHERE username='admin';
UPDATE users SET email='mechelleloraine.monsale@letran.edu.ph' WHERE username='admin';

INSERT INTO users (username, password, role) 
VALUES ('admin', '$2y$10$XXck90mju15XPWCtDWbGj.SriwAbM7frXqwUoLl8D3RPqo.Y3PvF2', 'admin');

SELECT * FROM users WHERE username='admin';

DELETE FROM users 
WHERE id=2;
SELECT * FROM users;

ALTER TABLE users ADD COLUMN status ENUM('active', 'inactive', 'banned') NOT NULL DEFAULT 'active';
