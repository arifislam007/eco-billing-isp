# ISP Billing System - Deployment Guide for AlmaLinux 9

## Prerequisites
- AlmaLinux 9 (or RHEL 9 / Rocky Linux 9)
- SSH access to server
- Root or sudo privileges

## Step 1: Update System and Install Dependencies

```bash
# Update system
sudo dnf update -y

# Install required packages
sudo dnf install -y epel-release
sudo dnf install -y wget unzip vim git

# Install Nginx
sudo dnf install -y nginx
sudo systemctl enable nginx
sudo systemctl start nginx

# Install PHP 8.2 and extensions
sudo dnf install -y php php-fpm php-cli php-mysql php-gd php-mbstring php-xml php-curl php-tokenizer php-json php-fileinfo
sudo systemctl enable php-fpm
sudo systemctl start php-fpm

# Install MariaDB 10.x
sudo dnf install -y mariadb-server mariadb
sudo systemctl enable mariadb
sudo systemctl start mariadb
```

## Step 2: Configure Database

```bash
# Secure MariaDB installation
sudo mysql_secure_installation

# Login to MariaDB
sudo mysql -u root -p

# Execute SQL commands:
CREATE USER 'billing'@'localhost' IDENTIFIED BY 'Billing123';
CREATE DATABASE isp_billing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE radius CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON isp_billing.* TO 'billing'@'localhost';
GRANT ALL PRIVILEGES ON radius.* TO 'billing'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Import database schema
mysql -u billing -pBilling123 < database/schema.sql
```

## Step 3: Install FreeRADIUS 3.x

```bash
# Enable FreeRADIUS repository
sudo dnf install -y https://rpm.freeradius.org/releases/freeradius-release-3.0.x-9.el7.x86_64.rpm 2>/dev/null || true

# Alternative: Install from EPEL
sudo dnf install -y freeradius freeradius-mysql freeradius-utils
sudo systemctl enable freeradius
sudo systemctl start freeradius

# Copy FreeRADIUS configuration
sudo cp freeradius/sql/mysql/dialup.conf /etc/raddb/mods-available/dialup.conf
sudo cp freeradius/clients.conf /etc/raddb/clients.conf
sudo cp freeradius/sites-available/default /etc/raddb/sites-available/default

# Enable SQL module
sudo ln -s /etc/raddb/mods-available/dialup.conf /etc/raddb/mods-enabled/dialup.conf

# Restart FreeRADIUS
sudo systemctl restart freeradius
```

## Step 4: Deploy Application

```bash
# Create web directory
sudo mkdir -p /var/www/html/isp-billing
sudo chown -R nginx:nginx /var/www/html/isp-billing

# Clone repository
cd /var/www/html/isp-billing
git clone -b v3.0 https://github.com/arifislam007/eco-billing-isp.git .

# Set permissions
sudo chmod -R 755 /var/www/html/isp-billing
sudo chown -R nginx:nginx /var/www/html/isp-billing
```

## Step 5: Configure Nginx

```bash
# Create Nginx configuration
sudo vim /etc/nginx/conf.d/isp-billing.conf
```

Add this configuration:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/html/isp-billing/public;
    index index.php index.html;

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

    # URL rewriting
    location /admin {
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

# Set SELinux permissions (if enabled)
sudo setsebool -P httpd_can_network_connect on
```

## Step 6: Configure Firewall

```bash
# Allow HTTP/HTTPS
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https

# Allow RADIUS ports (if needed)
sudo firewall-cmd --permanent --add-port=1812-1813/udp

# Reload firewall
sudo firewall-cmd --reload
```

## Step 7: Set Up SSL (Recommended)

```bash
# Install Certbot
sudo dnf install -y certbot python3-certbot-nginx

# Obtain SSL certificate
sudo certbot --nginx -d your-domain.com

# Auto-renewal
sudo systemctl enable certbot-renew.timer
```

## Step 8: Access Application

1. Open browser and navigate to: `http://your-domain.com/admin`
2. Login with default credentials:
   - Username: `admin`
   - Password: `admin123`

## Step 9: Configure FreeRADIUS (Optional)

```bash
# Test RADIUS connection
radtest testing testing123 localhost 0 testing123

# Check FreeRADIUS status
sudo systemctl status freeradius

# View logs
sudo tail -f /var/log/radius/radius.log
```

## File Locations

| Item | Location |
|------|----------|
| Web Root | `/var/www/html/isp-billing` |
| Nginx Config | `/etc/nginx/conf.d/isp-billing.conf` |
| FreeRADIUS Config | `/etc/raddb/` |
| Application Logs | `/var/log/nginx/` |
| PHP Config | `/etc/php.ini` |

## Troubleshooting

```bash
# Check PHP-FPM status
sudo systemctl status php-fpm

# Check Nginx error logs
sudo tail -f /var/log/nginx/error.log

# Check application errors
sudo tail -f /var/www/html/isp-billing/writable/logs/

# Restart services
sudo systemctl restart nginx php-fpm mariadb freeradius
```

## Update Application

```bash
cd /var/www/html/isp-billing
git fetch origin
git checkout v3.0
git pull origin v3.0
sudo chown -R nginx:nginx /var/www/html/isp-billing
```
