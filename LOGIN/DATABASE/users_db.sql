CREATE DATABASE userlist_db;
USE userlist_db;
DROP DATABASE userlist_db;

-- Admin Table (Manages Everything)
CREATE TABLE Admins (
    Admin_ID INT(11) AUTO_INCREMENT PRIMARY KEY,
    Admin_Name VARCHAR(100) NOT NULL,
    Admin_Email VARCHAR(50) UNIQUE NOT NULL,
    Admin_Password VARCHAR(255) NOT NULL
);

-- Customers Table
CREATE TABLE Customers (
    Customer_ID INT(11) AUTO_INCREMENT PRIMARY KEY,
    First_Name VARCHAR(50) NOT NULL,
    Last_Name VARCHAR(50) NOT NULL,
    Contact_Number VARCHAR(15) UNIQUE NOT NULL,
    Customer_Email VARCHAR(50) UNIQUE NOT NULL,
    Date_Of_Birth DATE NOT NULL,
    Username VARCHAR(50) UNIQUE NOT NULL,
    Password VARCHAR(255) NOT NULL
);
SELECT * FROM Customers;

-- Customer Addresses (Multiple Addresses with Default Option)
CREATE TABLE Customer_Addresses (
    Address_ID INT(11) AUTO_INCREMENT PRIMARY KEY,
    Customer_ID INT(11) NOT NULL,
    Address VARCHAR(255) NOT NULL,
    Is_Default BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (Customer_ID) REFERENCES Customers(Customer_ID) ON DELETE CASCADE
);

-- Suppliers Table
CREATE TABLE Suppliers (
    Supplier_ID INT(11) AUTO_INCREMENT PRIMARY KEY,
    Supplier_Name VARCHAR(100) NOT NULL,
    Address VARCHAR(255) NOT NULL,
    Contact_Number VARCHAR(15) NOT NULL,
    Supplier_Email VARCHAR(50) NOT NULL
);

-- Product Categories Table
CREATE TABLE Product_Categories (
    ProductType_ID INT(11) AUTO_INCREMENT PRIMARY KEY,
    Category_Name VARCHAR(50) NOT NULL UNIQUE
);

-- Products Table
CREATE TABLE Products (
    Product_ID INT(11) AUTO_INCREMENT PRIMARY KEY,
    Product_Name VARCHAR(50) NOT NULL,
    Supplier_ID INT(11) NOT NULL,
    Product_Quantity INT(10) NOT NULL,
    ProductType_ID INT(11) NOT NULL,
    FOREIGN KEY (Supplier_ID) REFERENCES Suppliers(Supplier_ID) ON DELETE CASCADE,
    FOREIGN KEY (ProductType_ID) REFERENCES Product_Categories(ProductType_ID) ON DELETE CASCADE
);

-- Product Images (Storing Multiple Images for Each Product)
CREATE TABLE Product_Images (
    Image_ID INT(11) AUTO_INCREMENT PRIMARY KEY,
    Product_ID INT(11) NOT NULL,
    Image_Path VARCHAR(255) NOT NULL,
    FOREIGN KEY (Product_ID) REFERENCES Products(Product_ID) ON DELETE CASCADE
);

-- Orders Table
CREATE TABLE Orders (
    Order_ID INT(11) AUTO_INCREMENT PRIMARY KEY,
    Customer_ID INT(11) NOT NULL,
    Admin_ID INT(11) NOT NULL,
    Payment_ID INT(11) NOT NULL,
    Order_Status ENUM('Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled') NOT NULL DEFAULT 'Pending',
    FOREIGN KEY (Customer_ID) REFERENCES Customers(Customer_ID) ON DELETE CASCADE,
    FOREIGN KEY (Admin_ID) REFERENCES Admins(Admin_ID) ON DELETE CASCADE,
    FOREIGN KEY (Payment_ID) REFERENCES Payments(Payment_ID) ON DELETE CASCADE
);

-- Order Products (Handles Multiple Products in an Order)
CREATE TABLE Order_Products (
    Order_ID INT(11) NOT NULL,
    Product_ID INT(11) NOT NULL,
    Order_Quantity INT(11) NOT NULL,
    PRIMARY KEY (Order_ID, Product_ID),
    FOREIGN KEY (Order_ID) REFERENCES Orders(Order_ID) ON DELETE CASCADE,
    FOREIGN KEY (Product_ID) REFERENCES Products(Product_ID) ON DELETE CASCADE
);

-- Payment Methods Table
CREATE TABLE Payment_Methods (
    PaymentMethod_ID INT(11) AUTO_INCREMENT PRIMARY KEY,
    Method_Name VARCHAR(50) NOT NULL UNIQUE
);

-- Payments Table
CREATE TABLE Payments (
    Payment_ID INT(11) AUTO_INCREMENT PRIMARY KEY,
    Customer_ID INT(11) NOT NULL,
    Order_ID INT(11) NOT NULL,
    PaymentMethod_ID INT(11) NOT NULL,
    Payment_Source VARCHAR(50) NOT NULL,
    FOREIGN KEY (Customer_ID) REFERENCES Customers(Customer_ID) ON DELETE CASCADE,
    FOREIGN KEY (Order_ID) REFERENCES Orders(Order_ID) ON DELETE CASCADE,
    FOREIGN KEY (PaymentMethod_ID) REFERENCES Payment_Methods(PaymentMethod_ID) ON DELETE CASCADE
);

-- Shipping Table
CREATE TABLE Shipping (
    Shipping_ID INT(11) AUTO_INCREMENT PRIMARY KEY,
    Order_ID INT(11) NOT NULL,
    Address_ID INT(11) NOT NULL,
    Tracking_Number VARCHAR(50) UNIQUE NOT NULL,
    Shipping_Status ENUM('Pending', 'Shipped', 'Delivered') NOT NULL DEFAULT 'Pending',
    Estimated_Delivery DATE NOT NULL,
    FOREIGN KEY (Order_ID) REFERENCES Orders(Order_ID) ON DELETE CASCADE,
    FOREIGN KEY (Address_ID) REFERENCES Customer_Addresses(Address_ID) ON DELETE CASCADE
);

-- Admin Activity Log (Tracks Admin Actions)
CREATE TABLE Admin_Activity_Log (
    Log_ID INT(11) AUTO_INCREMENT PRIMARY KEY,
    Admin_ID INT(11) NOT NULL,
    Action VARCHAR(255) NOT NULL,
    Timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (Admin_ID) REFERENCES Admins(Admin_ID) ON DELETE CASCADE
);
