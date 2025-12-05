Make sure you run this inside inventory not inventorry

CREATE TABLE suppliers (
  supplier_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  contract_id VARCHAR(50),
  item_supplied VARCHAR(100),
  risk_level VARCHAR(20),
  contact_email VARCHAR(100),
  tracking_method VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


INSERT INTO suppliers (name, contract_id, item_supplied, risk_level, contact_email, tracking_method)
VALUES
('SpaceX', 'Gateway Logistics Services', 'Cargo delivery (Dragon XL)', 'Critical', 'contact@spacex.com', 'RFID/Barcode'),
('Boeing', 'Artemis Program Support', 'Spacecraft components', 'High', 'support@boeing.com', 'Barcode'),
('Lockheed Martin', 'Orion/Gateway Contracts', 'Crew module hardware', 'Critical', 'info@lmco.com', 'RFID'),
('Northrop Grumman', 'Cygnus/Gateway Support', 'Pressurized cargo modules', 'Critical', 'cargo@ngc.com', 'RFID/Barcode'),
('Sierra Nevada Corp.', 'Commercial Partnerships', 'Scientific payloads', 'Medium', 'science@sncorp.com', 'Barcode'),
('NASA KSC Vendors', 'Kennedy Space Center Ops', 'Food, water, consumables', 'Critical', 'vendors@ksc.nasa.gov', 'RFID/Barcode');




CREATE TABLE contracts (
  contract_id INT AUTO_INCREMENT PRIMARY KEY,
  supplier_id INT NOT NULL,
  contract_name VARCHAR(100),
  start_date DATE,
  end_date DATE,
  status VARCHAR(20),
  FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id)
);

INSERT INTO contracts (contract_id, supplier_id, contract_name, start_date, end_date, status) VALUES
(1, 1, 'Gateway Logistics Services',        '2025-01-01', '2028-01-01', 'Active'),
(2, 2, 'Artemis Program Support – SLS',     '2024-08-01', '2027-08-01', 'Active'),
(3, 3, 'Orion Crew Module Contract',        '2024-08-01', '2027-08-01', 'Active'),
(4, 4, 'Cygnus Gateway Cargo Contract',     '2025-03-01', '2028-03-01', 'Active'),
(5, 5, 'Scientific Payload Partnership',    '2025-02-01', '2028-02-01', 'Pending'),
(6, 6, 'Consumables Supply – KSC Vendors',  '2025-01-01', '2028-01-01', 'Active');



