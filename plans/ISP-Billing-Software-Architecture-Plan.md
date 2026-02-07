# ISP Billing Software - Architectural Plan

## FreeRADIUS Backend + PHP MVC Framework + MariaDB

---

## 1. Executive Summary

This document outlines the architectural plan for a comprehensive ISP Billing Software system with FreeRADIUS backend authentication and a PHP MVC framework frontend using MariaDB 10. The system is designed to manage ISP customers, billing, bandwidth packages, NAS devices, and generate detailed reports.

### Key Features Based on Screenshots
- Dashboard with analytics (Active Users, Online Now, Revenue, Sales)
- Customer Management (CRUD operations)
- Package Management
- NAS Device Management
- Billing & Invoicing
- FreeRADIUS Integration (RadGroupReply, RadReply)
- Support Tickets
- User Self-Service Portal

---

## 2. Technology Stack

### Operating System
- **OS**: AlmaLinux 9.x
- **Web Server**: Nginx 1.22+
- **PHP Processor**: PHP-FPM 8.2+

### Backend
- **Language**: PHP 8.2+
- **Framework**: Laravel 10.x / CodeIgniter 4.x (MVC)
- **Database**: MariaDB 10.11+ (Both App + FreeRADIUS)
- **Authentication Backend**: FreeRADIUS 3.x

### Frontend
- **Template Engine**: Blade (Laravel) or Native (CodeIgniter)
- **CSS Framework**: Bootstrap 5.x
- **JavaScript**: Vanilla JS + Chart.js (for analytics)
- **Icons**: FontAwesome 6.x

### Server Requirements
- OS: AlmaLinux 9.x
- RAM: 4GB minimum (8GB recommended)
- Storage: 50GB+ SSD
- CPU: 2 cores minimum
- Additional: Redis (for caching, optional)

---

## 3. System Architecture

### High-Level Architecture

```
+------------------+     +------------------+     +------------------+
|   ISP Admin      |     |   Customer       |     |   NAS Devices    |
|   Panel (MVC)    |---->|   Portal (MVC)   |<--->|   (MikroTik)     |
+------------------+     +------------------+     +------------------+
         |                       |                       |
         v                       v                       v
+------------------+     +------------------+     +------------------+
|   MariaDB 10     |     |   FreeRADIUS    |     |   RADIUS         |
|   (App Database) |<----|   Server 3.x    |<----|   Protocol       |
+------------------+     +------------------+     +------------------+
                               |
                               v
                      +------------------+
                      |   MariaDB 10     |
                      |   (RADIUS DB)    |
                      +------------------+
```

### Dual Database Setup
1. **App Database** (`isp_billing`): Customer data, invoices, payments, packages, tickets, settings
2. **RADIUS Database** (`radius`): FreeRADIUS tables (radcheck, radreply, radgroupreply, radacct, etc.)

### Component Overview

1. **Web Application (PHP MVC)** - Main business logic, UI with routing
2. **MariaDB 10** - Data persistence for both app and RADIUS
3. **FreeRADIUS Server** - RADIUS authentication/authorization
4. **NAS Devices** - Network Access Servers (MikroTik, Cisco, Ubiquiti)

---

## 4. Database Schema Design

### 4.1 App Database (`isp_billing`)

#### 1. **users** - System administrators
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    role ENUM('admin', 'manager', 'support') DEFAULT 'support',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### 2. **customers** - ISP customers
```sql
CREATE TABLE customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id VARCHAR(20) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
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
    FOREIGN KEY (package_id) REFERENCES packages(id),
    FOREIGN KEY (router_id) REFERENCES routers(id)
);
```

#### 3. **packages** - Bandwidth packages
```sql
CREATE TABLE packages (
    id INT PRIMARY KEY AUTO_INCREMENT,
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
```

#### 4. **routers** - NAS devices
```sql
CREATE TABLE routers (
    id INT PRIMARY KEY AUTO_INCREMENT,
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
```

#### 5. **invoices** - Billing invoices
```sql
CREATE TABLE invoices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_number VARCHAR(20) UNIQUE NOT NULL,
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
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (package_id) REFERENCES packages(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);
```

#### 6. **payments** - Payment records
```sql
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    payment_id VARCHAR(20) UNIQUE NOT NULL,
    invoice_id INT,
    customer_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'bank_transfer', 'mobile_banking', 'card', 'other'),
    transaction_id VARCHAR(100),
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    received_by INT,
    notes TEXT,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id),
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (received_by) REFERENCES users(id)
);
```

#### 7. **tickets** - Support tickets
```sql
CREATE TABLE tickets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ticket_number VARCHAR(20) UNIQUE NOT NULL,
    customer_id INT,
    subject VARCHAR(200) NOT NULL,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    status ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
    created_by INT,
    assigned_to INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (assigned_to) REFERENCES users(id)
);
```

#### 8. **ticket_replies** - Support ticket replies
```sql
CREATE TABLE ticket_replies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ticket_id INT NOT NULL,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

#### 9. **account_logs** - Activity logging
```sql
CREATE TABLE account_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id)
);
```

#### 10. **settings** - System settings
```sql
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### 11. **migrations** - Laravel migration tracking
```sql
CREATE TABLE migrations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    migration VARCHAR(255) NOT NULL,
    batch INT NOT NULL
);
```

### 4.2 RADIUS Database (`radius`)

Standard FreeRADIUS tables for MariaDB:

```sql
-- Create radius database
CREATE DATABASE radius;

-- Standard FreeRADIUS tables
CREATE TABLE radreply (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(64) NOT NULL,
    attribute VARCHAR(64) NOT NULL,
    op CHAR(2) DEFAULT '=',
    value VARCHAR(253) NOT NULL
);

CREATE TABLE radgroupreply (
    id INT PRIMARY KEY AUTO_INCREMENT,
    groupname VARCHAR(64) NOT NULL,
    attribute VARCHAR(64) NOT NULL,
    op CHAR(2) DEFAULT '=',
    value VARCHAR(253) NOT NULL
);

CREATE TABLE radcheck (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(64) NOT NULL,
    attribute VARCHAR(64) NOT NULL,
    op CHAR(2) DEFAULT '=',
    value VARCHAR(253) NOT NULL
);

CREATE TABLE radgroupcheck (
    id INT PRIMARY KEY AUTO_INCREMENT,
    groupname VARCHAR(64) NOT NULL,
    attribute VARCHAR(64) NOT NULL,
    op CHAR(2) DEFAULT '=',
    value VARCHAR(253) NOT NULL
);

CREATE TABLE radusergroup (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(64) NOT NULL,
    groupname VARCHAR(64) NOT NULL,
    priority INT DEFAULT 1
);

CREATE TABLE radacct (
    id INT PRIMARY KEY AUTO_INCREMENT,
    acctsessionid VARCHAR(64) NOT NULL,
    acctuniqueid VARCHAR(32) NOT NULL,
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
    PRIMARY KEY (id),
    UNIQUE KEY acctuniqueid (acctuniqueid),
    KEY username (username),
    KEY framedipaddress (framedipaddress),
    KEY acctsessionid (acctsessionid),
    KEY acctsessiontime (acctsessiontime),
    KEY acctstarttime (acctstarttime),
    KEY acctstoptime (acctstoptime),
    KEY nasipaddress (nasipaddress)
);

CREATE TABLE radpostauth (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(64),
    pass VARCHAR(64),
    reply VARCHAR(32),
    authdate DATETIME
);

CREATE TABLE nas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nasname VARCHAR(45) NOT NULL,
    shortname VARCHAR(32),
    type VARCHAR(30) DEFAULT 'other',
    ports INT,
    secret VARCHAR(60) DEFAULT 'secret',
    server VARCHAR(64),
    community VARCHAR(50),
    description VARCHAR(200) DEFAULT 'RADIUS Client'
);
```

---

## 5. MVC Framework Structure

### 5.1 Laravel 10.x Structure

```
eco-billing-isp/
├── app/
│   ├── Console/
│   │   └── Kernel.php
│   ├── Exceptions/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   ├── LoginController.php
│   │   │   │   └── RegisterController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── CustomerController.php
│   │   │   ├── PackageController.php
│   │   │   ├── RouterController.php
│   │   │   ├── InvoiceController.php
│   │   │   ├── PaymentController.php
│   │   │   ├── ReportController.php
│   │   │   ├── TicketController.php
│   │   │   ├── SettingController.php
│   │   │   └── RadiusController.php
│   │   ├── Middleware/
│   │   │   ├── Authenticate.php
│   │   │   ├── VerifyCsrfToken.php
│   │   │   └── RoleMiddleware.php
│   │   └── Kernel.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Customer.php
│   │   ├── Package.php
│   │   ├── Router.php
│   │   ├── Invoice.php
│   │   ├── Payment.php
│   │   ├── Ticket.php
│   │   ├── Setting.php
│   │   └── Radius/
│   │       ├── Radreply.php
│   │       ├── Radgroupreply.php
│   │       └── Radacct.php
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   └── RouteServiceProvider.php
│   └── User.php
├── bootstrap/
├── config/
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── public/
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── plugins/
│   ├── index.php
│   └── .htaccess
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   │   ├── app.blade.php
│   │   │   ├── header.blade.php
│   │   │   ├── sidebar.blade.php
│   │   │   └── footer.blade.php
│   │   ├── dashboard/
│   │   ├── customers/
│   │   ├── packages/
│   │   ├── routers/
│   │   ├── invoices/
│   │   ├── payments/
│   │   ├── reports/
│   │   ├── tickets/
│   │   ├── settings/
│   │   ├── radius/
│   │   ├── auth/
│   │   └── portal/
│   └── lang/
├── routes/
│   ├── web.php
│   ├── api.php
│   └── console.php
├── storage/
│   ├── app/
│   ├── framework/
│   └── logs/
├── tests/
├── vendor/
├── artisan
├── composer.json
├── composer.lock
├── package.json
├── server.php
└── .env
```

### 5.2 CodeIgniter 4.x Structure

```
eco-billing-isp/
├── app/
│   ├── Config/
│   │   ├── App.php
│   │   ├── Database.php
│   │   ├── Routes.php
│   │   ├── Migrations.php
│   │   ├── Seeds.php
│   │   └── Validation.php
│   ├── Controllers/
│   │   ├── BaseController.php
│   │   ├── Dashboard.php
│   │   ├── Customers.php
│   │   ├── Packages.php
│   │   ├── Routers.php
│   │   ├── Invoices.php
│   │   ├── Payments.php
│   │   ├── Reports.php
│   │   ├── Tickets.php
│   │   ├── Settings.php
│   │   ├── Radius.php
│   │   └── Auth.php
│   ├── Models/
│   │   ├── UserModel.php
│   │   ├── CustomerModel.php
│   │   ├── PackageModel.php
│   │   ├── RouterModel.php
│   │   ├── InvoiceModel.php
│   │   ├── PaymentModel.php
│   │   ├── TicketModel.php
│   │   └── SettingModel.php
│   ├── Views/
│   │   ├── layout.php
│   │   ├── header.php
│   │   ├── sidebar.php
│   │   ├── footer.php
│   │   ├── dashboard.php
│   │   ├── customers/
│   │   ├── packages/
│   │   └── ...
│   └── Filters/
│       └── AuthFilter.php
├── public/
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── plugins/
│   ├── index.php
│   └── .htaccess
├── writable/
│   ├── cache/
│   ├── logs/
│   └── session/
├── tests/
├── spark
├── composer.json
└── phpunit.xml
```

---

## 6. Module Breakdown

### 6.1 Dashboard Module
- **Controller**: `DashboardController`
- **Model**: `DashboardModel`
- **Views**: `dashboard/index.blade.php`
- **Features**:
  - Statistics cards (Active Users, Online Now, Expired Today, Revenue Today)
  - Sales/Revenue charts (Chart.js)
  - Payment method distribution chart
  - Recent activities feed
  - New users table

### 6.2 Customer Management Module
- **Controller**: `CustomerController`
- **Model**: `CustomerModel`
- **Views**:
  - `customers/index.blade.php` - List with DataTables
  - `customers/create.blade.php` - Add form
  - `customers/edit.blade.php` - Edit form
  - `customers/show.blade.php` - Detail view

**Features**:
- CRUD operations
- Search and filter
- Export to CSV/PDF
- Bulk actions (activate, suspend, delete)
- Status management
- Package assignment

### 6.3 Package Management Module
- **Controller**: `PackageController`
- **Model**: `PackageModel`
- **Views**:
  - `packages/index.blade.php`
  - `packages/create.blade.php`
  - `packages/edit.blade.php`

**Features**:
- CRUD operations
- Package types (Prepaid, Postpaid, Hotspot, PPPoE)
- Speed limits configuration
- Price and validity
- FreeRADIUS group mapping
- One-click sync to RADIUS

### 6.4 Router/NAS Management Module
- **Controller**: `RouterController`
- **Model**: `RouterModel`
- **Views**:
  - `routers/index.blade.php`
  - `routers/create.blade.php`
  - `routers/edit.blade.php`
  - `routers/test.blade.php`

**Features**:
- CRUD operations
- Multiple router types (MikroTik, Cisco, Ubiquiti)
- API integration (MikroTik RouterOS API)
- Connection testing
- Location tracking

### 6.5 Billing & Invoices Module
- **Controller**: `InvoiceController`, `PaymentController`
- **Model**: `InvoiceModel`, `PaymentModel`
- **Views**:
  - `invoices/index.blade.php`
  - `invoices/create.blade.php`
  - `invoices/show.blade.php`
  - `invoices/print.blade.php`
  - `payments/index.blade.php`

**Features**:
- Auto/manual invoice generation
- Payment recording
- Multiple payment methods
- Invoice PDF generation
- Payment history
- Revenue tracking

### 6.6 FreeRADIUS Management Module
- **Controller**: `RadiusController`
- **Model**: `RadreplyModel`, `RadgroupreplyModel`
- **Views**:
  - `radius/groups.blade.php`
  - `radius/replies.blade.php`
  - `radius/test.blade.php`

**Features**:
- RadGroupReply management
- RadReply management
- Speed limit attributes
- Session timeout configuration
- RADIUS authentication testing

### 6.7 Reports Module
- **Controller**: `ReportController`
- **Views**:
  - `reports/index.blade.php`
  - `reports/revenue.blade.php`
  - `reports/customers.blade.php`
  - `reports/payments.blade.php`
  - `reports/online.blade.php`

**Features**:
- Revenue reports
- Customer reports
- Payment reports
- Online users monitoring
- Charts and graphs
- Export functionality

### 6.8 Support Tickets Module
- **Controller**: `TicketController`
- **Model**: `TicketModel`, `TicketReplyModel`
- **Views**:
  - `tickets/index.blade.php`
  - `tickets/create.blade.php`
  - `tickets/show.blade.php`

**Features**:
- Ticket creation
- Priority management
- Status workflow
- Internal notes
- Email notifications

### 6.9 Settings Module
- **Controller**: `SettingController`
- **Model**: `SettingModel`
- **Views**:
  - `settings/index.blade.php`
  - `settings/company.blade.php`
  - `settings/billing.blade.php`
  - `settings/smtp.blade.php`
  - `settings/api.blade.php`

**Features**:
- Company information
- Billing settings
- Email/SMTP configuration
- API settings
- Backup/restore

### 6.10 Customer Portal Module
- **Controller**: `PortalController`
- **Views**:
  - `portal/login.blade.php`
  - `portal/dashboard.blade.php`
  - `portal/invoices.blade.php`
  - `portal/tickets.blade.php`
  - `portal/profile.blade.php`

**Features**:
- Self-service login
- View invoices and payments
- Create support tickets
- Profile management
- Password change

---

## 7. FreeRADIUS Integration

### 7.1 MariaDB Configuration for FreeRADIUS

**File**: `/etc/raddb/mods-available/sql`

```ini
sql {
    driver = "rlm_sql_mysql"
    dialect = "mysql"
    
    # Connection info
    server = "localhost"
    port = 3306
    login = "freeradius"
    password = "freeradius_password"
    
    # Database name (RADIUS database)
    radius_db = "radius"
    
    # Table names
    acct_table1 = "radacct"
    acct_table2 = "radacct"
    postauth_table = "radpostauth"
    authcheck_table = "radcheck"
    authreply_table = "radreply"
    groupcheck_table = "radgroupcheck"
    groupreply_table = "radgroupreply"
    usergroup_table = "radusergroup"
    
    # Pool configuration
    pool {
        start = 5
        min = 3
        max = 20
        idle = 60
        retry = 1
        uses = 0
    }
}
```

### 7.2 Site Configuration

**File**: `/etc/raddb/sites-available/default`

```radius
server default {
    listen {
        type = auth
        ipaddr = *
        port = 1812
    }
    
    listen {
        type = acct
        ipaddr = *
        port = 1813
    }
    
    authorize {
        preprocess
        suffix
        SQL
        expiration
        logintime
    }
    
    authenticate {
        Auth-Type PAP {
            pap
        }
        Auth-Type CHAP {
            chap
        }
        MS-CHAP {
            mschap
        }
        SQL
    }
    
    accounting {
        detail
        SQL
        attr_filter.accounting_response
    }
    
    session {
        radutmp
        SQL
    }
    
    post-auth {
        update {
            &reply: += &session-state:
        }
        SQL
        Post-Auth-Type REJECT {
            attr_filter.access_reject
            eap
            ok
            reject
        }
    }
    
    pre-proxy {
        eap
    }
    
    post-proxy {
        eap
    }
}
```

### 7.3 Application Sync to RADIUS

**Laravel Model/Service**: `RadiusService.php`

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class RadiusService
{
    protected $radiusDb;
    
    public function __construct()
    {
        $this->radiusDb = DB::connection('radius');
    }
    
    /**
     * Create user in RADIUS database
     */
    public function createUser($username, $password, $group = null)
    {
        // Add to radcheck table
        $this->radiusDb->table('radcheck')->insert([
            'username' => $username,
            'attribute' => 'Cleartext-Password',
            'op' => ':=',
            'value' => $password
        ]);
        
        // Add to usergroup if group specified
        if ($group) {
            $this->addToGroup($username, $group);
        }
        
        return true;
    }
    
    /**
     * Add user to RADIUS group
     */
    public function addToGroup($username, $groupname, $priority = 1)
    {
        $this->radiusDb->table('radusergroup')->insert([
            'username' => $username,
            'groupname' => $groupname,
            'priority' => $priority
        ]);
    }
    
    /**
     * Remove user from RADIUS group
     */
    public function removeFromGroup($username, $groupname)
    {
        $this->radiusDb->table('radusergroup')
            ->where('username', $username)
            ->where('groupname', $groupname)
            ->delete();
    }
    
    /**
     * Sync package to RADIUS group
     */
    public function syncPackageToGroup($package)
    {
        // Remove existing attributes for this group
        $this->radiusDb->table('radgroupreply')
            ->where('groupname', $package->radius_group)
            ->delete();
        
        // Add bandwidth limit (Mikrotik format)
        $this->radiusDb->table('radgroupreply')->insert([
            'groupname' => $package->radius_group,
            'attribute' => 'Mikrotik-Rate-Limit',
            'op' => '=',
            'value' => "{$package->download_speed}M/{$package->upload_speed}M"
        ]);
        
        // Add interim interval
        $this->radiusDb->table('radgroupreply')->insert([
            'groupname' => $package->radius_group,
            'attribute' => 'Acct-Interim-Interval',
            'op' => '=',
            'value' => '300'
        ]);
        
        // Add session timeout
        $this->radiusDb->table('radgroupreply')->insert([
            'groupname' => $package->radius_group,
            'attribute' => 'Session-Timeout',
            'op' => '=',
            'value' => $package->valid_days * 86400
        ]);
    }
    
    /**
     * Get online users from radacct
     */
    public function getOnlineUsers()
    {
        return $this->radiusDb->table('radacct')
            ->whereNull('acctstoptime')
            ->get();
    }
    
    /**
     * Test RADIUS authentication
     */
    public function testAuth($username, $password)
    {
        $radtest = shell_exec("echo 'User-Name={$username}, Cleartext-Password={$password}' | radclient -s 127.0.0.1 auth testing123");
        return strpos($radtest, 'Access-Accept') !== false;
    }
}
```

---

## 8. API Endpoints (RESTful)

### 8.1 Authentication
```
POST /api/v1/auth/login
POST /api/v1/auth/logout
GET  /api/v1/auth/me
POST /api/v1/auth/refresh
```

### 8.2 Customers
```
GET    /api/v1/customers           # List customers
POST   /api/v1/customers           # Create customer
GET    /api/v1/customers/{id}       # Get customer
PUT    /api/v1/customers/{id}      # Update customer
DELETE /api/v1/customers/{id}     # Delete customer
GET    /api/v1/customers/{id}/invoices
PUT    /api/v1/customers/{id}/status
```

### 8.3 Packages
```
GET    /api/v1/packages            # List packages
POST   /api/v1/packages           # Create package
GET    /api/v1/packages/{id}       # Get package
PUT    /api/v1/packages/{id}       # Update package
DELETE /api/v1/packages/{id}       # Delete package
POST   /api/v1/packages/{id}/sync   # Sync to RADIUS
```

### 8.4 Routers
```
GET    /api/v1/routers             # List routers
POST   /api/v1/routers            # Add router
GET    /api/v1/routers/{id}        # Get router
PUT    /api/v1/routers/{id}        # Update router
DELETE /api/v1/routers/{id}        # Delete router
POST   /api/v1/routers/{id}/test    # Test connection
```

### 8.5 Invoices
```
GET    /api/v1/invoices            # List invoices
POST   /api/v1/invoices            # Create invoice
GET    /api/v1/invoices/{id}       # Get invoice
PUT    /api/v1/invoices/{id}       # Update invoice
POST   /api/v1/invoices/{id}/pay   # Mark as paid
GET    /api/v1/invoices/{id}/pdf   # Generate PDF
```

### 8.6 Payments
```
GET    /api/v1/payments            # List payments
POST   /api/v1/payments            # Record payment
GET    /api/v1/payments/{id}       # Get payment
```

### 8.7 Reports
```
GET    /api/v1/reports/revenue     # Revenue report
GET    /api/v1/reports/customers   # Customer report
GET    /api/v1/reports/payments    # Payment report
GET    /api/v1/reports/online      # Online users
GET    /api/v1/reports/dashboard   # Dashboard data
```

### 8.8 Tickets
```
GET    /api/v1/tickets             # List tickets
POST   /api/v1/tickets             # Create ticket
GET    /api/v1/tickets/{id}        # Get ticket
PUT    /api/v1/tickets/{id}        # Update ticket
POST   /api/v1/tickets/{id}/reply  # Add reply
PUT    /api/v1/tickets/{id}/status # Update status
```

---

## 9. Nginx Configuration

### 9.1 Main Configuration

**File**: `/etc/nginx/conf.d/isp-billing.conf`

```nginx
server {
    listen 80;
    server_name billing.yourdomain.com;
    
    # Redirect to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name billing.yourdomain.com;
    
    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/billing.yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/billing.yourdomain.com/privkey.pem;
    ssl_session_timeout 1d;
    ssl_session_cache shared:SSL:50m;
    ssl_session_tickets off;
    
    # Modern SSL configuration
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    
    # HSTS
    add_header Strict-Transport-Security "max-age=63072000" always;
    
    # Root directory
    root /var/www/eco-billing-isp/public;
    index index.php index.html;
    
    # Access log
    access_log /var/log/nginx/isp-billing-access.log;
    error_log /var/log/nginx/isp-billing-error.log;
    
    # Main location
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # PHP-FPM configuration
    location ~ \.php$ {
        include /etc/nginx/fastcgi_params;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PHP_VALUE "upload_max_filesize=10M \n post_max_size=10M";
        fastcgi_buffering on;
        fastcgi_buffers 16 16k;
        fastcgi_busy_buffers_size 32k;
    }
    
    # Deny access to hidden files
    location ~ /\. {
        deny all;
    }
    
    # Cache static files
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2)$ {
        expires 7d;
        add_header Cache-Control "public, immutable";
    }
    
    # Upload directory
    location /uploads/ {
        expires 1d;
        add_header Cache-Control "public";
    }
}
```

### 9.2 PHP-FPM Pool Configuration

**File**: `/etc/php/8.2/fpm/pool.d/isp-billing.conf`

```ini
[isp-billing]
; Pool name
user = nginx
group = nginx

; Socket
listen = /run/php/php8.2-fpm.sock
listen.owner = nginx
listen.group = nginx
listen.mode = 0660

; Process manager
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500

; Environment
clear_env = no

; Chroot
chroot = /var/www/eco-billing-isp

; Chdir
chdir = /

; Redirect worker stdout/stderr
catch_workers_output = yes

; Decorate worker output
decorate_workers_output = no

; Performance
php_admin_value[error_log] = /var/log/php-fpm/isp-billing-error.log
php_admin_flag[log_errors] = on

; Memory limit
php_admin_value[memory_limit] = 256M

; Upload settings
php_admin_value[upload_max_filesize] = 10M
php_admin_value[post_max_size] = 10M

; Session
php_admin_value[session.save_path] = /var/lib/php/session
```

---

## 10. Security Features

### 10.1 Authentication & Authorization
- Session-based authentication (Laravel Session / CodeIgniter Session)
- Role-based access control (RBAC)
- Password hashing (bcrypt/Argon2)
- CSRF protection
- XSS prevention
- Rate limiting

### 10.2 Data Protection
- SQL injection prevention (Eloquent ORM / Query Builder)
- Input validation and sanitization
- File upload validation
- Sensitive data encryption
- Database encryption at rest (optional)

### 10.3 Network Security
- FreeRADIUS security best practices
- API authentication (Sanctum / JWT)
- IP whitelisting for admin panel
- SSL/TLS encryption (Let's Encrypt)
- Firewall configuration (firewalld)

### 10.4 Application Security Headers
```nginx
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
add_header Content-Security-Policy "default-src 'self' 'unsafe-inline' 'unsafe-eval';" always;
```

---

## 11. Installation & Deployment

### 11.1 Server Setup (AlmaLinux 9)

```bash
# Update system
sudo dnf update -y

# Install EPEL repository
sudo dnf install epel-release -y

# Install Nginx
sudo dnf install nginx -y
sudo systemctl enable --now nginx

# Install PHP 8.2
sudo dnf module reset php -y
sudo dnf module enable php:8.2 -y
sudo dnf install php-fpm php-cli php-mysql php-curl php-gd php-mbstring php-xml php-json php-tokenizer php-bcmath -y
sudo systemctl enable --now php-fpm

# Install MariaDB 10
sudo dnf install mariadb-server mariadb -y
sudo systemctl enable --now mariadb

# Secure MariaDB
sudo mysql_secure_installation

# Install FreeRADIUS
sudo dnf install freeradius freeradius-mysql freeradius-utils -y
sudo systemctl enable --now freeradius

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Git and other tools
sudo dnf install git curl wget unzip -y
```

### 11.2 Database Setup

```bash
# Create databases and users
sudo mysql -u root -p

-- Create app database
CREATE DATABASE isp_billing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'ispuser'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON isp_billing.* TO 'ispuser'@'localhost';
FLUSH PRIVILEGES;

-- Create RADIUS database
CREATE DATABASE radius CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'freeradius'@'localhost' IDENTIFIED BY 'radius_password_here';
GRANT ALL PRIVILEGES ON radius.* TO 'freeradius'@'localhost';
FLUSH PRIVILEGES;

EXIT;

# Import RADIUS schema
sudo mysql -u freeradius -p radius < /etc/raddb/mods-config/sql/main/mysql/schema.sql
```

### 11.3 Application Setup

```bash
# Navigate to web directory
cd /var/www/

# Clone repository
sudo git clone https://github.com/arifislam007/eco-billing-isp.git
cd eco-billing-isp

# Set permissions
sudo chown -R nginx:nginx /var/www/eco-billing-isp/
sudo chmod -R 755 /var/www/eco-billing-isp/
sudo chmod -R 775 storage/
sudo chmod -R 775 bootstrap/cache/
sudo chmod -R 775 uploads/

# Install dependencies
composer install --no-dev --optimize-autoloader

# Copy environment file
cp .env.example .env
nano .env

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Seed database (optional)
php artisan db:seed

# Configure storage link
php artisan storage:link

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 11.4 FreeRADIUS Configuration

```bash
# Enable SQL module
sudo ln -s /etc/raddb/mods-available/sql /etc/raddb/mods-enabled/

# Configure RADIUS SQL connection
sudo nano /etc/raddb/mods-available/sql

# Add RADIUS client
sudo nano /etc/raddb/clients.conf

# Restart FreeRADIUS
sudo systemctl restart freeradius

# Test FreeRADIUS
radtest testuser testpass localhost 0 testing123
```

### 11.5 SSL Configuration (Let's Encrypt)

```bash
# Install Certbot
sudo dnf install certbot python3-certbot-nginx -y

# Obtain SSL certificate
sudo certbot --nginx -d billing.yourdomain.com

# Auto-renewal
sudo systemctl enable --now certbot-renew.timer
```

---

## 12. Implementation Phases

### Phase 1: Core Foundation (Week 1)
- [ ] Setup project with Laravel/CodeIgniter
- [ ] Configure database connections (app + RADIUS)
- [ ] Implement authentication system
- [ ] Build layout templates (header, sidebar, footer)
- [ ] Create dashboard with basic stats

### Phase 2: Customer Management (Week 2)
- [ ] Customer CRUD operations
- [ ] Customer search and filtering
- [ ] Customer status management
- [ ] Bulk actions functionality
- [ ] Export to CSV/PDF

### Phase 3: Package Management (Week 3)
- [ ] Package CRUD operations
- [ ] Package types implementation
- [ ] Speed limit configuration
- [ ] FreeRADIUS group sync
- [ ] Router/NAS management

### Phase 4: Billing System (Week 4)
- [ ] Invoice generation
- [ ] Payment recording
- [ ] Payment methods
- [ ] Invoice PDF generation
- [ ] Revenue reports

### Phase 5: FreeRADIUS Integration (Week 5)
- [ ] RADIUS server configuration
- [ ] NAS device integration
- [ ] Authentication testing
- [ ] Bandwidth limiting setup
- [ ] Online user monitoring

### Phase 6: Reports & Analytics (Week 6)
- [ ] Sales reports
- [ ] Customer reports
- [ ] Payment reports
- [ ] Charts and graphs
- [ ] Export functionality

### Phase 7: Support System (Week 7)
- [ ] Ticket creation
- [ ] Ticket management
- [ ] Customer portal tickets
- [ ] Email notifications

### Phase 8: Settings & Portal (Week 8)
- [ ] Company settings
- [ ] Billing settings
- [ ] Email configuration
- [ ] Customer self-service portal
- [ ] Final testing and deployment

---

## 13. Testing Plan

### 13.1 Unit Testing
- Feature tests (Laravel Feature Tests)
- Unit tests for services
- Database migrations
- API endpoint tests

### 13.2 Integration Testing
- FreeRADIUS authentication flow
- Payment processing
- Email functionality
- API responses

### 13.3 User Acceptance Testing
- Admin panel workflow
- Customer portal workflow
- Edge cases handling
- Performance testing

---

## 14. Documentation Requirements

### 14.1 User Documentation
- Administrator manual
- Customer user guide
- API documentation
- Installation guide

### 14.2 Technical Documentation
- Database schema reference
- API endpoint reference
- Code documentation
- Deployment procedures

---

## 15. Future Enhancements

- [ ] Mobile App (React Native/Flutter)
- [ ] Payment Gateway Integration (Stripe, PayPal, bKash)
- [ ] SMS Notifications (Twilio, local SMS gateway)
- [ ] Email Marketing Integration
- [ ] Multi-location Support
- [ ] Advanced Analytics Dashboard
- [ ] Automated Expiry Reminders
- [ ] Bulk SMS/Email System
- [ ] Two-Factor Authentication
- [ ] Docker Containerization
- [ ] Kubernetes Deployment
- [ ] CI/CD Pipeline (GitHub Actions)

---

## 16. Estimated Budget

| Item | Description | Estimated Cost |
|------|-------------|----------------|
| Domain & SSL | Annual domain + SSL certificate | $50/year |
| VPS Hosting | 4GB RAM, 2 CPU, 50GB SSD | $20/month |
| SMS Gateway | 10,000 SMS/month | $50/month |
| Email Service | Professional email + automation | $20/month |

---

## 17. Conclusion

This architectural plan provides a comprehensive blueprint for building a robust ISP Billing Software system with FreeRADIUS backend integration using AlmaLinux 9, Nginx, PHP 8.2, and MariaDB 10. The MVC framework (Laravel 10 or CodeIgniter 4) ensures maintainability and scalability, while the dual MariaDB setup (app + RADIUS) provides reliable data management.

The phased implementation approach allows for gradual development and testing, reducing risks and ensuring a stable release. Regular testing and documentation throughout the development lifecycle will ensure a high-quality, production-ready system.
