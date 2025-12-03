# TO SETUP ON A NEW MACHINE:

## 1. Install XAMPP
## 2. Install PHP Server Extension 
## 3. Disable PHP Language Features @builtin php
## 4. Set Config Path to C:\xampp\php\php.ini
## 5. Set PHP Path to C:\xampp\php\php.exe 
## 6. Follow instructions below


# Create "inventory":

### Create the database
CREATE DATABASE IF NOT EXISTS inventory;
USE inventory;

### Create the users table
CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name  VARCHAR(50) NOT NULL,
    password   VARCHAR(300) NOT NULL,
    email      VARCHAR(50) NOT NULL UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

# Create "inventoryy":

### Create the database
CREATE DATABASE IF NOT EXISTS inventoryy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE inventoryy;

### Hierarchy nodes
CREATE TABLE IF NOT EXISTS hierarchy (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT NULL,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(parent_id)
);

### Items placed under a hierarchy node
CREATE TABLE IF NOT EXISTS items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hierarchy_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    notes TEXT,
    expiry_date DATE NULL,
    calories INT NULL,
    rfid VARCHAR(128) NULL,
    type VARCHAR(50) DEFAULT 'food',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hierarchy_id) REFERENCES hierarchy(id) ON DELETE CASCADE
);

### Optional: Populate a few starter nodes:

INSERT INTO hierarchy (parent_id, name, type) VALUES
(NULL, 'NASA Corporation', 'corporation'),
(1, 'Gateway Mission', 'large_team'),
(2, 'Deep Space Logistics Module', 'small_team'),
(2, 'Kennedy Space Center', 'small_team');