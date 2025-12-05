First make suppliers but make sure it is inside inventory not inventorry

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
