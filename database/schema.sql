-- ISP Billing System Database Schema
-- Compatible with MariaDB 10.x

-- Create ISP Billing Database
CREATE DATABASE IF NOT EXISTS isp_billing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE isp_billing;

-- Users Table (Admin users)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    role ENUM('admin', 'manager', 'support') DEFAULT 'support',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Customers Table
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id VARCHAR(20) NOT NULL UNIQUE,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    address TEXT,
    nid_number VARCHAR(30),
    package_id INT,
    router_id INT,
    static_ip VARCHAR(45),
    mac_address VARCHAR(17),
    connection_type ENUM('pppoe', 'hotspot', 'static') DEFAULT 'pppoe',
    status ENUM('active', 'inactive', 'expired', 'suspended') DEFAULT 'inactive',
    activation_date DATE,
    expiration_date DATE,
    balance DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE SET NULL,
    FOREIGN KEY (router_id) REFERENCES routers(id) ON DELETE SET NULL
);

-- Packages Table
CREATE TABLE IF NOT EXISTS packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    package_name VARCHAR(100) NOT NULL,
    package_type ENUM('prepaid', 'postpaid', 'hotspot', 'pppoe') DEFAULT 'prepaid',
    download_speed VARCHAR(20),
    upload_speed VARCHAR(20),
    bandwidth_limit BIGINT,
    price DECIMAL(10,2) NOT NULL,
    tax_percentage DECIMAL(5,2) DEFAULT 0.00,
    radius_group VARCHAR(100),
    valid_days INT DEFAULT 30,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Routers Table
CREATE TABLE IF NOT EXISTS routers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    router_name VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    username VARCHAR(50),
    password VARCHAR(255),
    port VARCHAR(10) DEFAULT '8728',
    api_token VARCHAR(255),
    router_type ENUM('mikrotik', 'cisco', 'ubiquiti', 'other') DEFAULT 'mikrotik',
    location VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Invoices Table
CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(20) NOT NULL UNIQUE,
    customer_id INT NOT NULL,
    package_id INT,
    amount DECIMAL(10,2) NOT NULL,
    tax_amount DECIMAL(10,2) DEFAULT 0.00,
    discount DECIMAL(10,2) DEFAULT 0.00,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_status ENUM('paid', 'unpaid', 'partial', 'cancelled') DEFAULT 'unpaid',
    payment_method ENUM('cash', 'bank_transfer', 'mobile_banking', 'card', 'other'),
    paid_amount DECIMAL(10,2) DEFAULT 0.00,
    created_date DATE NOT NULL,
    due_date DATE NOT NULL,
    paid_date DATE,
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Payments Table
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    payment_id VARCHAR(20) NOT NULL UNIQUE,
    invoice_id INT,
    customer_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'bank_transfer', 'mobile_banking', 'card', 'other'),
    transaction_id VARCHAR(100),
    received_by INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE SET NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Tickets Table
CREATE TABLE IF NOT EXISTS tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_number VARCHAR(20) NOT NULL UNIQUE,
    customer_id INT,
    subject VARCHAR(200) NOT NULL,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    status ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
    created_by INT,
    assigned_to INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
);

-- Ticket Replies Table
CREATE TABLE IF NOT EXISTS ticket_replies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Account Logs Table
CREATE TABLE IF NOT EXISTS account_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

-- Settings Table
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, full_name, email, role, status) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin@example.com', 'admin', 'active');

-- Insert default settings
INSERT INTO settings (setting_key, setting_value) VALUES
('company_name', 'My ISP Company'),
('company_address', '123 Main Street, City'),
('company_phone', '+880 1234567890'),
('company_email', 'info@example.com'),
('company_website', 'https://example.com'),
('tax_percentage', '0'),
('invoice_due_days', '7'),
('currency', 'BDT'),
('smtp_host', 'smtp.example.com'),
('smtp_port', '587'),
('smtp_user', ''),
('smtp_pass', ''),
('smtp_from_email', 'noreply@example.com'),
('smtp_from_name', 'ISP Billing');

-- Create RADIUS database
CREATE DATABASE IF NOT EXISTS radius CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE radius;

-- Standard FreeRADIUS Tables

CREATE TABLE IF NOT EXISTS radcheck (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(64) NOT NULL,
    attribute VARCHAR(64) NOT NULL,
    op CHAR(2) DEFAULT ':=',
    value VARCHAR(253) NOT NULL,
    INDEX (username)
);

CREATE TABLE IF NOT EXISTS radreply (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(64) NOT NULL,
    attribute VARCHAR(64) NOT NULL,
    op CHAR(2) DEFAULT ':=',
    value VARCHAR(253) NOT NULL,
    INDEX (username)
);

CREATE TABLE IF NOT EXISTS radgroupcheck (
    id INT AUTO_INCREMENT PRIMARY KEY,
    groupname VARCHAR(64) NOT NULL,
    attribute VARCHAR(64) NOT NULL,
    op CHAR(2) DEFAULT ':=',
    value VARCHAR(253) NOT NULL,
    INDEX (groupname)
);

CREATE TABLE IF NOT EXISTS radgroupreply (
    id INT AUTO_INCREMENT PRIMARY KEY,
    groupname VARCHAR(64) NOT NULL,
    attribute VARCHAR(64) NOT NULL,
    op CHAR(2) DEFAULT ':=',
    value VARCHAR(253) NOT NULL,
    INDEX (groupname)
);

CREATE TABLE IF NOT EXISTS radusergroup (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(64) NOT NULL,
    groupname VARCHAR(64) NOT NULL,
    priority INT DEFAULT 1,
    INDEX (username),
    INDEX (groupname)
);

CREATE TABLE IF NOT EXISTS radacct (
    id INT AUTO_INCREMENT PRIMARY KEY,
    acctsessionid VARCHAR(64) NOT NULL,
    acctuniqueid VARCHAR(32) NOT NULL UNIQUE,
    username VARCHAR(64),
    realm VARCHAR(64),
    nasipaddress VARCHAR(45) NOT NULL,
    nasportid VARCHAR(15),
    nasporttype VARCHAR(32),
    acctstarttime DATETIME,
    acctupdatetime DATETIME,
    acctstoptime DATETIME,
    acctinterval INT,
    acctsessiontime INT,
    acctauthentic VARCHAR(32),
    connectinfo_start VARCHAR(50),
    connectinfo_stop VARCHAR(50),
    acctinputoctets BIGINT,
    acctoutputoctets BIGINT,
    calledstationid VARCHAR(50),
    callingstationid VARCHAR(50),
    acctterminatecause VARCHAR(32),
    servicetype VARCHAR(32),
    framedprotocol VARCHAR(32),
    framedipaddress VARCHAR(45),
    INDEX (username),
    INDEX (framedipaddress),
    INDEX (acctsessionid),
    INDEX (acctsessiontime),
    INDEX (acctstarttime),
    INDEX (acctstoptime),
    INDEX (nasipaddress)
);

CREATE TABLE IF NOT EXISTS radpostauth (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(64),
    pass VARCHAR(64),
    reply VARCHAR(32),
    authdate DATETIME,
    INDEX (username)
);

CREATE TABLE IF NOT EXISTS nas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nasname VARCHAR(45) NOT NULL,
    shortname VARCHAR(32),
    type VARCHAR(30) DEFAULT 'other',
    ports INT,
    secret VARCHAR(60) DEFAULT 'secret',
    server VARCHAR(64),
    community VARCHAR(50),
    description VARCHAR(200) DEFAULT 'RADIUS Client'
);
