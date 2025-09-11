-- Create database
CREATE DATABASE IF NOT EXISTS solar_proposal_system;
USE solar_proposal_system;

-- Table for customers
CREATE TABLE IF NOT EXISTS customers (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for projects
CREATE TABLE IF NOT EXISTS projects (
    project_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT,
    project_capacity DECIMAL(10,2) NOT NULL,
    project_date DATE NOT NULL,
    project_location VARCHAR(100) NOT NULL,
    effective_cost DECIMAL(12,2) NOT NULL,
    subsidy_amount DECIMAL(12,2) NOT NULL,
    net_landing_cost DECIMAL(12,2) NOT NULL,
    discom_meter_charge VARCHAR(50) DEFAULT 'INCLUDED',
    transportation_charge VARCHAR(50) DEFAULT 'INCLUDED',
    status VARCHAR(50) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
);

-- Table for materials used in projects
CREATE TABLE IF NOT EXISTS materials (
    material_id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT,
    description VARCHAR(100) NOT NULL,
    unit VARCHAR(20) NOT NULL,
    quantity INT NOT NULL,
    size VARCHAR(50),
    manufacturer VARCHAR(100),
    FOREIGN KEY (project_id) REFERENCES projects(project_id)
);

-- Table for activities/services
CREATE TABLE IF NOT EXISTS activities (
    activity_id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT,
    activity_name VARCHAR(100) NOT NULL,
    activity_details TEXT,
    FOREIGN KEY (project_id) REFERENCES projects(project_id)
);

-- Table for users
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- Insert default material descriptions
INSERT INTO materials (project_id, description, unit, quantity, size, manufacturer) VALUES
(NULL, 'Solar PV Module', 'Nos', 8, '580WP', 'ADANI TOPCON (Bifacial Mono Panel)'),
(NULL, 'Module Mounting Structure', 'Set', 1, 'At Actual', 'Hot dip GI/60*40/40*40/2mm'),
(NULL, 'Grid Tie Inverter', 'Nos', 1, '4.4 kw, 1phase', 'XWATT/SOLARYAAN'),
(NULL, 'DC Cable', 'Mtr', 0, '4 Sq.mm', 'Polycab'),
(NULL, 'ACDB & DCDB', 'Set', 1, 'As per Rating', 'MCB -C&S'),
(NULL, 'Earthing system (Chemical)', 'Set', 1, '1.0 meter', 'Standard /6 sq mm AL Kanbary Make'),
(NULL, 'AC Cable', 'Mtr', 0, '4 Sqmm, 2core', 'Polycab/RR'),
(NULL, 'Lightning Arrester', 'Set', 1, '1.2 mtr', 'Copper coated, 16 sqmm'),
(NULL, 'Isolation Mcb', 'Nos', 1, 'As actual', 'C&S'),
(NULL, 'Foundation', 'Set', 1, 'As actual', 'As per structure Design');

-- Insert default activities
INSERT INTO activities (project_id, activity_name, activity_details) VALUES
(NULL, 'Pre site visits', 'Site Survey'),
(NULL, 'Design/Installation', 'Installation of system as per guidelines.'),
(NULL, 'Warranty', 'Panel warranty 30 years\nInverter Warranty for 10 years'),
(NULL, 'AMC Support', 'Annual maintenance support for 5 years');

-- Insert a default admin user (password: admin123)
INSERT INTO users (username, password, full_name, email, role) VALUES
('admin', '$2y$10$8QYeB2LhV9QNTb4X7IQo5OJ7.yBHVqg6x3m0fTqIo2sRhEFD9wr9m', 'Administrator', 'admin@janetasolar.com', 'admin');