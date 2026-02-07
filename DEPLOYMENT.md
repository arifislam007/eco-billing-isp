# ISP Billing System - Deployment Guide for AlmaLinux 9

This guide covers deployment with either **Apache** or **Nginx** web server using server IP address (no domain required).

## Prerequisites
- AlmaLinux 9 (or RHEL 9 / Rocky Linux 9)
- SSH access to server
- Root or sudo privileges
- Server IP address (e.g., `192.168.1.100`)

## Step 1: Update System and Install Dependencies

```bash
# Update system
sudo dnf update -y

# Install basic tools
sudo dnf install -y epel-release wget unzip vim git

# Install PHP 8.2 and extensions
sudo dnf php-cli php-common php-f install -y phppm php-mysqlnd php-gd php-mbstring php-xml php-curl php-json php-tokenizer php-fileinfo php-bcmath
```

## Step 2: Choose Web Server (Choose ONE)

### Option A: Apache (Recommended for simplicity)
```bash
# Install Apache
sudo dnf install -y httpd

# Enable and start Apache
sudo systemctl enable httpd
sudo systemctl start httpd

# Install PHP module for Apache
sudo dnf install -y php-common php-cli php-httpd
sudo systemctl restart httpd
```

### Option B: Nginx
```bash
# Install Nginx
sudo dnf install -y nginx

# Enable and start Nginx
sudo systemctl enable nginx
sudo systemctl start nginx
```

## Step 3: Install MariaDB

```bash
# Install MariaDB
sudo dnf install -y mariadb-server mariadb

# Enable and start MariaDB
sudo systemctl enable mariadb
sudo systemctl start mariadb

# Secure MariaDB installation
sudo mysql_secure_installation
```

## Step 4: Configure Database

```bash
# Login to MariaDB as root
sudo mysql -u root -p
```

Execute these SQL commands:

```sql
CREATE USER 'billing'@'localhost' IDENTIFIED BY 'Billing123';
CREATE DATABASE isp_billing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE radius CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON isp_billing.* TO 'billing'@'localhost';
GRANT ALL PRIVILEGES ON radius.* TO 'billing'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

Import the database schema:

```bash
mysql -u billing -pBilling123 < database/schema.sql
```

## Step 5: Deploy Application

```bash
# Create web directory
sudo mkdir -p /var/www/html/isp-billing

# Clone repository
cd /var/www/html/isp-billing
sudo git clone -b v3.0 https://github.com/arifislam007/eco-billing-isp.git .

# Set permissions
sudo chmod -R 755 /var/www/html/isp-billing
sudo chown -R apache:apache /var/www/html/isp-billing  # For Apache
# OR for Nginx:
# sudo chown -R nginx:nginx /var/www/html/isp-billing
```

## Step 6: Configure Web Server

### Option A: Apache Virtual Host

```bash
# Create Apache configuration
sudo vim /etc/httpd/conf.d/isp-billing.conf
```

Add this configuration:

```apache
Listen 80

<VirtualHost *:80>
    ServerName YOUR_SERVER_IP
    DocumentRoot /var/www/html/isp-billing/public

    <Directory /var/www/html/isp-billing>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    <Directory /var/www/html/isp-billing/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # PHP-FPM configuration
    <FilesMatch \.php$>
        SetHandler "proxy:unix:/var/run/php-fpm/www.sock|fcgi://localhost"
    </FilesMatch>

    # Increase upload size
    LimitRequestBody 52428800

    ErrorLog /var/log/httpd/isp-billing-error.log
    CustomLog /var/log/httpd/isp-billing-access.log combined
</VirtualHost>
```

```bash
# Test and reload Apache
sudo httpd -t
sudo systemctl restart httpd

# Set SELinux permissions
sudo setsebool -P httpd_can_network_connect on
```

### Option B: Nginx Server Block

```bash
# Create Nginx configuration
sudo vim /etc/nginx/conf.d/isp-billing.conf
```

Add this configuration:

```nginx
server {
    listen 80;
    server_name YOUR_SERVER_IP;
    root /var/www/html/isp-billing/public;
    index index.php index.html;

    # Access and error logs
    access_log /var/log/nginx/isp-billing-access.log;
    error_log /var/log/nginx/isp-billing-error.log;

    # PHP-FPM configuration
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php-fpm/www.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Main location
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Deny access to hidden files
    location ~ /\.ht {
        deny all;
    }

    # Increase upload size
    client_max_body_size 50M;
}
```

```bash
# Test and reload Nginx
sudo nginx -t
sudo systemctl reload nginx

# Set SELinux permissions
sudo setsebool -P httpd_can_network_connect on
```

## Step 7: Configure Firewall

```bash
# Allow HTTP
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https

# Reload firewall
sudo firewall-cmd --reload

# Check firewall status
sudo firewall-cmd --list-all
```

## Step 8: Start PHP-FPM

```bash
# Enable and start PHP-FPM
sudo systemctl enable php-fpm
sudo systemctl start php-fpm
```

## Step 9: Access Application

Open your browser and navigate to:

```
http://YOUR_SERVER_IP/admin
```

Replace `YOUR_SERVER_IP` with your actual server IP address, e.g.:
```
http://192.168.1.100/admin
```

### Default Login Credentials:
- **Username:** `admin`
- **Password:** `admin123`

## Step 10: Install FreeRADIUS (Optional)

```bash
# Install FreeRADIUS
sudo dnf install -y freeradius freeradius-mysql freeradius-utils

# Enable and start
sudo systemctl enable freeradius
sudo systemctl start freeradius

# Copy configuration files
sudo cp freeradius/sql/mysql/dialup.conf /etc/raddb/mods-available/
sudo cp freeradius/clients.conf /etc/raddb/
sudo ln -s /etc/raddb/mods-available/dialup.conf /etc/raddb/mods-enabled/dialup.conf

# Restart FreeRADIUS
sudo systemctl restart freeradius
```

## File Locations Reference

| Item | Apache Location | Nginx Location |
|------|-----------------|----------------|
| Web Root | `/var/www/html/isp-billing` | `/var/www/html/isp-billing` |
| Config File | `/etc/httpd/conf.d/isp-billing.conf` | `/etc/nginx/conf.d/isp-billing.conf` |
| Apache/Nginx User | `apache` | `nginx` |
| PHP-FPM Socket | `/var/run/php-fpm/www.sock` | `/var/run/php-fpm/www.sock` |
| Error Log | `/var/log/httpd/isp-billing-error.log` | `/var/log/nginx/isp-billing-error.log` |

## Troubleshooting

### Check Service Status
```bash
# Apache
sudo systemctl status httpd

# Nginx
sudo systemctl status nginx

# MariaDB
sudo systemctl status mariadb

# PHP-FPM
sudo systemctl status php-fpm
```

### View Logs
```bash
# Apache errors
sudo tail -f /var/log/httpd/isp-billing-error.log

# Nginx errors
sudo tail -f /var/log/nginx/isp-billing-error.log

# PHP errors
sudo tail -f /var/log/php-fpm/www-error.log

# Application logs
sudo tail -f /var/www/html/isp-billing/writable/logs/
```

### Restart All Services
```bash
# Apache
sudo systemctl restart httpd php-fpm mariadb

# Nginx
sudo systemctl restart nginx php-fpm mariadb
```

### Fix Permissions
```bash
# For Apache
sudo chown -R apache:apache /var/www/html/isp-billing
sudo chmod -R 755 /var/www/html/isp-billing
sudo chmod -R 755 /var/www/html/isp-billing/writable

# For Nginx
sudo chown -R nginx:nginx /var/www/html/isp-billing
sudo chmod -R 755 /var/www/html/isp-billing
sudo chmod -R 755 /var/www/html/isp-billing/writable
```

### Clear Cache
```bash
sudo rm -rf /var/www/html/isp-billing/writable/cache/*
sudo rm -rf /var/www/html/isp-billing/writable/logs/*
```

## Update Application

```bash
cd /var/www/html/isp-billing
sudo git fetch origin
sudo git checkout v3.0
sudo git pull origin v3.0

# Fix permissions after update
sudo chown -R apache:apache /var/www/html/isp-billing  # or nginx
sudo chmod -R 755 /var/www/html/isp-billing

# Clear cache
sudo rm -rf /var/www/html/isp-billing/writable/cache/*

# Restart services
sudo systemctl restart httpd php-fpm  # or nginx
```

## Security Recommendations (After Testing)

1. **Change default admin password immediately**
2. **Configure firewall to allow only necessary ports**
3. **Regular backups of database:**
   ```bash
   mysqldump -u billing -pBilling123 isp_billing > backup_isp_billing_$(date +%Y%m%d).sql
   ```
4. **Keep system updated:**
   ```bash
   sudo dnf update -y
   ```
