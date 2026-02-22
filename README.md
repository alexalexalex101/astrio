# READ ME
TO SETUP ON A NEW MACHINE:
1. Install XAMPP
2. Install PHP Server Extension
3. Disable PHP Language Features @builtin php
4. Set Config Path to C:\xampp\php\php.ini
5. Set PHP Path to C:\xampp\php\php.exe
6. Follow instructions below



**SQL CHUNK 1 — Create Databases + Tables**

CREATE DATABASE IF NOT EXISTS inventory; USE inventory; CREATE TABLE IF NOT EXISTS users ( id INT AUTO_INCREMENT PRIMARY KEY, first_name VARCHAR(50) NOT NULL, last_name VARCHAR(50) NOT NULL, password VARCHAR(300) NOT NULL, email VARCHAR(50) NOT NULL UNIQUE, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ); SET FOREIGN_KEY_CHECKS = 0; DROP TABLE IF EXISTS contracts; DROP TABLE IF EXISTS suppliers; SET FOREIGN_KEY_CHECKS = 1; CREATE TABLE suppliers ( supplier_id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL, item_supplied VARCHAR(100), risk_level VARCHAR(20), contact_email VARCHAR(100), tracking_method VARCHAR(50), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ); CREATE TABLE contracts ( contract_id INT AUTO_INCREMENT PRIMARY KEY, supplier_id INT NOT NULL, contract_name VARCHAR(100), start_date DATE, end_date DATE, status VARCHAR(20), contract_value BIGINT, FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id) ); INSERT INTO suppliers (name, item_supplied, risk_level, contact_email, tracking_method) VALUES ('Collins Aerospace','Avionics and life support hardware','High','support@collinsaerospace.com','RFID/Barcode'), ('Honeywell Aerospace','Environmental control systems and sensors','Medium','contact@honeywell.com','Barcode'), ('Lockheed Martin','Orion spacecraft components','Critical','info@lmco.com','RFID'), ('Boeing','SLS structural components','High','support@boeing.com','Barcode'), ('Axiom Space','Habitat module hardware','High','info@axiomspace.com','RFID'), ('Sierra Nevada Corp.','Scientific payload hardware','Medium','science@sncorp.com','Barcode'), ('Teledyne Brown Engineering','ISS science racks and hardware','High','info@teledyne.com','RFID'), ('Paragon Space Development Corp.','Life support consumables and filters','Medium','info@paragonsdc.com','RFID'), ('UTC Aerospace Systems','Spacecraft components and assemblies','Medium','contact@utcaerospacesystems.com','Barcode'), ('Thermo Fisher Scientific','Medical kits and scientific instruments','High','support@thermofisher.com','RFID'); INSERT INTO contracts (supplier_id, contract_name, start_date, end_date, status, contract_value) VALUES (1,'NEST - NASA End-User Services & Technologies','2019-01-01','2029-08-31','Active',2900000000), (2,'AEGIS - Advanced Enterprise Global IT Solutions','2021-01-01','2032-04-30','Active',2500000000), (3,'Human Health & Performance Contract','2015-01-01','2025-10-31','Active',1400000000), (4,'SACOM - Consolidated Operations & Maintenance','2015-01-01','2025-06-30','Active',1300000000), (4,'BOSS - Base Operations & Spaceport Services','2018-01-01','2025-03-21','Active',675000000), (6,'ATOM-5 - Aerospace Testing & Facilities O&M','2022-01-01','2027-06-21','Active',298000000), (7,'SAMDA - Support for Atmospheres, Modeling, and Data Assimilation','2017-01-01','2025-05-31','Active',298000000); CREATE DATABASE IF NOT EXISTS inventory CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; USE inventory; CREATE TABLE hierarchy ( id INT AUTO_INCREMENT PRIMARY KEY, parent_id INT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(50) DEFAULT 'node', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX(parent_id) ); CREATE TABLE items ( id INT AUTO_INCREMENT PRIMARY KEY, hierarchy_id INT NOT NULL, name VARCHAR(255) NOT NULL, location TEXT, expiry_date DATE NULL, calories INT NULL, rfid VARCHAR(128) NULL, type VARCHAR(50) DEFAULT 'food', remaining_percent TINYINT UNSIGNED NOT NULL DEFAULT 100, volume_liters DECIMAL(5,2) DEFAULT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (hierarchy_id) REFERENCES hierarchy(id) ON DELETE CASCADE ); CREATE TABLE incoming ( id INT AUTO_INCREMENT PRIMARY KEY, hierarchy_id INT DEFAULT 0, name VARCHAR(255) NOT NULL, type VARCHAR(50) DEFAULT 'food', location VARCHAR(255), expiry_date DATE, calories INT, rfid VARCHAR(100), remaining_percent TINYINT UNSIGNED NOT NULL DEFAULT 100, volume_liters DECIMAL(5,2) DEFAULT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP );

**SQL CHUNK 2 — NASA DSLM Hierarchy + Items**


USE inventory; DELETE FROM items; DELETE FROM hierarchy; INSERT INTO hierarchy (id, parent_id, name) VALUES (1,NULL,'NASA Corporation'), (2,1,'Gateway Mission'), (3,2,'Deep Space Logistics Module'), (10,3,'Stack S1'), (11,3,'Stack S2'), (12,3,'Stack S3'), (13,3,'Stack C1'), (14,3,'Stack C2'), (15,3,'Waste Bay'), (20,10,'CTB-S1-OUTER-01'), (21,20,'CTB-S1-INNER-01'), (22,21,'CTB-S1-SUB-01'), (30,11,'CTB-S2-OUTER-01'), (31,30,'CTB-S2-INNER-01'), (32,31,'CTB-S2-SUB-01'), (40,12,'CTB-S3-OUTER-01'), (41,40,'CTB-S3-INNER-01'), (42,41,'CTB-S3-SUB-01'), (50,13,'CTB-C1-OUTER-01'), (51,50,'CTB-C1-INNER-01'), (52,51,'CTB-C1-SUB-01'), (60,14,'CTB-C2-OUTER-01'), (61,60,'CTB-C2-INNER-01'), (62,61,'CTB-C2-SUB-01'), (70,22,'Food Package Group A'), (71,70,'Protein Bars'), (72,71,'Protein Bar Pouch 1'), (73,72,'Protein Bar Strip 1'), (74,70,'Rehydratable Eggs'), (75,74,'Eggs Pouch 1'), (76,22,'Coffee Group'), (77,76,'Coffee Packs'), (78,77,'Coffee Sleeve 1'), (90,32,'Medical Package Alpha'), (91,90,'Analgesics'), (92,91,'Analgesic Blister Pack 1'), (93,92,'Analgesic Strip 1'), (94,90,'Antibiotics'), (95,94,'Antibiotic Vial Tray 1'), (96,95,'Antibiotic Vial Row 1'), (110,42,'Hand Tools Set'), (111,110,'Torque Wrenches'), (112,111,'Torque Wrench Case 1'), (113,110,'Precision Screwdrivers'), (114,113,'Screwdriver Case 1'), (130,52,'Optical Bench Instruments'), (131,130,'Spectrum Analyzers'), (132,131,'Spectrum Analyzer Case 1'), (133,132,'Spectrum Analyzer Main Unit'), (134,132,'Spectrum Analyzer Calibration Block'), (135,130,'Fiber Inspection Scopes'), (136,135,'Fiber Scope Case 1'), (150,62,'Critical Spares'), (151,150,'Pump Assemblies'), (152,151,'Pump Crate 1'), (153,150,'Fan Modules'), (154,153,'Fan Crate 1'); INSERT INTO items (hierarchy_id,name,type,expiry_date,calories,location,rfid,remaining_percent,volume_liters) SELECT 73, CONCAT('Protein Bar - Chocolate 50g #', n.num),'food','2027-03-01',220, 'Stack S1 / CTB-S1-OUTER-01 / CTB-S1-INNER-01 / CTB-S1-SUB-01 / Food Package Group A / Protein Bars / Protein Bar Pouch 1 / Protein Bar Strip 1', CONCAT('RFID-FOOD-BAR-',LPAD(n.num,4,'0')),100,0.20 FROM (SELECT 1 num UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10) n; INSERT INTO items (hierarchy_id,name,type,expiry_date,calories,location,rfid,remaining_percent,volume_liters) SELECT 75, CONCAT('Rehydratable Scrambled Eggs 120g Pouch #', n.num),'food','2027-02-15',350, 'Stack S1 / CTB-S1-OUTER-01 / CTB-S1-INNER-01 / CTB-S1-SUB-01 / Food Package Group A / Rehydratable Eggs / Eggs Pouch 1', CONCAT('RFID-FOOD-EGGS-',LPAD(n.num,4,'0')),100,0.60 FROM (SELECT 1 num UNION ALL SELECT 2 UNION ALL SELECT 3) n; INSERT INTO items (hierarchy_id,name,type,expiry_date,calories,location,rfid,remaining_percent,volume_liters) SELECT 78, CONCAT('Instant Coffee Pack 10g #', n.num),'food','2028-01-01',5, 'Stack S1 / CTB-S1-OUTER-01 / CTB-S1-INNER-01 / CTB-S1-SUB-01 / Coffee Group / Coffee Packs / Coffee Sleeve 1', CONCAT('RFID-FOOD-COFFEE-',LPAD(n.num,4,'0')), CASE WHEN n.num<=5 THEN 60 ELSE 100 END, 0.05 FROM ( SELECT 1 num UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12 UNION ALL SELECT 13 UNION ALL SELECT 14 UNION ALL SELECT 15 UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18 UNION ALL SELECT 19 UNION ALL SELECT 20 ) n; INSERT INTO items (hierarchy_id,name,type,expiry_date,calories,location,rfid,remaining_percent,volume_liters) VALUES (93,'Ibuprofen 200mg Tablet','medical','2026-12-31',NULL,'Stack S2 / CTB-S2-OUTER-01 / CTB-S2-INNER-01 / CTB-S2-SUB-01 / Medical Package Alpha / Analgesics / Analgesic Blister Pack 1 / Analgesic Strip 1','RFID-MED-IBU-0001',100,0.01), (93,'Ibuprofen 200mg Tablet','medical','2026-12-31',NULL,'Stack S2 / CTB-S2-OUTER-01 / CTB-S2-INNER-01 / CTB-S2-SUB-01 / Medical Package Alpha / Analgesics / Analgesic Blister Pack 1 / Analgesic Strip 1','RFID-MED-IBU-0002',100,0.01), (96,'Amoxicillin 500mg Vial','medical','2026-06-30',NULL,'Stack S2 / CTB-S2-OUTER-01 / CTB-S2-INNER-01 / CTB-S2-SUB-01 / Medical Package Alpha / Antibiotics / Antibiotic Vial Tray 1 / Antibiotic Vial Row 1','RFID-MED-AMOX-0001',100,0.03), (96,'Amoxicillin 500mg Vial','medical','2026-06-30',NULL,'Stack S2 / CTB-S2-OUTER-01 / CTB-S2-INNER-01 / CTB-S2-SUB-01 / Medical Package Alpha / Antibiotics / Antibiotic Vial Tray 1 / Antibiotic Vial Row 1','RFID-MED-AMOX-0002',60,0.03); INSERT INTO items (hierarchy_id,name,type,expiry_date,calories,location,rfid,remaining_percent,volume_liters) VALUES (112,'Torque Wrench 5-60 Nm','tool',NULL,NULL,'Stack S3 / CTB-S3-OUTER-01 / CTB-S3-INNER-01 / CTB-S3-SUB-01 / Hand Tools Set / Torque Wrenches / Torque Wrench Case 1','RFID-TOOL-TW-0001',100,2.50), (114,'Precision Screwdriver Set 24-bit','tool',NULL,NULL,'Stack S3 / CTB-S3-OUTER-01 / CTB-S3-INNER-01 / CTB-S3-SUB-01 / Hand Tools Set / Precision Screwdrivers / Screwdriver Case 1','RFID-TOOL-SD-0001',100,1.20); INSERT INTO items (hierarchy_id,name,type,expiry_date,calories,location,rfid,remaining_percent,volume_liters) VALUES (133,'RF Spectrum Analyzer 9kHz-6GHz','scientific',NULL,NULL,'Stack C1 / CTB-C1-OUTER-01 / CTB-C1-INNER-01 / CTB-C1-SUB-01 / Optical Bench Instruments / Spectrum Analyzers / Spectrum Analyzer Case 1 / Spectrum Analyzer Main Unit','RFID-SCI-SA-0001',100,18.00), (134,'Spectrum Analyzer Calibration Block','scientific',NULL,NULL,'Stack C1 / CTB-C1-OUTER-01 / CTB-C1-INNER-01 / CTB-C1-SUB-01 / Optical Bench Instruments / Spectrum Analyzers / Spectrum Analyzer Case 1 / Spectrum Analyzer Calibration Block','RFID-SCI-SA-CAL-0001',100,1.50), (136,'Fiber Inspection Scope Handheld','scientific',NULL,NULL,'Stack C1 / CTB-C1-OUTER-01 / CTB-C1-INNER-01 / CTB-C1-SUB-01 / Optical Bench Instruments / Fiber Inspection Scopes / Fiber Scope Case 1','RFID-SCI-FIB-0001',100,3.20); INSERT INTO items (hierarchy_id,name,type,expiry_date,calories,location,rfid,remaining_percent,volume_liters) VALUES (152,'Pump Assembly Water Recycling','equipment',NULL,NULL,'Stack C2 / CTB-C2-OUTER-01 / CTB-C2-INNER-01 / CTB-C2-SUB-01 / Critical Spares / Pump Assemblies / Pump Crate 1','RFID-SPARE-PUMP-0001',100,25.00), (154,'Fan Module Environmental Control','equipment',NULL,NULL,'Stack C2 / CTB-C2-OUTER-01 / CTB-C2-INNER-01 / CTB-C2-SUB-01 / Critical Spares / Fan Modules / Fan Crate 1','RFID-SPARE-FAN-0001',100,12.00); INSERT INTO items (hierarchy_id,name,type,expiry_date,calories,location,rfid,remaining_percent,volume_liters) VALUES (15,'Compressed Waste Block 5kg','waste',NULL,NULL,'Waste Bay','RFID-WASTE-0001',100,8.00), (15,'Compressed Waste Block 5kg','waste',NULL,NULL,'Waste Bay','RFID-WASTE-0002',100,8.00);


**SQL CHUNK 3 — Incoming Items + Mapping + RFID Replacement**
USE inventory;

INSERT INTO incoming
(id, hierarchy_id, name, location, expiry_date, calories, rfid, type, remaining_percent, volume_liters, created_at)
VALUES
(40,0,'Meal Pack C','Rack_C_1 / CTB_005','2026-02-05',480,'3824991122','food',100,NULL,'2025-12-07 23:55:00'),
(41,0,'Meal Pack D','Rack_C_1 / CTB_006','2026-02-12',530,'1070441122','food',100,NULL,'2025-12-07 23:55:30'),
(42,0,'Scientific Sample B','Rack_C_1 / CTB_005',NULL,NULL,'3811482233','scientific',100,NULL,'2025-12-07 23:56:00'),
(43,0,'Medical Kit B','Rack_C_1 / CTB_006','2026-07-01',NULL,'3821103344','medical',100,NULL,'2025-12-07 23:56:30'),
(44,0,'Water Container C','Rack_D_2 / CTB_007',NULL,NULL,'3822914455','water',100,NULL,'2025-12-07 23:57:00'),
(45,0,'Water Container D','Rack_D_2 / CTB_008',NULL,NULL,'3823955566','water',100,NULL,'2025-12-07 23:57:30');

UPDATE items
SET rfid = '3824991122'
WHERE name = 'Protein Bar - Chocolate 50g #1'
LIMIT 1;

UPDATE items
SET rfid = '1070441122'
WHERE name = 'Protein Bar - Chocolate 50g #2'
LIMIT 1;

UPDATE items
SET rfid = '3811482233'
WHERE name = 'RF Spectrum Analyzer 9kHz-6GHz'
LIMIT 1;

UPDATE items
SET rfid = '3821103344'
WHERE name = 'Ibuprofen 200mg Tablet'
LIMIT 1;

UPDATE items
SET rfid = '3818806677'
WHERE name = 'Torque Wrench 5-60 Nm'
LIMIT 1;

UPDATE items
SET rfid = '3822914455'
WHERE name = 'Pump Assembly Water Recycling'
ORDER BY id
LIMIT 1;

UPDATE items
SET rfid = '3823955566'
WHERE id = (
    SELECT id FROM (
        SELECT id
        FROM items
        WHERE name = 'Pump Assembly Water Recycling'
        ORDER BY id
        LIMIT 1 OFFSET 1
    ) AS t
);

UPDATE items
SET rfid = '1071688899'
WHERE name = 'Precision Screwdriver Set 24-bit'
LIMIT 1;

UPDATE items
SET rfid = '3816691223'
WHERE name = 'Fan Module Environmental Control'
LIMIT 1;

UPDATE items
SET rfid = '3820456000'
WHERE name = 'Compressed Waste Block 5kg'
LIMIT 1;


**SQL CHUNK 4 FIX CTB SPECIFICATIONS AND HIERACHY**


ALTER TABLE hierarchy
ADD COLUMN ctb_type VARCHAR(50) DEFAULT NULL,
ADD COLUMN capacity_liters DECIMAL(7,2) DEFAULT NULL;

CREATE TABLE IF NOT EXISTS ctb_specifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ctb_type VARCHAR(50) NOT NULL,
    description VARCHAR(255),
    capacity_liters DECIMAL(7,2) NOT NULL,
    max_weight_kg DECIMAL(7,2),
    length_cm DECIMAL(6,2),
    width_cm DECIMAL(6,2),
    height_cm DECIMAL(6,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (ctb_type)
);

INSERT INTO ctb_specifications (ctb_type, description, capacity_liters)
VALUES 
('CTB-0.5','Half-size CTB',10.0),
('CTB-1.0','Standard CTB',20.0),
('CTB-2.0','Double CTB',38.0),
('CTB-4.0','Quad CTB',75.0),
('CTB-6.0','Six-unit CTB',110.0),
('CTB-8.0','Eight-unit CTB',150.0),
('CTB-10.0','Ten-unit CTB',190.0),
('STRIP','Strip container',5.0),
('POUCH','Pouch container',8.0),
('SLEEVE','Sleeve container',12.0),
('CASE','Case container',15.0),
('STACK','4-meter stack',4.0)
ON DUPLICATE KEY UPDATE capacity_liters = VALUES(capacity_liters);

UPDATE hierarchy
SET ctb_type = 'CTB-1.0'
WHERE name LIKE '%SUB%' AND (ctb_type IS NULL OR ctb_type = '');

UPDATE hierarchy
SET ctb_type = 'POUCH'
WHERE name LIKE '%Pouch%' AND (ctb_type IS NULL OR ctb_type = '');

UPDATE hierarchy
SET ctb_type = 'STRIP'
WHERE name LIKE '%Strip%' AND (ctb_type IS NULL OR ctb_type = '');

UPDATE hierarchy
SET ctb_type = 'CASE'
WHERE name LIKE '%Food Package%' AND (ctb_type IS NULL OR ctb_type = '');

UPDATE hierarchy
SET ctb_type = 'STACK'
WHERE name IN ('S1','S2','S3','C1','C2');

UPDATE hierarchy h
JOIN ctb_specifications s ON h.ctb_type = s.ctb_type
SET h.capacity_liters = s.capacity_liters;



