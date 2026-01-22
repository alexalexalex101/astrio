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
    location TEXT,
    expiry_date DATE NULL,
    calories INT NULL,
    rfid VARCHAR(128) NULL,
    type VARCHAR(50) DEFAULT 'food',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hierarchy_id) REFERENCES hierarchy(id) ON DELETE CASCADE
);


# Make sure you run this in inventory not inventorry this is for suppliers and contracts db setup

# Make sure you run this inside the "inventory" database
USE inventory;

SET FOREIGN_KEY_CHECKS = 0;

-- Reset contracts first (FK dependency)
DROP TABLE IF EXISTS contracts;

-- Reset suppliers
DROP TABLE IF EXISTS suppliers;

SET FOREIGN_KEY_CHECKS = 1;

-- Recreate suppliers table (REAL NASA item suppliers only)
CREATE TABLE suppliers (
    supplier_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    item_supplied VARCHAR(100),
    risk_level VARCHAR(20),
    contact_email VARCHAR(100),
    tracking_method VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert accurate NASA suppliers of PHYSICAL ITEMS
INSERT INTO suppliers (name, item_supplied, risk_level, contact_email, tracking_method)
VALUES
('Collins Aerospace', 'Avionics and life support hardware', 'High', 'support@collinsaerospace.com', 'RFID/Barcode'),
('Honeywell Aerospace', 'Environmental control systems and sensors', 'Medium', 'contact@honeywell.com', 'Barcode'),
('Lockheed Martin', 'Orion spacecraft components', 'Critical', 'info@lmco.com', 'RFID'),
('Boeing', 'SLS structural components', 'High', 'support@boeing.com', 'Barcode'),
('Axiom Space', 'Habitat module hardware', 'High', 'info@axiomspace.com', 'RFID'),
('Sierra Nevada Corp.', 'Scientific payload hardware', 'Medium', 'science@sncorp.com', 'Barcode'),
('Teledyne Brown Engineering', 'ISS science racks and hardware', 'High', 'info@teledyne.com', 'RFID'),
('Paragon Space Development Corp.', 'Life support consumables and filters', 'Medium', 'info@paragonsdc.com', 'RFID'),
('UTC Aerospace Systems', 'Spacecraft components and assemblies', 'Medium', 'contact@utcaerospacesystems.com', 'Barcode'),
('NASA KSC Vendors', 'Food, water, and consumables', 'Critical', 'vendors@ksc.nasa.gov', 'RFID/Barcode');

-- Recreate contracts table (now includes contract_value)
CREATE TABLE contracts (
    contract_id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NULL,
    contract_name VARCHAR(100),
    start_date DATE,
    end_date DATE,
    status VARCHAR(20),
    contract_value BIGINT,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id)
);

-- Insert only the most important, highest-value NASA contracts
INSERT INTO contracts (supplier_id, contract_name, start_date, end_date, status, contract_value)
VALUES
(NULL, 'NEST - NASA End-User Services & Technologies', '2019-01-01', '2029-08-31', 'Active', 2900000000),
(NULL, 'AEGIS - Advanced Enterprise Global IT Solutions', '2021-01-01', '2032-04-30', 'Active', 2500000000),
(NULL, 'Human Health & Performance Contract', '2015-01-01', '2025-10-31', 'Active', 1400000000),
(NULL, 'SACOM - Consolidated Operations & Maintenance', '2015-01-01', '2025-06-30', 'Active', 1300000000),
(NULL, 'BOSS - Base Operations & Spaceport Services', '2018-01-01', '2025-03-21', 'Active', 675000000),
(NULL, 'ATOM-5 - Aerospace Testing & Facilities O&M', '2022-01-01', '2027-06-21', 'Active', 298000000),
(NULL, 'SAMDA - Support for Atmospheres, Modeling, and Data Assimilation', '2017-01-01', '2025-05-31', 'Active', 298000000);


# Make sure you run this in inventoryy to populate the items and hiearchy tables with data

## 🔄 Reset
DELETE FROM items;
DELETE FROM hierarchy;

## 🗂️ Hierarchy (modeled from DSLM doc)
INSERT INTO hierarchy (id, parent_id, name, created_at) VALUES (1, NULL, 'NASA Corporation', NOW()), (2, 1, 'Gateway Mission', NOW()), (3, 2, 'Deep Space Logistics Module', NOW()), (4, 3, 'Rack_A_1', NOW()), (5, 3, 'Rack_B_2', NOW()), (6, 4, 'CTB_001', NOW()), (7, 4, 'CTB_002', NOW()), (8, 5, 'CTB_003', NOW()), (9, 5, 'CTB_004', NOW()), (10, 3, 'Waste Bay', NOW());

## Items (RFIDs evenly distributed, realistic categories) 
INSERT INTO items (hierarchy_id, name, type, expiry_date, calories, location, rfid, created_at) VALUES (6, 'Meal Pack A', 'food', '2026-01-15', 500, 'Rack_A_1 / CTB_001', '3824983316', NOW()), (7, 'Meal Pack B', 'food', '2026-01-20', 520, 'Rack_A_1 / CTB_002', '1070336339', NOW()), (6, 'Scientific Sample A', 'scientific', NULL, NULL, 'Rack_A_1 / CTB_001', '3811479572', NOW()), (7, 'Medical Kit A', 'medical', '2026-06-01', NULL, 'Rack_A_1 / CTB_002', '3821091716', NOW()), (8, 'Water Container A', 'water', NULL, NULL, 'Rack_B_2 / CTB_003', '3822908996', NOW()), (9, 'Water Container B', 'water', NULL, NULL, 'Rack_B_2 / CTB_004', '3823946900', NOW()), (8, 'Tool Kit A', 'equipment', NULL, NULL, 'Rack_B_2 / CTB_003', '3818797508', NOW()), (9, 'Tool Kit B', 'equipment', NULL, NULL, 'Rack_B_2 / CTB_004', '1071677363', NOW()), (8, 'Spare Part A', 'spare', NULL, NULL, 'Rack_B_2 / CTB_003', '3816690612', NOW()), (10, 'Waste Package A', 'waste', NULL, NULL, 'Waste Bay', '3820455092', NOW());


## Incoming Table
CREATE TABLE incoming (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hierarchy_id INT DEFAULT 0,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(50) DEFAULT 'food',
    location VARCHAR(255),
    expiry_date DATE,
    calories INT,
    rfid VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

## Incoming Values
INSERT INTO incoming (id, hierarchy_id, name, location, expiry_date, calories, rfid, type, created_at) VALUES
(40, 6, 'Meal Pack C', 'Rack_C_1 / CTB_005', '2026-02-05', 480, 3824991122, 'food', '2025-12-07 23:55:00'),
(41, 7, 'Meal Pack D', 'Rack_C_1 / CTB_006', '2026-02-12', 530, 1070441122, 'food', '2025-12-07 23:55:30'),
(42, 6, 'Scientific Sample B', 'Rack_C_1 / CTB_005', NULL, NULL, 3811482233, 'scientific', '2025-12-07 23:56:00'),
(43, 7, 'Medical Kit B', 'Rack_C_1 / CTB_006', '2026-07-01', NULL, 3821103344, 'medical', '2025-12-07 23:56:30'),
(44, 8, 'Water Container C', 'Rack_D_2 / CTB_007', NULL, NULL, 3822914455, 'water', '2025-12-07 23:57:00'),
(45, 9, 'Water Container D', 'Rack_D_2 / CTB_008', NULL, NULL, 3823955566, 'water', '2025-12-07 23:57:30'),
(46, 8, 'Tool Kit C', 'Rack_D_2 / CTB_007', NULL, NULL, 3818806677, 'equipment', '2025-12-07 23:58:00'),
(47, 9, 'Tool Kit D', 'Rack_D_2 / CTB_008', NULL, NULL, 1071688899, 'equipment', '2025-12-07 23:58:30'),
(48, 8, 'Spare Part B', 'Rack_D_2 / CTB_007', NULL, NULL, 3816691223, 'spare', '2025-12-07 23:59:00'),
(49, 10, 'Waste Package B', 'Waste Bay', NULL, NULL, 3820456000, 'waste', '2025-12-07 23:59:30');
