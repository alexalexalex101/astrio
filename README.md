# TO SETUP ON A NEW MACHINE:

## 1. Install XAMPP
## 2. Install PHP Server Extension 
## 3. Disable PHP Language Features @builtin php
## 4. Set Config Path to C:\xampp\php\php.ini
## 5. Set PHP Path to C:\xampp\php\php.exe 
## 6. Follow instructions below


# Create "inventory":

CREATE DATABASE IF NOT EXISTS inventory;
USE inventory;

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

CREATE DATABASE IF NOT EXISTS inventoryy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE inventoryy;

CREATE TABLE IF NOT EXISTS hierarchy (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT NULL,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(parent_id)
);

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




# Make sure you run this in inventory not inventorry this is for suppliers and contracts db setup

CREATE TABLE suppliers (
    supplier_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    item_supplied VARCHAR(100),
    risk_level VARCHAR(20),
    contact_email VARCHAR(100),
    tracking_method VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO suppliers
(name, item_supplied, risk_level, contact_email, tracking_method)
VALUES
('SpaceX', 'Cargo delivery (Dragon XL)', 'Critical', 'contact@spacex.com', 'RFID/Barcode'),
('Boeing', 'Spacecraft components', 'High', 'support@boeing.com', 'Barcode'),
('Lockheed Martin', 'Crew module hardware', 'Critical', 'info@lmco.com', 'RFID'),
('Northrop Grumman', 'Pressurized cargo modules', 'Critical', 'cargo@ngc.com', 'RFID/Barcode'),
('Sierra Nevada Corp.', 'Scientific payloads', 'Medium', 'science@sncorp.com', 'Barcode'),
('NASA KSC Vendors', 'Food, water, consumables', 'Critical', 'vendors@ksc.nasa.gov', 'RFID/Barcode');

CREATE TABLE contracts (
    contract_id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL,
    contract_name VARCHAR(100),
    start_date DATE,
    end_date DATE,
    status VARCHAR(20),
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id)
);

INSERT INTO contracts
(supplier_id, contract_name, start_date, end_date, status)
VALUES
(1, 'Gateway Logistics Services', '2025-01-01', '2028-01-01', 'Active'),
(2, 'Artemis Program Support – SLS', '2024-08-01', '2027-08-01', 'Active'),
(3, 'Orion Crew Module Contract', '2024-08-01', '2027-08-01', 'Active'),
(4, 'Cygnus Gateway Cargo Contract', '2025-03-01', '2028-03-01', 'Active'),
(5, 'Scientific Payload Partnership', '2025-02-01', '2028-02-01', 'Pending'),
(6, 'Consumables Supply – KSC Vendors', '2025-01-01', '2028-01-01', 'Active');
