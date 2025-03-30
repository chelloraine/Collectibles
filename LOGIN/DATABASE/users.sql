CREATE DATABASE user_db;

USE user_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    email VARCHAR(100),
    username VARCHAR(50),
    password VARCHAR(255) 
);

ALTER TABLE users ADD COLUMN status ENUM('active', 'inactive', 'banned') NOT NULL DEFAULT 'active';
