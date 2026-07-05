# Hostinger Hosting Setup Guide

## Overview

This guide will help you deploy your PK Live News website on Hostinger shared hosting or VPS.

## Prerequisites

- Hostinger hosting account (Shared or VPS)
- Domain name configured
- PHP 8.0+ support
- MySQL database access

## Step 1: Prepare Your Files

### 1.1 Export Database

```bash
# From your local XAMPP
mysqldump -u root -p pk_live_news > pk_live_news_backup.sql
```

### 1.2 Compress Your Files

```bash
# Create a zip file of your project
zip -r pk_live_news.zip . -x "*.git*" "backups/*" "logs/*"
```

## Step 2: Hostinger Setup

### 2.1 For Shared Hosting

1. Log into Hostinger cPanel
2. Go to **File Manager**
3. Upload and extract `pk_live_news.zip` to `public_html/`
4. Set proper permissions:
   - All folders: 755
   - All PHP files: 644
   - Upload folders: 755

### 2.2 For VPS Hosting

1. SSH into your Hostinger VPS
2. Install LAMP stack if not already installed
3. Upload files to `/var/www/html/`

## Step 3: Database Configuration

### 3.1 Create Database on Hostinger

1. In cPanel, go to **MySQL Databases**
2. Create a new database: `pklivenews_main`
3. Create a database user with strong password
4. Grant all privileges to the user

### 3.2 Import Database

1. In cPanel, go to **phpMyAdmin**
2. Select your new database
3. Click **Import** and upload `pk_live_news_backup.sql`

## Step 4: Environment Configuration

### 4.1 Create .env File

Create a `.env` file in your root directory with:

```env
# Database Configuration
DB_HOST=localhost
DB_USER=u129650532_ibraheem
DB_PASS=your_database_password
DB_NAME=u129650532_ibraheem

# Site Configuration
SITE_URL=https://pk-news.com
SITE_NAME=PK Live News
APP_ENV=production

# Email Configuration (Hostinger SMTP)
SMTP_HOST=smtp.hostinger.com
SMTP_USER=your_email@your-domain.com
SMTP_PASS=your_email_password
SMTP_PORT=587
SMTP_SECURE=tls

# File Upload Configuration
UPLOAD_PATH=uploads/
MAX_FILE_SIZE=5242880

# Security
ADMIN_EMAIL=admin@your-domain.com
SUPPORT_EMAIL=support@your-domain.com
```

### 4.2 Update Database Configuration

Edit `config/database.php` to use Hostinger settings:

```php
// Production settings for Hostinger
if (APP_ENV === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
}
```

## Step 5: File Permissions

### 5.1 Set Correct Permissions

```bash
# For shared hosting (via File Manager or FTP)
# Folders: 755
# Files: 644
# Upload directories: 755

# For VPS via SSH
chmod -R 755 /var/www/html/
chmod -R 644 /var/www/html/*.php
chmod -R 755 /var/www/html/uploads/
chown -R www-data:www-data /var/www/html/
```

## Step 6: .htaccess Configuration

### 6.1 Production .htaccess

Replace your `.htaccess` with production-ready version:

```apache
# Enable URL rewriting
RewriteEngine On

# Security headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection '1; mode=block'
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# PHP settings (production)
<IfModule mod_php.c>
    php_flag display_errors Off
    php_value error_reporting 0
    php_value memory_limit 256M
    php_value max_execution_time 120
    php_value upload_max_filesize 20M
    php_value post_max_size 20M
    php_flag log_errors On
    php_value error_log /tmp/php_errors.log
</IfModule>

# URL routing
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

# Cache control for static files
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
    ExpiresByType image/ico "access plus 1 month"
    ExpiresByType image/webp "access plus 1 month"
</IfModule>

# Gzip compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>

# Protect sensitive files
<Files ~ "^\.">
    Order allow,deny
    Deny from all
</Files>

<Files ~ "^(config|database|env)\.php$">
    Order allow,deny
    Deny from all
</Files>

# Block access to backup files
<FilesMatch "\.(bak|backup|old|orig|save|tmp)$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

## Step 7: SSL Certificate

### 7.1 Enable SSL
1. In Hostinger cPanel, go to **SSL**
2. Enable free Let's Encrypt SSL certificate
3. Force HTTPS redirect by adding to `.htaccess`:

```apache
# Force HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

## Step 8: Testing

### 8.1 Test Your Website
1. Visit `https://your-domain.com/`
2. Check all pages are loading
3. Test admin login
4. Test file uploads
5. Test news creation

### 8.2 Debug Common Issues
- **500 Internal Server Error**: Check `.htaccess` syntax and file permissions
- **Database Connection Error**: Verify `.env` database credentials
- **Upload Issues**: Check upload folder permissions (755)
- **White Screen**: Enable error logging temporarily

## Step 9: Performance Optimization

### 9.1 Enable Caching
```apache
# Add to .htaccess
<IfModule mod_cache.c>
    CacheEnable mem
    CacheEnable disk
</IfModule>
```

### 9.2 Optimize Images
- Use WebP format for images
- Compress images before uploading
- Enable lazy loading

## Step 10: Security Hardening

### 10.1 Security Checklist
- [ ] SSL certificate installed
- [ ] File permissions set correctly
- [ ] Error display disabled in production
- [ ] Admin panel protected with strong password
- [ ] Regular backups scheduled
- [ ] Security headers configured

### 10.2 Backup Strategy
1. Use Hostinger's automatic backup feature
2. Set up additional manual backups:
   - Database: Weekly
   - Files: Monthly
   - Configuration: After changes

## Step 11: Cron Jobs Setup

### 11.1 Set Up Cron Jobs
In Hostinger cPanel, go to **Cron Jobs**:

```bash
# RSS import (every 5 minutes)
*/5 * * * * /usr/bin/php /home/your_user/public_html/cron_import_news.php

# Daily backup
0 2 * * * /usr/bin/mysqldump -u db_user -p'db_pass' db_name > /home/your_user/backups/daily_backup_$(date +\%Y\%m\%d).sql
```

## Troubleshooting

### Common Issues and Solutions

1. **Database Connection Failed**
   - Check database credentials in `.env`
   - Verify database exists
   - Check database user permissions

2. **File Upload Not Working**
   - Check upload folder permissions (755)
   - Verify PHP upload limits
   - Check disk space

3. **404 Errors**
   - Verify `.htaccess` is present
   - Check mod_rewrite is enabled
   - Verify file permissions

4. **Slow Performance**
   - Enable caching
   - Optimize images
   - Check database queries

## Support

If you encounter issues:
1. Check Hostinger knowledge base
2. Contact Hostinger support
3. Review error logs in cPanel

## Migration Checklist

- [ ] Database exported and imported
- [ ] All files uploaded
- [ ] File permissions set
- [ ] .env file configured
- [ ] .htaccess configured for production
- [ ] SSL certificate installed
- [ ] Email configuration tested
- [ ] Admin functionality tested
- [ ] File uploads tested
- [ ] Cron jobs set up
- [ ] Backup strategy implemented
