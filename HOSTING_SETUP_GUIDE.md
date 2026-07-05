# VPS Hosting Setup Commands

## Ubuntu 20.04/22.04 Setup

### 1. Update System
```bash
sudo apt update && sudo apt upgrade -y
```

### 2. Install LAMP Stack
```bash
# Apache
sudo apt install apache2 -y
sudo systemctl start apache2
sudo systemctl enable apache2

# MySQL
sudo apt install mysql-server -y
sudo mysql_secure_installation

# PHP 8.1
sudo apt install php8.1 php8.1-mysql php8.1-curl php8.1-gd php8.1-mbstring php8.1-xml php8.1-zip -y
sudo systemctl restart apache2
```

### 3. Configure MySQL
```bash
sudo mysql -u root -p
CREATE DATABASE pk_live_news;
CREATE USER 'pklivenews'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON pk_live_news.* TO 'pklivenews'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 4. Setup Website Files
```bash
cd /var/www/html
sudo rm index.html
sudo git clone your-repo-url .
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html
```

### 5. Configure Apache Virtual Host
```bash
sudo nano /etc/apache2/sites-available/pklivenews.conf
```

Add this content:
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/html
    
    <Directory /var/www/html>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

Enable site and rewrite:
```bash
sudo a2ensite pklivenews.conf
sudo a2dissite 000-default.conf
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### 6. SSL Certificate (Let's Encrypt)
```bash
sudo apt install certbot python3-certbot-apache -y
sudo certbot --apache -d your-domain.com
```

## CentOS 7/8 Setup

### 1. Update System
```bash
sudo yum update -y
```

### 2. Install LAMP Stack
```bash
# Apache
sudo yum install httpd -y
sudo systemctl start httpd
sudo systemctl enable httpd

# MariaDB
sudo yum install mariadb-server -y
sudo systemctl start mariadb
sudo systemctl enable mariadb
sudo mysql_secure_installation

# PHP 8.1
sudo yum install php php-mysql php-curl php-gd php-mbstring php-xml php-zip -y
sudo systemctl restart httpd
```

## Security Hardening

### 1. Firewall Setup
```bash
# Ubuntu (UFW)
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable

# CentOS (firewalld)
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --reload
```

### 2. PHP Security
```bash
sudo nano /etc/php/8.1/apache2/php.ini
```
Update these settings:
```ini
expose_php = Off
display_errors = Off
log_errors = On
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 30
```

### 3. Database Security
```bash
sudo mysql -u root -p
DELETE FROM mysql.user WHERE User='';
DROP DATABASE IF EXISTS test;
FLUSH PRIVILEGES;
```

## Performance Optimization

### 1. Enable Apache Caching
```bash
sudo a2enmod expires
sudo a2enmod headers
sudo systemctl restart apache2
```

### 2. Install PHP OPcache
```bash
sudo apt install php8.1-opcache -y
sudo systemctl restart apache2
```

### 3. MySQL Optimization
```bash
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```
Add under `[mysqld]`:
```ini
innodb_buffer_pool_size = 256M
innodb_log_file_size = 64M
query_cache_size = 32M
query_cache_type = 1
```

## Monitoring & Maintenance

### 1. Setup Log Rotation
```bash
sudo nano /etc/logrotate.d/apache2
```

### 2. Setup Backups
```bash
# Create backup script
sudo nano /usr/local/bin/backup.sh
```

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u root -p pk_live_news > /backups/pklivenews_$DATE.sql
tar -czf /backups/files_$DATE.tar.gz /var/www/html/uploads
find /backups -name "*.sql" -mtime +7 -delete
find /backups -name "*.tar.gz" -mtime +7 -delete
```

```bash
sudo chmod +x /usr/local/bin/backup.sh
sudo crontab -e
```
Add: `0 2 * * * /usr/local/bin/backup.sh`
